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
                    'ip_address' => \App\Helpers\IpHelper::getClientIp($request),
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
                'ip_address' => \App\Helpers\IpHelper::getClientIp($request),
                'user_agent' => $request->userAgent(),
            ]);

            // Send welcome email
            \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\NewsletterWelcome($subscriber));

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
    /**
     * Admin: Get paginated subscribers
     */
    public function index(Request $request)
    {
        try {
            $query = NewsletterSubscriber::query();

            // Search by email or name
            if ($request->has('q')) {
                $search = $request->q;
                $query->where(function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status') && in_array($request->status, ['subscribed', 'unsubscribed'])) {
                $query->where('status', $request->status);
            }

            $perPage = $request->get('per_page', 10);
            $subscribers = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return $this->success($subscribers);
        } catch (\Exception $e) {
            Log::error('Newsletter admin index error: ' . $e->getMessage());
            return $this->error('Failed to fetch subscribers', 500);
        }
    }

    /**
     * Admin: Delete subscriber
     */
    public function destroy($id)
    {
        try {
            $subscriber = NewsletterSubscriber::findOrFail($id);
            $subscriber->delete();

            return $this->success(null, 'Subscriber deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete subscriber', 500);
        }
    }

    /**
     * Admin: Export subscribers (returns raw CSV data)
     */
    public function export(Request $request)
    {
        try {
            $query = NewsletterSubscriber::query();

            if ($request->has('status') && in_array($request->status, ['subscribed', 'unsubscribed'])) {
                $query->where('status', $request->status);
            }

            $subscribers = $query->orderBy('created_at', 'desc')->get();

            $headers = [
                'Content-type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=subscribers.csv',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function () use ($subscribers) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['ID', 'Email', 'Name', 'Status', 'Joined At', 'Source']);

                foreach ($subscribers as $subscriber) {
                    fputcsv($file, [
                        $subscriber->id,
                        $subscriber->email,
                        $subscriber->name,
                        $subscriber->status,
                        $subscriber->created_at,
                        $subscriber->source,
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
             Log::error('Newsletter export error: ' . $e->getMessage());
             return $this->error('Failed to export subscribers', 500);
        }
    }
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:newsletter_subscribers,id',
            'action' => 'required|in:delete,unsubscribe,subscribe',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        try {
            if ($action === 'delete') {
                NewsletterSubscriber::whereIn('id', $ids)->delete();
                return $this->success(null, 'Selected subscribers deleted successfully');
            }

            if ($action === 'unsubscribe') {
                NewsletterSubscriber::whereIn('id', $ids)->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]);
                return $this->success(null, 'Selected subscribers unsubscribed successfully');
            }

            if ($action === 'subscribe') {
                 NewsletterSubscriber::whereIn('id', $ids)->update(['status' => 'subscribed', 'subscribed_at' => now(), 'unsubscribed_at' => null]);
                return $this->success(null, 'Selected subscribers subscribed successfully');
            }

        } catch (\Exception $e) {
            return $this->error('Bulk action failed: ' . $e->getMessage(), 500);
        }

        return $this->error('Invalid action', 422);
    }
}

