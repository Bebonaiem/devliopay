<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Models\TicketDepartment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Currency::updateOrCreate(['code' => 'USD'], ['name' => 'US Dollar', 'symbol' => '$', 'is_default' => true]);
        Currency::updateOrCreate(['code' => 'EUR'], ['name' => 'Euro', 'symbol' => '€']);
        Currency::updateOrCreate(['code' => 'GBP'], ['name' => 'British Pound', 'symbol' => '£']);

        Setting::updateOrCreate(['group' => 'general', 'name' => 'site_name'], ['value' => 'DevlioPay']);
        Setting::updateOrCreate(['group' => 'general', 'name' => 'site_description'], ['value' => 'Open-source billing platform for hosting businesses']);
        Setting::updateOrCreate(['group' => 'general', 'name' => 'currency'], ['value' => 'USD']);

        TicketDepartment::updateOrCreate(['slug' => 'general'], ['name' => 'General Support', 'description' => 'General questions and support', 'sort_order' => 1]);
        TicketDepartment::updateOrCreate(['slug' => 'technical'], ['name' => 'Technical Support', 'description' => 'Technical issues and troubleshooting', 'sort_order' => 2]);
        TicketDepartment::updateOrCreate(['slug' => 'billing'], ['name' => 'Billing', 'description' => 'Billing and payment inquiries', 'sort_order' => 3]);
        TicketDepartment::updateOrCreate(['slug' => 'sales'], ['name' => 'Sales', 'description' => 'Sales and product inquiries', 'sort_order' => 4]);

        // --- CLIENT EMAIL TEMPLATES ---

        EmailTemplate::updateOrCreate(['slug' => 'welcome'], [
            'name' => 'Welcome',
            'subject' => 'Welcome to {company_name}!',
            'body_html' => file_get_contents(resource_path('views/emails/welcome.blade.php')),
            'variables' => ['name' => 'Client name', 'email' => 'Client email', 'company_name' => 'Company name'],
            'is_enabled' => true,
        ]);

        EmailTemplate::updateOrCreate(['slug' => 'verify-email'], [
            'name' => 'Verify Email',
            'subject' => 'Verify your email address',
            'body_html' => file_get_contents(resource_path('views/emails/verify-email.blade.php')),
            'variables' => ['name' => 'Client name', 'url' => 'Verification URL', 'company_name' => 'Company name'],
            'is_enabled' => true,
        ]);

        EmailTemplate::updateOrCreate(['slug' => 'password-reset'], [
            'name' => 'Password Reset',
            'subject' => 'Reset Your Password',
            'body_html' => file_get_contents(resource_path('views/emails/password-reset.blade.php')),
            'variables' => ['name' => 'Client name', 'url' => 'Reset URL', 'company_name' => 'Company name'],
            'is_enabled' => true,
        ]);

        EmailTemplate::updateOrCreate(['slug' => 'invoice-created'], [
            'name' => 'Invoice Created',
            'subject' => 'New Invoice #{invoice_number}',
            'body_html' => file_get_contents(resource_path('views/emails/invoice-created.blade.php')),
            'variables' => ['invoice_number' => 'Invoice number', 'name' => 'Client name', 'amount' => 'Invoice amount', 'due_date' => 'Payment due date', 'company_name' => 'Company name'],
            'is_enabled' => true,
        ]);

        EmailTemplate::updateOrCreate(['slug' => 'payment-received'], [
            'name' => 'Payment Received',
            'subject' => 'Payment Confirmation - Invoice #{invoice_number}',
            'body_html' => file_get_contents(resource_path('views/emails/payment-received.blade.php')),
            'variables' => ['invoice_number' => 'Invoice number', 'name' => 'Client name', 'amount' => 'Amount paid', 'date' => 'Payment date', 'company_name' => 'Company name'],
            'is_enabled' => true,
        ]);

        EmailTemplate::updateOrCreate(['slug' => 'invoice-overdue'], [
            'name' => 'Invoice Overdue',
            'subject' => 'Overdue Invoice #{invoice_number}',
            'body_html' => file_get_contents(resource_path('views/emails/invoice-overdue.blade.php')),
            'variables' => ['invoice_number' => 'Invoice number', 'name' => 'Client name', 'amount' => 'Amount due', 'due_date' => 'Due date', 'days_overdue' => 'Days overdue', 'company_name' => 'Company name'],
            'is_enabled' => true,
        ]);

        EmailTemplate::updateOrCreate(['slug' => 'service-activated'], [
            'name' => 'Service Activated',
            'subject' => 'Your {product_name} has been activated',
            'body_html' => file_get_contents(resource_path('views/emails/service-activated.blade.php')),
            'variables' => ['product_name' => 'Product name', 'name' => 'Client name', 'ip_address' => 'Server IP', 'company_name' => 'Company name'],
            'is_enabled' => true,
        ]);

        EmailTemplate::updateOrCreate(['slug' => 'service-suspended'], [
            'name' => 'Service Suspended',
            'subject' => 'Service Suspended - {product_name}',
            'body_html' => file_get_contents(resource_path('views/emails/service-suspended.blade.php')),
            'variables' => ['product_name' => 'Product name', 'name' => 'Client name', 'reason' => 'Suspension reason', 'company_name' => 'Company name'],
            'is_enabled' => true,
        ]);

        EmailTemplate::updateOrCreate(['slug' => 'service-status-changed'], [
            'name' => 'Service Status Changed',
            'subject' => 'Service Status Updated - {product_name}',
            'body_html' => file_get_contents(resource_path('views/emails/service-status-changed.blade.php')),
            'variables' => ['product_name' => 'Product name', 'name' => 'Client name', 'old_status' => 'Previous status', 'new_status' => 'New status', 'company_name' => 'Company name'],
            'is_enabled' => true,
        ]);

        EmailTemplate::updateOrCreate(['slug' => 'ticket-reply'], [
            'name' => 'Ticket Reply',
            'subject' => 'Re: [{ticket_id}] {subject}',
            'body_html' => file_get_contents(resource_path('views/emails/ticket-reply.blade.php')),
            'variables' => ['ticket_id' => 'Ticket number', 'subject' => 'Ticket subject', 'name' => 'Client name', 'message' => 'Reply message', 'company_name' => 'Company name'],
            'is_enabled' => true,
        ]);

        EmailTemplate::updateOrCreate(['slug' => 'order-completed'], [
            'name' => 'Order Completed',
            'subject' => 'Order #{order_number} Confirmed',
            'body_html' => file_get_contents(resource_path('views/emails/order-completed.blade.php')),
            'variables' => ['order_number' => 'Order number', 'name' => 'Client name', 'product_name' => 'Product name', 'amount' => 'Order total', 'status' => 'Order status', 'company_name' => 'Company name'],
            'is_enabled' => true,
        ]);

        EmailTemplate::updateOrCreate(['slug' => 'credit-deposit'], [
            'name' => 'Credit Deposited',
            'subject' => 'Credit {type} - ${amount}',
            'body_html' => file_get_contents(resource_path('views/emails/credit-deposit.blade.php')),
            'variables' => ['name' => 'Client name', 'amount' => 'Amount', 'type' => 'Transaction type', 'old_balance' => 'Previous balance', 'new_balance' => 'New balance', 'company_name' => 'Company name'],
            'is_enabled' => true,
        ]);

        // --- ADMIN EMAIL TEMPLATES ---

        EmailTemplate::updateOrCreate(['slug' => 'admin-new-order'], [
            'name' => 'Admin: New Order',
            'subject' => 'New Order #{order_number} - {company_name}',
            'body_html' => file_get_contents(resource_path('views/emails/admin-new-order.blade.php')),
            'variables' => ['admin_name' => 'Admin name', 'order_number' => 'Order number', 'customer_name' => 'Customer name', 'customer_email' => 'Customer email', 'product_name' => 'Product name', 'amount' => 'Order total', 'status' => 'Order status', 'company_name' => 'Company name'],
            'is_enabled' => true,
        ]);

        EmailTemplate::updateOrCreate(['slug' => 'admin-new-ticket'], [
            'name' => 'Admin: New Ticket',
            'subject' => 'New Ticket #{ticket_number} - {company_name}',
            'body_html' => file_get_contents(resource_path('views/emails/admin-new-ticket.blade.php')),
            'variables' => ['admin_name' => 'Admin name', 'ticket_number' => 'Ticket number', 'subject' => 'Ticket subject', 'customer_name' => 'Customer name', 'customer_email' => 'Customer email', 'priority' => 'Priority', 'company_name' => 'Company name'],
            'is_enabled' => true,
        ]);

        EmailTemplate::updateOrCreate(['slug' => 'admin-payment-received'], [
            'name' => 'Admin: Payment Received',
            'subject' => 'Payment Received - ${amount} - {company_name}',
            'body_html' => file_get_contents(resource_path('views/emails/admin-payment-received.blade.php')),
            'variables' => ['admin_name' => 'Admin name', 'customer_name' => 'Customer name', 'customer_email' => 'Customer email', 'amount' => 'Payment amount', 'invoice_number' => 'Invoice number', 'gateway' => 'Payment gateway', 'company_name' => 'Company name'],
            'is_enabled' => true,
        ]);
    }
}
