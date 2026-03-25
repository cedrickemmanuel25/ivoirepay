<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBulkNotificationJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::guard('admin')->user() ?? Auth::user();
        
        $notifications = $user->notifications()
            ->latest()
            ->paginate(50);
            
        return view('admin.notifications.index', compact('notifications'));
    }

    public function unreadCount()
    {
        $user = Auth::guard('admin')->user() ?? Auth::user();

        if (!$user) {
            return response()->json(['count' => 0, 'latest' => []]);
        }

        $count = $user->notifications()->whereNull('read_at')->count();
        
        $latest = $user->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($notif) {
                // Determine icon based on type
                $icon = 'fa-bell text-gray-500';
                if ($notif->type === 'kyc_submitted') $icon = 'fa-id-card text-blue-500';
                if ($notif->type === 'withdrawal_requested') $icon = 'fa-money-bill-transfer text-orange-500';
                
                return [
                    'id' => $notif->id,
                    'title' => $notif->title,
                    'message' => \Illuminate\Support\Str::limit($notif->message, 50),
                    'time' => $notif->created_at->diffForHumans(),
                    'icon' => $icon,
                    'read' => $notif->read_at !== null,
                ];
            });

        return response()->json([
            'count' => $count,
            'latest' => $latest
        ]);
    }

    public function markAllAsRead()
    {
        $user = Auth::guard('admin')->user() ?? Auth::user();
        
        if ($user) {
            $user->notifications()->whereNull('read_at')->update(['read_at' => now()]);
        }

        return back()->with('success', 'Toutes vos notifications ont été marquées comme lues.');
    }

    public function sendBulk(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|in:all,clients,merchants,specific',
            'specific_user_id' => 'required_if:target,specific|exists:users,id',
            'channels' => 'required|array|min:1',
            'channels.sms' => 'boolean',
            'channels.inapp' => 'boolean',
            'message' => 'required|string|max:160',
        ]);

        $channels = [
            'sms' => !empty($validated['channels']['sms']),
            'inapp' => !empty($validated['channels']['inapp']),
        ];

        SendBulkNotificationJob::dispatch(
            $validated['target'],
            $channels,
            $validated['message'],
            $validated['specific_user_id'] ?? null
        );

        return back()->with('success', 'L\'envoi des notifications a été planifié en arrière-plan avec succès.');
    }
}
