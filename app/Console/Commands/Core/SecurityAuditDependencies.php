<?php

namespace App\Console\Commands\Core;

use App\Models\Core\DependencyVulnerability;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class SecurityAuditDependencies extends Command
{
    protected $signature = 'security:audit-dependencies';

    protected $description = 'Scan composer and npm dependencies for known vulnerabilities';

    public function handle(): int
    {
        $this->info('Running dependency security audit...');

        // Composer audit
        $this->info('Checking Composer dependencies...');
        $composerResult = Process::path(base_path())->run('composer audit --format=json --no-interaction');

        if ($composerResult->successful()) {
            $this->parseComposerAudit($composerResult->output());
        }

        // NPM audit
        $this->info('Checking NPM dependencies...');
        $npmResult = Process::path(base_path())->run('npm audit --json');

        if ($npmResult->successful()) {
            $this->parseNpmAudit($npmResult->output());
        }

        $newCount = DependencyVulnerability::where('status', 'new')->count();

        if ($newCount > 0) {
            $this->warn("Found {$newCount} new vulnerabilities!");
        } else {
            $this->info('No new vulnerabilities found.');
        }

        return Command::SUCCESS;
    }

    protected function parseComposerAudit(string $json): void
    {
        try {
            $data = json_decode($json, true);
            if (! is_array($data)) {
                return;
            }

            $advisories = $data['advisories'] ?? [];
            if (! is_array($advisories)) {
                return;
            }

            foreach ($advisories as $package => $alerts) {
                if (! is_array($alerts)) {
                    continue;
                }
                foreach ($alerts as $advisory) {
                    if (! is_array($advisory)) {
                        continue;
                    }
                    DependencyVulnerability::updateOrCreate(
                        [
                            'package_name' => $advisory['packageName'] ?? 'unknown',
                            'version' => $advisory['affectedVersions'] ?? 'unknown',
                            'cve' => $advisory['cve'] ?? null,
                        ],
                        [
                            'severity' => strtolower($advisory['severity'] ?? 'medium'),
                            'fixed_in' => $advisory['sources'][0]['remediatedVersions'] ?? null,
                            'source' => 'composer',
                            'description' => $advisory['title'] ?? '',
                            'status' => 'new',
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            $this->error('Failed to parse composer audit: '.$e->getMessage());
        }
    }

    protected function parseNpmAudit(string $json): void
    {
        try {
            $data = json_decode($json, true);
            if (! is_array($data)) {
                return;
            }

            $vulnerabilities = $data['vulnerabilities'] ?? [];
            if (! is_array($vulnerabilities)) {
                return;
            }

            foreach ($vulnerabilities as $name => $vuln) {
                if (! is_array($vuln)) {
                    continue;
                }
                DependencyVulnerability::updateOrCreate(
                    [
                        'package_name' => $name,
                        'version' => $vuln['range'] ?? 'unknown',
                        'cve' => $vuln['via'][0]['cve'] ?? null,
                    ],
                    [
                        'severity' => strtolower($vuln['severity'] ?? 'medium'),
                        'fixed_in' => ($vuln['fixAvailable'] ?? false) ? 'available' : null,
                        'source' => 'npm',
                        'description' => $vuln['via'][0]['title'] ?? '',
                        'status' => 'new',
                    ]
                );
            }
        } catch (\Exception $e) {
            $this->error('Failed to parse npm audit: '.$e->getMessage());
        }
    }
}
