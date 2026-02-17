import { defineStore } from 'pinia'
import axios from 'axios'
import { logger } from '@/utils/logger'
import i18n from '@/i18n'

export const useSecurityStore = defineStore('security', {
    state: () => ({
        isShieldVisible: false,
        shieldProgress: 0,
        shieldStatus: i18n.global.t('features.security.shield.challenge.status.initializing'),
        lastChallenge: null as { nonce: string; difficulty: number } | null,
    }),

    actions: {
        showShield() {
            this.isShieldVisible = true
            this.shieldProgress = 0
            this.shieldStatus = i18n.global.t('features.security.shield.challenge.status.verifying')
        },

        hideShield() {
            this.isShieldVisible = false
            this.shieldProgress = 0
        },

        updateShield(progress: number, status: string) {
            this.shieldProgress = progress
            this.shieldStatus = status
        },

        async solveChallenge(nonce: string, difficulty: number): Promise<string | null> {
            this.lastChallenge = { nonce, difficulty }
            this.showShield()
            this.updateShield(10, i18n.global.t('features.security.shield.challenge.status.analyzing'))

            try {
                const solution = await this.calculatePoW(nonce, difficulty)

                if (solution !== null) {
                    this.updateShield(80, i18n.global.t('features.security.shield.challenge.status.finalizing'))
                    const success = await this.verifyOnBackend(nonce, solution)

                    if (success) {
                        this.updateShield(100, i18n.global.t('features.security.shield.challenge.status.verified'))
                        setTimeout(() => this.hideShield(), 500)
                        return String(solution)
                    }
                }

                throw new Error('Verification failed')
            } catch (error) {
                logger.error('Security challenge failed:', error)
                this.updateShield(0, i18n.global.t('features.security.shield.challenge.status.failed'))
                setTimeout(() => this.hideShield(), 2000)
                return null
            }
        },

        async calculatePoW(nonce: string, difficulty: number): Promise<number | null> {
            const target = '0'.repeat(difficulty)
            let solution = 0
            const maxAttempts = 1000000

            // Simple interval-based chunking to keep UI responsive if not using worker
            // But we already have worker logic in thoughts, let's use it if possible or plain loop for now
            // To keep it simple and robust across all browsers in SPA:
            const encoder = new TextEncoder()

            // We'll process in chunks of 5000 to allow UI updates
            while (solution < maxAttempts) {
                for (let i = 0; i < 5000; i++) {
                    const data = encoder.encode(nonce + solution)
                    const hashBuffer = await crypto.subtle.digest('SHA-256', data)
                    const hashArray = Array.from(new Uint8Array(hashBuffer))
                    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('')

                    if (hashHex.startsWith(target)) {
                        return solution
                    }
                    solution++
                }

                // Update progress slightly
                this.shieldProgress = Math.min(70, this.shieldProgress + 2)
                // Yield to main thread
                await new Promise(resolve => setTimeout(resolve, 0))
            }

            return null
        },

        async verifyOnBackend(nonce: string, solution: number): Promise<boolean> {
            try {
                // Use a direct axios call to avoid circular interceptor issues
                const response = await axios.post('/api/v1/security/verify-connection', {
                    nonce,
                    solution: String(solution),
                }, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })

                return response.data.success && response.data.data.verified
            } catch (error) {
                return false
            }
        }
    }
})
