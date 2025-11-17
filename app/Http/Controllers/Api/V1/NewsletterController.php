<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class NewsletterController extends BaseApiController
{
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $email = $request->email;
            $name = $request->name;

            // Check if already subscribed
            $existing = NewsletterSubscriber::where('email', $email)->first();

            if ($existing) {
                if ($existing->status === 'subscribed') {
                    return $this->error('Email sudah terdaftar untuk newsletter', 422);
                }

                // Re-subscribe
                $existing->update([
                    'status' => 'subscribed',
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                    'name' => $name ?? $existing->name,
                    'source' => $request->header('referer') ?? 'unknown',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return $this->success([
                    'subscriber' => $existing,
                ], 'Berhasil berlangganan newsletter!');
            }

            // Create new subscriber
            $subscriber = NewsletterSubscriber::create([
                'email' => $email,
                'name' => $name,
                'status' => 'subscribed',
                'subscribed_at' => now(),
                'source' => $request->header('referer') ?? 'unknown',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // TODO: Send welcome email
            // Mail::to($email)->send(new NewsletterWelcome($subscriber));

            return $this->success([
                'subscriber' => $subscriber,
            ], 'Berhasil berlangganan newsletter!', 201);
        } catch (\Exception $e) {
            Log::error('Newsletter subscription error: '.$e->getMessage());

            return $this->error('Terjadi kesalahan saat mendaftar newsletter', 500);
        }
    }

    public function unsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $subscriber = NewsletterSubscriber::where('email', $request->email)->first();

            if (!$subscriber) {
                return $this->error('Email tidak ditemukan', 404);
            }

            if ($subscriber->status === 'unsubscribed') {
                return $this->error('Email sudah berhenti berlangganan', 422);
            }

            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);

            return $this->success(null, 'Berhasil berhenti berlangganan');
        } catch (\Exception $e) {
            Log::error('Newsletter unsubscribe error: '.$e->getMessage());

            return $this->error('Terjadi kesalahan saat berhenti berlangganan', 500);
        }
    }
}

