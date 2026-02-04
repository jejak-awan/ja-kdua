<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class EmailTestController extends BaseApiController
{
    /**
     * Test SMTP connection
     *
     * Attempts to connect to the SMTP server using current configuration
     */
    public function testConnection(Request $request)
    {
        try {
            $config = $this->getEmailConfig($request);

            // Validate required config
            if (empty($config['host']) || empty($config['port'])) {
                return $this->error(
                    'SMTP host and port are required',
                    422,
                    [],
                    'INVALID_CONFIG'
                );
            }

            // Test connection by attempting to connect to SMTP server
            // We'll use fsockopen to test basic connectivity
            $this->updateMailConfig($config);

            // Test basic socket connection
            $timeout = 5;
            $connection = @fsockopen(
                $config['host'],
                $config['port'],
                $errno,
                $errstr,
                $timeout
            );

            if (! $connection) {
                throw new \Exception("Cannot connect to {$config['host']}:{$config['port']} - {$errstr} ({$errno})");
            }

            fclose($connection);
            $testResult = true;

            return $this->success([
                'connected' => $testResult,
                'host' => $config['host'],
                'port' => $config['port'],
                'encryption' => $config['encryption'] ?? 'none',
            ], 'SMTP connection test completed');
        } catch (\Exception $e) {
            return $this->error(
                'Failed to connect to SMTP server: '.$e->getMessage(),
                500,
                [],
                'SMTP_CONNECTION_FAILED',
                ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Send test email
     *
     * Sends a test email to the specified address
     */
    public function sendTest(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        try {
            $config = $this->getEmailConfig($request);

            // Temporarily update mail config
            $this->updateMailConfig($config);

            $to = $validated['to'];
            $subject = $validated['subject'] ?? 'Test Email from '.config('app.name');
            $message = $validated['message'] ?? 'This is a test email sent from the CMS email testing tool.';

            // Send email
            Mail::raw($message, function (Message $mail) use ($to, $subject, $config) {
                $mail->to($to)
                    ->subject($subject)
                    ->from($config['from_address'], $config['from_name']);
            });

            // Log the test email
            $this->logEmailSent([
                'to' => $to,
                'subject' => $subject,
                'type' => 'test',
                'status' => 'sent',
            ]);

            return $this->success([
                'to' => $to,
                'subject' => $subject,
                'sent_at' => now()->toIso8601String(),
            ], 'Test email sent successfully');
        } catch (\Exception $e) {
            Log::error('Test email failed', [
                'to' => $validated['to'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->error(
                'Failed to send test email: '.$e->getMessage(),
                500,
                [],
                'EMAIL_SEND_FAILED',
                ['exception' => $e->getMessage()]
            );
        }
    }

    /**
     * Get email queue status
     *
     * Returns information about pending email jobs in the queue
     */
    public function getQueueStatus()
    {
        try {
            $queueConnection = config('queue.default');
            $queueDriver = config("queue.connections.{$queueConnection}.driver");

            $status = [
                'driver' => $queueDriver,
                'connection' => $queueConnection,
                'pending_jobs' => 0,
                'failed_jobs' => 0,
            ];

            // Get queue stats based on driver
            if ($queueDriver === 'database') {
                $status['pending_jobs'] = DB::table('jobs')->count();
                $status['failed_jobs'] = DB::table('failed_jobs')->count();
            } elseif ($queueDriver === 'redis') {
                // Try to get queue length from Redis
                try {
                    $redis = app('redis');
                    $status['pending_jobs'] = $redis->llen('queues:default');
                } catch (\Exception $e) {
                    // Redis not available or queue name different
                    $status['pending_jobs'] = 'unknown';
                }
            }

            // Get mail queue stats if using mail queue
            $mailQueueCount = Cache::get('email_queue_count', 0);

            return $this->success($status, 'Queue status retrieved successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to get queue status', 'Email queue status');
        }
    }

    /**
     * Get recent email logs
     *
     * Returns recent email sending logs (from cache or log files)
     */
    public function getRecentLogs(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $limit = min(max(1, $limit), 50); // Between 1 and 50

            // Get from cache (stored when emails are sent)
            $logs = Cache::get('email_logs', []);

            // Sort by timestamp descending
            usort($logs, function ($a, $b) {
                return strtotime($b['sent_at'] ?? 0) - strtotime($a['sent_at'] ?? 0);
            });

            // Limit results
            $logs = array_slice($logs, 0, $limit);

            return $this->success([
                'logs' => $logs,
                'total' => count(Cache::get('email_logs', [])),
            ], 'Recent email logs retrieved successfully');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to get email logs', 'Email logs');
        }
    }

    /**
     * Validate email configuration
     *
     * Validates the current email configuration settings
     */
    public function validateConfig(Request $request)
    {
        try {
            $config = $this->getEmailConfig($request);

            $errors = [];
            $warnings = [];

            // Required fields
            if (empty($config['host'])) {
                $errors[] = 'SMTP host is required';
            }

            if (empty($config['port'])) {
                $errors[] = 'SMTP port is required';
            }

            if (empty($config['from_address'])) {
                $errors[] = 'From address is required';
            } elseif (! filter_var($config['from_address'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'From address is not a valid email';
            }

            // Warnings
            if (empty($config['username'])) {
                $warnings[] = 'SMTP username is not set (may be required for authentication)';
            }

            if (empty($config['password'])) {
                $warnings[] = 'SMTP password is not set (may be required for authentication)';
            }

            if (empty($config['encryption'])) {
                $warnings[] = 'SMTP encryption is not set (recommended: tls or ssl)';
            }

            $isValid = empty($errors);

            return $this->success([
                'valid' => $isValid,
                'errors' => $errors,
                'warnings' => $warnings,
                'config' => [
                    'host' => $config['host'] ?? null,
                    'port' => $config['port'] ?? null,
                    'encryption' => $config['encryption'] ?? null,
                    'from_address' => $config['from_address'] ?? null,
                    'from_name' => $config['from_name'] ?? null,
                    'has_username' => ! empty($config['username']),
                    'has_password' => ! empty($config['password']),
                ],
            ], $isValid ? 'Email configuration is valid' : 'Email configuration has errors');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Failed to validate email configuration', 'Email config validation');
        }
    }

    /**
     * Get email configuration from request or settings
     */
    private function getEmailConfig(Request $request): array
    {
        // If config is provided in request, use it (for testing different configs)
        if ($request->has('config')) {
            return array_merge([
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username'),
                'password' => config('mail.mailers.smtp.password'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ], $request->get('config', []));
        }

        // Otherwise, get from settings or config
        $settings = Setting::getGroup('email');

        return [
            'host' => $settings['email.smtp_host'] ?? config('mail.mailers.smtp.host'),
            'port' => $settings['email.smtp_port'] ?? config('mail.mailers.smtp.port'),
            'username' => $settings['email.smtp_username'] ?? config('mail.mailers.smtp.username'),
            'password' => $settings['email.smtp_password'] ?? config('mail.mailers.smtp.password'),
            'encryption' => $settings['email.smtp_encryption'] ?? config('mail.mailers.smtp.encryption'),
            'from_address' => $settings['email.from_address'] ?? config('mail.from.address'),
            'from_name' => $settings['email.from_name'] ?? config('mail.from.name'),
        ];
    }

    /**
     * Temporarily update mail configuration
     */
    private function updateMailConfig(array $config): void
    {
        Config::set('mail.mailers.smtp.host', $config['host']);
        Config::set('mail.mailers.smtp.port', $config['port']);
        Config::set('mail.mailers.smtp.username', $config['username'] ?? null);
        Config::set('mail.mailers.smtp.password', $config['password'] ?? null);
        Config::set('mail.mailers.smtp.encryption', $config['encryption'] ?? null);
        Config::set('mail.from.address', $config['from_address']);
        Config::set('mail.from.name', $config['from_name'] ?? config('app.name'));
    }

    /**
     * Log email sent event
     */
    private function logEmailSent(array $data): void
    {
        $logs = Cache::get('email_logs', []);
        $logs[] = array_merge($data, [
            'sent_at' => now()->toIso8601String(),
        ]);

        // Keep only last 100 logs
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }

        Cache::put('email_logs', $logs, now()->addDays(7));
    }
}
