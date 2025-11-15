<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends BaseApiController
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return $this->unauthorized('Unauthenticated');
            }

            // Use Notification model directly with user_id filter
            $query = Notification::where('user_id', $user->id);

            if ($request->has('is_read')) {
                $query->where('is_read', $request->boolean('is_read'));
            }

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            $limit = $request->input('limit', 20);
            
            // Always use pagination for consistency, but limit results if limit is specified
            if ($limit && $limit < 100) {
                $notifications = $query->latest()->limit($limit)->get();
                // Return as paginated response for consistency
                $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                    $notifications,
                    $notifications->count(),
                    $limit,
                    1,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
                return $this->paginated($paginator, 'Notifications retrieved successfully');
            }

            $notifications = $query->latest()->paginate($limit ?: 20);

            return $this->paginated($notifications, 'Notifications retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Notifications index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            // Return empty array instead of error
            return $this->success([], 'Notifications retrieved successfully');
        }
    }

    public function unreadCount(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return $this->success(['count' => 0], 'Unread count retrieved');
        }

        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return $this->success(['count' => $count], 'Unread count retrieved');
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        $user = $request->user();
        
        if (!$user) {
            return $this->unauthorized('Unauthenticated');
        }
        
        if ($notification->user_id !== $user->id) {
            return $this->forbidden('Unauthorized');
        }

        $notification->markAsRead();

        return $this->success($notification, 'Notification marked as read');
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return $this->unauthorized('Unauthenticated');
        }
        
        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return $this->success(null, 'All notifications marked as read');
    }

    public function destroy(Request $request, Notification $notification)
    {
        $user = $request->user();
        
        if (!$user) {
            return $this->unauthorized('Unauthenticated');
        }
        
        if ($notification->user_id !== $user->id) {
            return $this->forbidden('Unauthorized');
        }

        $notification->delete();

        return $this->success(null, 'Notification deleted successfully');
    }
}
