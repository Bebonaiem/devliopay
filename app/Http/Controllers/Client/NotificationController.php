<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $notificationTypes = [
            'invoice_created' => 'Invoice Created',
            'payment_received' => 'Payment Received',
            'invoice_overdue' => 'Invoice Overdue',
            'service_activated' => 'Service Activated',
            'service_suspended' => 'Service Suspended',
            'service_status_changed' => 'Service Status Changed',
            'ticket_reply' => 'Ticket Reply',
            'welcome_user' => 'Welcome',
            'order_completed' => 'Order Completed',
            'credit_deposited' => 'Credit Deposit',
            'verify_email' => 'Email Verification',
            'reset_password' => 'Password Reset',
        ];

        $preferences = [];
        foreach ($notificationTypes as $type => $label) {
            $pref = ClientNotification::getForUser($user->id, $type);
            $preferences[$type] = [
                'label' => $label,
                'email' => $pref->email_enabled,
                'dashboard' => $pref->dashboard_enabled,
            ];
        }

        $notifications = $user->notifications()->latest()->limit(50)->get();

        return view('client.notifications.index', compact('preferences', 'notifications'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'preferences' => 'required|array',
        ]);

        foreach ($request->preferences as $type => $settings) {
            ClientNotification::updateOrCreate(
                ['user_id' => $user->id, 'type' => $type],
                [
                    'email_enabled' => $settings['email'] ?? false,
                    'dashboard_enabled' => $settings['dashboard'] ?? false,
                ]
            );
        }

        return redirect()->route('client.notifications.index')
            ->with('success', 'Notification preferences updated');
    }

    public function markAsRead(Request $request)
    {
        $user = Auth::user();

        if ($request->filled('id')) {
            $notification = $user->notifications()->where('id', $request->id)->first();
            if ($notification) {
                $notification->markAsRead();
            }
        } else {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    public function clear()
    {
        $user = Auth::user();
        $user->notifications()->delete();

        return redirect()->route('client.notifications.index')
            ->with('success', 'Notification history cleared');
    }
}
