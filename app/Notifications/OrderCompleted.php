<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCompleted extends Notification
{
    use Queueable;
    use Traits\UsesEmailTemplate;

    public array $via = ['mail', 'database'];

    public function __construct(
        public Order $order,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->via;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->buildMailMessage(
            data: [
                'name' => $notifiable->name,
                'order_number' => $this->order->number,
                'product_name' => $this->order->product->name ?? 'N/A',
                'amount' => number_format($this->order->total, 2),
                'status' => $this->order->status,
                'url' => route('client.orders.show', $this->order),
            ],
            fallbackSubject: 'Order #'.$this->order->number.' Confirmed',
            fallbackGreeting: 'Hello '.$notifiable->name.'!',
            fallbackLines: [
                'Your order has been placed successfully.',
                'Order: #'.$this->order->number,
                'Product: '.($this->order->product->name ?? 'N/A'),
                'Amount: $'.number_format($this->order->total, 2),
                "You'll receive an email once your service is active.",
            ],
            fallbackActionUrl: route('client.orders.show', $this->order),
            fallbackActionText: 'View Order',
        );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Order Confirmed',
            'message' => 'Order #'.$this->order->number.' has been placed. Total: $'.number_format($this->order->total, 2),
            'order_id' => $this->order->id,
            'order_number' => $this->order->number,
            'amount' => $this->order->total,
            'url' => route('client.orders.show', $this->order),
        ];
    }
}
