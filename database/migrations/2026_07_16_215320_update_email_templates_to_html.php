<?php

use App\Models\EmailTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $templates = [
            [
                'slug' => 'invoice-created',
                'body_html' => file_get_contents(resource_path('views/emails/invoice-created.blade.php')),
                'variables' => ['invoice_number' => 'Invoice number', 'name' => 'Client name', 'amount' => 'Invoice amount', 'due_date' => 'Payment due date', 'company_name' => 'Company name'],
            ],
            [
                'slug' => 'payment-received',
                'body_html' => file_get_contents(resource_path('views/emails/payment-received.blade.php')),
                'variables' => ['invoice_number' => 'Invoice number', 'name' => 'Client name', 'amount' => 'Amount paid', 'date' => 'Payment date', 'company_name' => 'Company name'],
            ],
            [
                'slug' => 'service-activated',
                'body_html' => file_get_contents(resource_path('views/emails/service-activated.blade.php')),
                'variables' => ['product_name' => 'Product name', 'name' => 'Client name', 'ip_address' => 'Server IP', 'company_name' => 'Company name'],
            ],
            [
                'slug' => 'service-suspended',
                'body_html' => file_get_contents(resource_path('views/emails/service-suspended.blade.php')),
                'variables' => ['product_name' => 'Product name', 'name' => 'Client name', 'reason' => 'Suspension reason', 'company_name' => 'Company name'],
            ],
            [
                'slug' => 'ticket-reply',
                'body_html' => file_get_contents(resource_path('views/emails/ticket-reply.blade.php')),
                'variables' => ['ticket_id' => 'Ticket number', 'subject' => 'Ticket subject', 'name' => 'Client name', 'message' => 'Reply message', 'company_name' => 'Company name'],
            ],
        ];

        foreach ($templates as $data) {
            EmailTemplate::where('slug', $data['slug'])->update([
                'body_html' => $data['body_html'],
                'variables' => $data['variables'],
            ]);
        }

        // Add new templates that don't exist yet
        $newTemplates = [
            ['name' => 'Verify Email', 'slug' => 'verify-email', 'subject' => 'Verify your email address', 'body_html' => file_get_contents(resource_path('views/emails/verify-email.blade.php')), 'variables' => ['name' => 'Client name', 'url' => 'Verification URL', 'company_name' => 'Company name']],
            ['name' => 'Password Reset', 'slug' => 'password-reset', 'subject' => 'Reset Your Password', 'body_html' => file_get_contents(resource_path('views/emails/password-reset.blade.php')), 'variables' => ['name' => 'Client name', 'url' => 'Reset URL', 'company_name' => 'Company name']],
            ['name' => 'Invoice Overdue', 'slug' => 'invoice-overdue', 'subject' => 'Overdue Invoice #{invoice_number}', 'body_html' => file_get_contents(resource_path('views/emails/invoice-overdue.blade.php')), 'variables' => ['invoice_number' => 'Invoice number', 'name' => 'Client name', 'amount' => 'Amount due', 'due_date' => 'Due date', 'days_overdue' => 'Days overdue', 'company_name' => 'Company name']],
            ['name' => 'Service Status Changed', 'slug' => 'service-status-changed', 'subject' => 'Service Status Updated - {product_name}', 'body_html' => file_get_contents(resource_path('views/emails/service-status-changed.blade.php')), 'variables' => ['product_name' => 'Product name', 'name' => 'Client name', 'old_status' => 'Previous status', 'new_status' => 'New status', 'company_name' => 'Company name']],
            ['name' => 'Welcome', 'slug' => 'welcome', 'subject' => 'Welcome to {company_name}!', 'body_html' => file_get_contents(resource_path('views/emails/welcome.blade.php')), 'variables' => ['name' => 'Client name', 'email' => 'Client email', 'company_name' => 'Company name']],
            ['name' => 'Order Completed', 'slug' => 'order-completed', 'subject' => 'Order #{order_number} Confirmed', 'body_html' => file_get_contents(resource_path('views/emails/order-completed.blade.php')), 'variables' => ['order_number' => 'Order number', 'name' => 'Client name', 'product_name' => 'Product name', 'amount' => 'Order total', 'status' => 'Order status', 'company_name' => 'Company name']],
            ['name' => 'Credit Deposited', 'slug' => 'credit-deposit', 'subject' => 'Credit {type} - ${amount}', 'body_html' => file_get_contents(resource_path('views/emails/credit-deposit.blade.php')), 'variables' => ['name' => 'Client name', 'amount' => 'Amount', 'type' => 'Transaction type', 'old_balance' => 'Previous balance', 'new_balance' => 'New balance', 'company_name' => 'Company name']],
            ['name' => 'Admin: New Order', 'slug' => 'admin-new-order', 'subject' => 'New Order #{order_number} - {company_name}', 'body_html' => file_get_contents(resource_path('views/emails/admin-new-order.blade.php')), 'variables' => ['admin_name' => 'Admin name', 'order_number' => 'Order number', 'customer_name' => 'Customer name', 'customer_email' => 'Customer email', 'product_name' => 'Product name', 'amount' => 'Order total', 'status' => 'Order status', 'company_name' => 'Company name']],
            ['name' => 'Admin: New Ticket', 'slug' => 'admin-new-ticket', 'subject' => 'New Ticket #{ticket_number} - {company_name}', 'body_html' => file_get_contents(resource_path('views/emails/admin-new-ticket.blade.php')), 'variables' => ['admin_name' => 'Admin name', 'ticket_number' => 'Ticket number', 'subject' => 'Ticket subject', 'customer_name' => 'Customer name', 'customer_email' => 'Customer email', 'priority' => 'Priority', 'company_name' => 'Company name']],
            ['name' => 'Admin: Payment Received', 'slug' => 'admin-payment-received', 'subject' => 'Payment Received - ${amount} - {company_name}', 'body_html' => file_get_contents(resource_path('views/emails/admin-payment-received.blade.php')), 'variables' => ['admin_name' => 'Admin name', 'customer_name' => 'Customer name', 'customer_email' => 'Customer email', 'amount' => 'Payment amount', 'invoice_number' => 'Invoice number', 'gateway' => 'Payment gateway', 'company_name' => 'Company name']],
        ];

        foreach ($newTemplates as $tpl) {
            EmailTemplate::updateOrCreate(
                ['slug' => $tpl['slug']],
                array_merge($tpl, ['is_enabled' => true])
            );
        }
    }

    public function down(): void
    {
        EmailTemplate::whereIn('slug', [
            'verify-email', 'password-reset', 'invoice-overdue', 'service-status-changed',
            'welcome', 'order-completed', 'credit-deposit',
            'admin-new-order', 'admin-new-ticket', 'admin-payment-received',
        ])->delete();
    }
};
