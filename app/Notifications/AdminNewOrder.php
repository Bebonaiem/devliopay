<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminNewOrder extends Notification
{
    use Queueable;
    use Traits\UsesEmailTemplate;

    public array $via = ['mail'];

    public function __construct(
        public Order $order,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->via;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $user = $this->order->user;

        return $this->buildMailMessage(
            data: [
                'admin_name' => $notifiable->name,
                'order_number' => $this->order->number,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'product_name' => $this->order->product->name ?? 'N/A',
                'amount' => number_format($this->order->total, 2),
                'status' => $this->order->status,
            ],
            fallbackSubject: "New Order #{$this->order->number}",
            fallbackGreeting: 'New Order Received',
            fallbackLines: [
                'A new order has been placed.',
                "Order: #{$this->order->number}",
                "Customer: {$user->name} ({$user->email})",
                'Amount: $'.number_format($this->order->total, 2),
                'Status: '.ucfirst($this->order->status),
            ],
            fallbackActionUrl: url("/admin/orders/{$this->order->id}/edit"),
            fallbackActionText: 'View Order',
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Order',
            'message' => 'Order #'.$this->order->number.' from '.$this->order->user->name.'. Total: $'.number_format($this->order->total, 2),
            'order_id' => $this->order->id,
            'order_number' => $this->order->number,
        ];
    }
}
