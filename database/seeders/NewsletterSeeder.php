<?php

namespace Database\Seeders;

use App\Models\NewsletterSubscriber;
use Illuminate\Database\Seeder;

class NewsletterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        NewsletterSubscriber::truncate();

        $firstNames = ['John', 'Jane', 'Michael', 'Sarah', 'David', 'Emily', 'Chris', 'Amanda', 'Robert', 'Lisa',
            'James', 'Anna', 'William', 'Sophia', 'Daniel', 'Olivia', 'Matthew', 'Emma', 'Andrew', 'Mia'];
        $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
            'Wilson', 'Anderson', 'Taylor', 'Thomas', 'Moore', 'Jackson', 'Martin', 'Lee', 'White', 'Harris'];
        $domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com', 'company.com', 'mail.com'];
        $sources = ['homepage', 'blog', 'footer', 'popup', 'landing-page', 'social-media', 'referral', 'api'];
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15',
            'Mozilla/5.0 (Linux; Android 13) AppleWebKit/537.36',
        ];

        $subscribers = [];

        // Create 50 subscribers
        for ($i = 0; $i < 50; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $domain = $domains[array_rand($domains)];
            $email = strtolower($firstName.'.'.$lastName.rand(1, 999).'@'.$domain);

            // 80% subscribed, 20% unsubscribed
            $isSubscribed = rand(1, 100) <= 80;
            $status = $isSubscribed ? 'subscribed' : 'unsubscribed';

            $subscribedAt = now()->subDays(rand(1, 180))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            $unsubscribedAt = ! $isSubscribed ? $subscribedAt->copy()->addDays(rand(1, 30)) : null;

            $subscribers[] = [
                'email' => $email,
                'name' => rand(1, 100) <= 70 ? $firstName.' '.$lastName : null, // 70% have names
                'status' => $status,
                'subscribed_at' => $subscribedAt,
                'unsubscribed_at' => $unsubscribedAt,
                'source' => $sources[array_rand($sources)],
                'ip_address' => rand(1, 255).'.'.rand(1, 255).'.'.rand(1, 255).'.'.rand(1, 255),
                'user_agent' => $userAgents[array_rand($userAgents)],
                'created_at' => $subscribedAt,
                'updated_at' => $unsubscribedAt ?? $subscribedAt,
            ];
        }

        // Insert all subscribers
        NewsletterSubscriber::insert($subscribers);

        $this->command->info('Created 50 newsletter subscribers');
        $this->command->info('- Subscribed: '.NewsletterSubscriber::where('status', 'subscribed')->count());
        $this->command->info('- Unsubscribed: '.NewsletterSubscriber::where('status', 'unsubscribed')->count());
    }
}
