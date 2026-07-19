<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Client\CreditController;
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\InvoiceController;
use App\Http\Controllers\Client\NotificationController;
use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Client\ServiceController;
use App\Http\Controllers\Client\TicketController;
use App\Http\Controllers\Client\TwoFactorController;
use App\Http\Controllers\Client\UpgradeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\WebhookController;
use App\Models\EmailVerificationToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/store', [StoreController::class, 'index'])->name('store.index');
Route::get('/store/{slug}', [StoreController::class, 'show'])->name('store.show');
Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
Route::get('/announcements/{slug}', [AnnouncementController::class, 'show'])->name('announcements.show');

// Knowledge Base
Route::get('/knowledgebase', [KnowledgeBaseController::class, 'index'])->name('knowledgebase.index');
Route::get('/knowledgebase/{slug}', [KnowledgeBaseController::class, 'show'])->name('knowledgebase.show');

// Legal Pages
Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/sla', 'pages.sla')->name('sla');

// Webhooks (no auth, no CSRF)
Route::withoutMiddleware([VerifyCsrfToken::class])->group(function () {
    Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])->name('webhooks.stripe');
    Route::post('/webhooks/paypal', [WebhookController::class, 'paypal'])->name('webhooks.paypal');
});

// Redirect Filament's admin login to the custom login page (so admins get 2FA)
Route::get('/admin/login', function () {
    return redirect('/login');
})->name('filament.admin.auth.login');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/two-factor/challenge', [\App\Http\Controllers\Auth\TwoFactorChallengeController::class, 'showForm'])->name('two-factor.challenge');
    Route::post('/two-factor/challenge', [\App\Http\Controllers\Auth\TwoFactorChallengeController::class, 'verify'])->name('two-factor.challenge.verify');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Password Reset Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// Email Verification
Route::get('/verify-email/{token}', function ($token) {
    $verificationToken = EmailVerificationToken::where('token', $token)
        ->with('user')
        ->first();

    if (! $verificationToken || $verificationToken->isExpired()) {
        return redirect('/')->with('error', 'Invalid or expired verification link.');
    }

    $verificationToken->user->update(['email_verified_at' => now()]);
    $verificationToken->delete();

    return redirect('/client')->with('success', 'Email verified successfully!');
})->name('verification.verify');

// Auth routes (must be logged in and email verified)
Route::middleware(['auth', 'verified.email'])->group(function () {
    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/{key}/quantity', [CartController::class, 'updateQuantity'])->name('cart.update-quantity');
    Route::delete('/cart/{key}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/promo', [CartController::class, 'applyPromo'])->name('cart.apply-promo');
    Route::delete('/cart/promo', [CartController::class, 'removePromo'])->name('cart.remove-promo');

    // Checkout
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

    // Orders
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('client.orders.show');

    // Client dashboard
    Route::prefix('client')->name('client.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Services
        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
        Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');
        Route::get('/services/{service}/status', [ServiceController::class, 'status'])->name('services.status');
        Route::post('/services/{service}/cancel', [ServiceController::class, 'cancel'])->name('services.cancel');

        // Upgrades
        Route::get('/services/{service}/upgrade', [UpgradeController::class, 'index'])->name('upgrades.index');
        Route::post('/services/{service}/upgrade', [UpgradeController::class, 'store'])->name('upgrades.store');
        Route::get('/services/{service}/upgrade/{upgrade}/pay', [UpgradeController::class, 'pay'])->name('upgrades.pay');
        Route::post('/services/{service}/upgrade/{upgrade}/pay', [UpgradeController::class, 'processPayment'])->name('upgrades.process-payment');

        // Invoices
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
        Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
        Route::post('/invoices/{invoice}/checkout-session', [InvoiceController::class, 'createCheckoutSession'])->name('invoices.create-checkout-session');
        Route::get('/invoices/{invoice}/success', [InvoiceController::class, 'success'])->name('invoices.success');
        Route::get('/invoices/{invoice}/paypal-cancel', [InvoiceController::class, 'paypalCancel'])->name('invoices.paypal-cancel');
        Route::post('/invoices/{invoice}/provision', [InvoiceController::class, 'provision'])->name('invoices.provision');
        Route::get('/stripe/session-status', [InvoiceController::class, 'sessionStatus'])->name('stripe.session-status');

        // Credits
        Route::get('/credits', [CreditController::class, 'index'])->name('credits.index');
        Route::post('/credits/deposit', [CreditController::class, 'deposit'])->name('credits.deposit');
        Route::post('/credits/checkout-session', [CreditController::class, 'createCheckoutSession'])->name('credits.create-checkout-session');
        Route::get('/credits/deposit-success', [CreditController::class, 'depositSuccess'])->name('credits.deposit-success');
        Route::get('/credits/paypal-cancel', [CreditController::class, 'paypalCancel'])->name('credits.paypal-cancel');
        Route::post('/credits/apply', [CreditController::class, 'apply'])->name('credits.apply');

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::put('/notifications', [NotificationController::class, 'update'])->name('notifications.update');
        Route::post('/notifications/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::delete('/notifications', [NotificationController::class, 'clear'])->name('notifications.clear');

        // Two-Factor Authentication
        Route::get('/two-factor', [TwoFactorController::class, 'index'])->name('two-factor.index');
        Route::get('/two-factor/setup', [TwoFactorController::class, 'showSetup'])->name('two-factor.show-setup');
        Route::post('/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
        Route::delete('/two-factor/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');

        // Tickets
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
        Route::post('/tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
        Route::post('/tickets/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
    });
});
