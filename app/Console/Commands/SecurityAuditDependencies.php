<?php

namespace App\Console\Commands;

use App\Models\DependencyVulnerability;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class SecurityAuditDependencies extends Command
{
    protected $signature = 'security:audit-dependencies';

    protected $description = 'Scan composer and npm dependencies for known vulnerabilities';

    public function handle()
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

    protected function parseComposerAudit($json)
    {
        try {
            $data = json_decode($json, true);

            if (isset($data['advisories']) && count($data['advisories']) > 0) {
                foreach ($data['advisories'] as $advisory) {
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

    protected function parseNpmAudit($json)
    {
        try {
            $data = json_decode($json, true);

            if (isset($data['vulnerabilities']) && count($data['vulnerabilities']) > 0) {
                foreach ($data['vulnerabilities'] as $name => $vuln) {
                    DependencyVulnerability::updateOrCreate(
                        [
                            'package_name' => $name,
                            'version' => $vuln['range'] ?? 'unknown',
                            'cve' => $vuln['via'][0]['cve'] ?? null,
                        ],
                        [
                            'severity' => strtolower($vuln['severity'] ?? 'medium'),
                            'fixed_in' => $vuln['fixAvailable'] ? 'available' : null,
                            'source' => 'npm',
                            'description' => $vuln['via'][0]['title'] ?? '',
                            'status' => 'new',
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            $this->error('Failed to parse npm audit: '.$e->getMessage());
        }
    }
}
