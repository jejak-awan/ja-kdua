<?php

namespace Database\Seeders;

use App\Models\AnalyticsSession;
use App\Models\AnalyticsVisit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        $startDate = now()->subDays(30);
        $endDate = now();
        
        $browsers = ['chrome', 'firefox', 'safari', 'edge'];
        $os = ['windows', 'macos', 'ios', 'android'];
        $devices = ['desktop', 'mobile', 'tablet'];
        $countries = ['ID', 'US', 'SG', 'MY', 'AU'];
        $cities = ['Jakarta', 'Surabaya', 'New York', 'Singapore', 'Kuala Lumpur', 'Sydney'];
        $urls = [
            'https://jejakawan.com/',
            'https://jejakawan.com/blog',
            'https://jejakawan.com/about',
            'https://jejakawan.com/contact',
            'https://jejakawan.com/portfolio'
        ];

        // Generate data for each day
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            // Random number of sessions per day (10-50)
            $sessionsCount = rand(10, 50);

            for ($i = 0; $i < $sessionsCount; $i++) {
                $startTime = $date->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59));
                $duration = rand(30, 600); // 30s to 10m
                $endTime = $startTime->copy()->addSeconds($duration);
                $sessionId = Str::uuid();
                
                $session = AnalyticsSession::create([
                    'session_id' => $sessionId,
                    'user_id' => rand(0, 5) === 0 ? 1 : null, // 1/5 chance of being admin
                    'ip_address' => long2ip(rand(0, 4294967295)),
                    'user_agent' => 'Mozilla/5.0 (Dummy Seeder)',
                    'device_type' => $devices[array_rand($devices)],
                    'browser' => $browsers[array_rand($browsers)],
                    'os' => $os[array_rand($os)],
                    'country' => $countries[array_rand($countries)],
                    'city' => $cities[array_rand($cities)],
                    'page_views' => rand(1, 5),
                    'duration' => $duration,
                    'started_at' => $startTime,
                    'ended_at' => $endTime,
                ]);

                // Create visits for this session
                $views = $session->page_views;
                $visitTime = $startTime->copy();

                for ($v = 0; $v < $views; $v++) {
                    AnalyticsVisit::create([
                        'session_id' => $sessionId,
                        'user_id' => $session->user_id,
                        'ip_address' => $session->ip_address,
                        'user_agent' => $session->user_agent,
                        'referer' => $v === 0 ? 'https://google.com' : $urls[array_rand($urls)],
                        'url' => $urls[array_rand($urls)],
                        'method' => 'GET',
                        'status_code' => 200,
                        'duration' => rand(10, $duration / $views),
                        'visited_at' => $visitTime,
                    ]);
                    
                    $visitTime->addSeconds(rand(10, 60));
                }
            }
        }
    }
}
