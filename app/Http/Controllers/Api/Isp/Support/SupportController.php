<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Support;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SupportController extends BaseApiController
{
    /**
     * Get list of tickets based on user role.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return $this->unauthorized();
        }

        $query = Ticket::with('user')->latest();

        // If member, only see their own tickets
        if ($user->hasRole('ISP Member')) {
            $query->where('user_id', (int) $user->id);
        }

        // Apply filters if any
        if ($request->has('status')) {
            $status = $request->input('status');
            if (is_string($status)) {
                $query->where('status', $status);
            }
        }

        if ($request->has('category')) {
            $category = $request->input('category');
            if (is_string($category)) {
                $query->where('category', $category);
            }
        }

        $perPage = $request->input('per_page');
        $perPageInt = is_numeric($perPage) ? (int) $perPage : 15;
        $tickets = $query->paginate($perPageInt);

        return $this->success($tickets, 'Tickets retrieved successfully');
    }

    /**
     * Create a new support ticket.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => ['required', Rule::in(['Technical', 'Billing', 'Sales'])],
            'priority' => ['required', Rule::in(['Low', 'Medium', 'High'])],
            'message' => 'required|string',
        ]);

        $ticket = Ticket::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'status' => 'Open',
        ]));

        return $this->success($ticket, 'Ticket created successfully', 201);
    }

    /**
     * Get ticket details.
     */
    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return $this->unauthorized();
        }

        /** @var Ticket $ticket */
        $ticket = Ticket::with('user')->findOrFail($id);

        // Access control
        if ($user->hasRole('ISP Member') && (int) $ticket->user_id !== (int) $user->id) {
            return $this->forbidden('You are not authorized to view this ticket');
        }

        return $this->success($ticket, 'Ticket details retrieved successfully');
    }

    /**
     * Update ticket status (Admin/Staff only).
     */
    public function updateStatus(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return $this->unauthorized();
        }

        // Simple role check for status updates
        if ($user->hasRole('ISP Member')) {
            return $this->forbidden('Members cannot update ticket status');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['Open', 'In Progress', 'Resolved', 'Closed'])],
        ]);

        /** @var Ticket $ticket */
        $ticket = Ticket::findOrFail($id);
        $ticket->update($validated);

        return $this->success($ticket, 'Ticket status updated successfully');
    }
}
