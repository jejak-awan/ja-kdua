import { defineStore } from 'pinia'

export const useIspStore = defineStore('isp', {
    state: () => ({
        nodes: [],
        loading: false,
        alerts: [],
    }),
    actions: {
        async fetchNodes() {
            // Placeholder for fetching infra nodes
        }
    }
})
