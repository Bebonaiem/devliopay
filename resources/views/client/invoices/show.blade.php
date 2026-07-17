@extends('layouts.client')

@section('title', 'Invoice #' . ($invoice->invoice_number ?? $invoice->id))

@section('content')
<div class="space-y-6" x-data="invoicePayment()" x-init="init()">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('client.invoices.index') }}" class="hover:text-gray-300 transition-colors">Invoices</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-300">#{{ $invoice->invoice_number ?? $invoice->id }}</span>
    </div>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Invoice #{{ $invoice->invoice_number ?? $invoice->id }}</h1>
            <p class="text-sm text-gray-400 mt-1">Issued {{ $invoice->created_at ? $invoice->created_at->format('M j, Y') : '-' }}</p>
        </div>
        <span class="px-3 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider w-fit
            {{ ($invoice->status ?? '') === 'paid' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '' }}
            {{ ($invoice->status ?? '') === 'unpaid' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : '' }}
            {{ ($invoice->status ?? '') === 'overdue' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : '' }}
            {{ !in_array($invoice->status ?? '', ['paid','unpaid','overdue']) ? 'bg-gray-500/10 text-gray-400 border border-gray-500/20' : '' }}
        ">{{ $invoice->status ?? 'unknown' }}</span>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-sm text-emerald-300" x-data="{ show: true }" x-show="show" x-transition>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i><span>{{ session('success') }}</span></div>
            <button @click="show = false" class="text-emerald-400 hover:text-emerald-300"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    </div>
    @endif
    @if(session('warning'))
    <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-sm text-amber-300" x-data="{ show: true }" x-show="show" x-transition>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2"><i data-lucide="alert-triangle" class="w-4 h-4"></i><span>{{ session('warning') }}</span></div>
            <button @click="show = false" class="text-amber-400 hover:text-amber-300"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-sm text-red-300" x-data="{ show: true }" x-show="show" x-transition>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2"><i data-lucide="alert-circle" class="w-4 h-4"></i><span>{{ session('error') }}</span></div>
            <button @click="show = false" class="text-red-400 hover:text-red-300"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Invoice Items + Payment --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Invoice Items Table --}}
            <div class="glass rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5">
                    <h2 class="text-sm font-semibold">Invoice Items</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Description</th>
                                <th class="text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Qty</th>
                                <th class="text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @if(isset($invoice->items) && count($invoice->items) > 0)
                                @foreach($invoice->items as $item)
                                <tr class="hover:bg-white/[0.02]">
                                    <td class="px-6 py-4 text-sm text-gray-300">{{ $item->description ?? $item->name ?? 'Item' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-400 text-center">{{ $item->quantity ?? 1 }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-right">{{ $defaultCurrencySymbol }}{{ number_format(($item->amount ?? 0) * ($item->quantity ?? 1), 2) }}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr><td colspan="3" class="px-6 py-8 text-center text-sm text-gray-400">No items found</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Payment Section (only for unpaid invoices) --}}
            @if(($invoice->status ?? '') !== 'paid')
            <div class="glass rounded-2xl p-6">
                <h2 class="text-sm font-semibold mb-4">Pay Invoice</h2>

                {{-- Payment Method Selection --}}
                <div x-show="!stripeReady" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Payment Method</label>
                        <div class="space-y-2">
                            @if((auth()->user()->balance ?? 0) > 0)
                            <label class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition-all"
                                :class="selectedMethod === 'balance' ? 'border-brand-500/60 bg-brand-500/10' : 'border-white/10 bg-white/[0.02] hover:border-white/20'">
                                <input type="radio" name="payment_method" value="balance" class="sr-only" @click="selectedMethod = 'balance'">
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                                    :class="selectedMethod === 'balance' ? 'border-brand-500' : 'border-gray-600'">
                                    <div class="w-2.5 h-2.5 rounded-full bg-brand-500 transition-transform"
                                        :class="selectedMethod === 'balance' ? 'scale-100' : 'scale-0'"></div>
                                </div>
                                <i data-lucide="wallet" class="w-5 h-5 shrink-0"
                                    :class="selectedMethod === 'balance' ? 'text-brand-400' : 'text-gray-400'"></i>
                                <div>
                                    <p class="text-sm font-medium">Credit Balance</p>
                                    <p class="text-xs text-gray-500">Available: {{ $defaultCurrencySymbol }}{{ number_format(auth()->user()->balance ?? 0, 2) }}</p>
                                </div>
                            </label>
                            @endif
                            @if(isset($availableGateways) && count($availableGateways) > 0)
                                @foreach($availableGateways as $name => $gateway)
                                <label class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition-all"
                                    :class="selectedMethod === '{{ $name }}' ? 'border-brand-500/60 bg-brand-500/10' : 'border-white/10 bg-white/[0.02] hover:border-white/20'">
                                    <input type="radio" name="payment_method" value="{{ $name }}" class="sr-only" @click="selectedMethod = '{{ $name }}'">
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                                        :class="selectedMethod === '{{ $name }}' ? 'border-brand-500' : 'border-gray-600'">
                                        <div class="w-2.5 h-2.5 rounded-full bg-brand-500 transition-transform"
                                            :class="selectedMethod === '{{ $name }}' ? 'scale-100' : 'scale-0'"></div>
                                    </div>
                                    <i data-lucide="credit-card" class="w-5 h-5 shrink-0"
                                        :class="selectedMethod === '{{ $name }}' ? 'text-brand-400' : 'text-gray-400'"></i>
                                    <div>
                                        <p class="text-sm font-medium">{{ $gateway->getDisplayName() }}</p>
                                        <p class="text-xs text-gray-500">Pay with {{ $gateway->getDisplayName() }}</p>
                                    </div>
                                </label>
                                @endforeach
                            @endif
                            @if((auth()->user()->balance ?? 0) <= 0 && (!isset($availableGateways) || count($availableGateways) === 0))
                            <div class="p-4 rounded-xl bg-amber-500/5 border border-amber-500/10 text-center">
                                <i data-lucide="alert-triangle" class="w-6 h-6 text-amber-400 mx-auto mb-2"></i>
                                <p class="text-sm text-gray-300">No payment methods available.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @if((auth()->user()->balance ?? 0) > 0 || (isset($availableGateways) && count($availableGateways) > 0))
                    <button @click="startPayment()" :disabled="processing" class="btn-primary w-full py-3 rounded-xl text-sm font-bold text-white shadow-lg shadow-brand-500/20 inline-flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!processing" class="inline-flex items-center gap-2"><i data-lucide="credit-card" class="w-4 h-4"></i> Pay {{ $defaultCurrencySymbol }}{{ number_format($invoice->total ?? 0, 2) }}</span>
                        <span x-show="processing" class="inline-flex items-center gap-2"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Processing...</span>
                    </button>
                    @endif
                </div>

                {{-- Stripe Loading --}}
                <div x-show="stripeLoading" class="text-center py-8">
                    <svg class="animate-spin w-8 h-8 text-brand-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <p class="text-sm text-gray-400">Loading secure payment form...</p>
                </div>

                {{-- Custom Payment Element --}}
                <div x-show="stripeReady" class="space-y-5">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Payment Details</p>
                        <button @click="resetStripe()" class="text-xs text-gray-500 hover:text-gray-300 transition-colors"><i data-lucide="arrow-left" class="w-3 h-3 inline mr-1"></i> Back</button>
                    </div>

                    {{-- Stripe Payment Element (dark themed) --}}
                    <div class="rounded-xl border border-white/10 bg-white/[0.02] p-4">
                        <div id="stripe-payment-element" class="min-h-[200px]"></div>
                    </div>

                    {{-- Submit Payment Button --}}
                    <button
                        @click="confirmPayment()"
                        :disabled="processing"
                        class="btn-primary w-full py-3.5 rounded-xl text-sm font-bold text-white shadow-lg shadow-brand-500/20 inline-flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-show="!processing" class="inline-flex items-center gap-2"><i data-lucide="lock" class="w-4 h-4"></i> Pay {{ $defaultCurrencySymbol }}{{ number_format($invoice->total ?? 0, 2) }}</span>
                        <span x-show="processing" class="inline-flex items-center gap-2"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Processing...</span>
                    </button>

                    {{-- Powered by Stripe --}}
                    <p class="text-center text-[10px] text-gray-600">Secured by <span class="font-semibold text-gray-500">Stripe</span></p>
                </div>

                {{-- Error --}}
                <div x-show="error" class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-sm text-red-300 mt-4">
                    <div class="flex items-center gap-2"><i data-lucide="alert-circle" class="w-4 h-4"></i><span x-text="error"></span></div>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Order Summary (always visible during payment) --}}
            <div class="glass rounded-2xl p-6" x-show="stripeReady">
                <h2 class="text-sm font-semibold mb-4">Order Summary</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Invoice</span>
                        <span class="text-sm font-medium">#{{ $invoice->invoice_number ?? $invoice->id }}</span>
                    </div>
                    @if(isset($invoice->items) && count($invoice->items) > 0)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Items</span>
                        <span class="text-sm font-medium">{{ count($invoice->items) }}</span>
                    </div>
                    @endif
                    @if(($invoice->tax ?? 0) > 0)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Tax</span>
                        <span class="text-sm font-medium">{{ $defaultCurrencySymbol }}{{ number_format($invoice->tax, 2) }}</span>
                    </div>
                    @endif
                    @if(($invoice->credit ?? 0) > 0)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Credit</span>
                        <span class="text-sm font-medium text-emerald-400">-{{ $defaultCurrencySymbol }}{{ number_format($invoice->credit, 2) }}</span>
                    </div>
                    @endif
                    <hr class="border-white/5">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold">Total</span>
                        <span class="text-lg font-black">{{ $defaultCurrencySymbol }}{{ number_format($invoice->total ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Summary (default state) --}}
            <div class="glass rounded-2xl p-6" x-show="!stripeReady">
                <h2 class="text-sm font-semibold mb-4">Summary</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Subtotal</span>
                        <span class="text-sm font-medium">{{ $defaultCurrencySymbol }}{{ number_format($invoice->subtotal ?? $invoice->total ?? 0, 2) }}</span>
                    </div>
                    @if(($invoice->tax ?? 0) > 0)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Tax</span>
                        <span class="text-sm font-medium">{{ $defaultCurrencySymbol }}{{ number_format($invoice->tax, 2) }}</span>
                    </div>
                    @endif
                    @if(($invoice->credit ?? 0) > 0)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Credit Applied</span>
                        <span class="text-sm font-medium text-emerald-400">-{{ $defaultCurrencySymbol }}{{ number_format($invoice->credit, 2) }}</span>
                    </div>
                    @endif
                    <hr class="border-white/5">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold">Total Due</span>
                        <span class="text-lg font-black">{{ $defaultCurrencySymbol }}{{ number_format($invoice->total ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment Info (paid state) --}}
            @if(($invoice->status ?? '') === 'paid')
            <div class="glass rounded-2xl p-6">
                <h2 class="text-sm font-semibold mb-4">Payment Information</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Payment Method</span>
                        <span class="text-sm text-gray-300">{{ $invoice->payment_method ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Transaction ID</span>
                        <span class="text-xs font-mono text-gray-400">{{ $invoice->transaction_id ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Paid Date</span>
                        <span class="text-sm text-gray-300">{{ $invoice->paid_at ? $invoice->paid_at->format('M j, Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            @if($invoice->service && $invoice->service->status === 'pending')
            <div class="glass rounded-2xl p-6 border border-amber-500/10">
                <h2 class="text-sm font-semibold text-amber-400 mb-2">Server Not Provisioned</h2>
                <p class="text-xs text-gray-400 mb-4">Your payment was received but the server hasn't been set up yet.</p>
                <form method="POST" action="{{ route('client.invoices.provision', $invoice->id) }}">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-brand-500 hover:bg-brand-400 transition-all inline-flex items-center justify-center gap-2">
                        <i data-lucide="server" class="w-3.5 h-3.5"></i> Provision Server Now
                    </button>
                </form>
            </div>
            @endif

            @if($invoice->service && $invoice->service->status === 'active')
            <div class="glass rounded-2xl p-6">
                <h2 class="text-sm font-semibold mb-4">Service</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Status</span>
                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</span>
                    </div>
                    <a href="{{ route('client.services.show', $invoice->service->id) }}" class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-brand-400 bg-brand-500/10 hover:bg-brand-500/20 border border-brand-500/20 transition-all inline-flex items-center justify-center gap-2">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i> View Service
                    </a>
                </div>
            </div>
            @endif
            @endif

            {{-- Actions --}}
            <div class="glass rounded-2xl p-6">
                <h2 class="text-sm font-semibold mb-4">Actions</h2>
                <div class="space-y-2">
                    <a href="{{ route('client.invoices.pdf', $invoice->id) }}" class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-gray-300 hover:text-white bg-white/[0.03] hover:bg-white/[0.06] border border-white/5 transition-all text-left block">
                        <i data-lucide="download" class="w-3.5 h-3.5 inline mr-2"></i> Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function invoicePayment() {
    return {
        selectedMethod: '',
        processing: false,
        stripeLoading: false,
        stripeReady: false,
        error: null,
        stripe: null,
        checkout: null,
        paymentElement: null,

        init() {
            this.stripe = window.Stripe ? window.Stripe('{{ $stripePublishableKey }}') : null;
        },

        async startPayment() {
            this.error = null;
            this.processing = true;

            // Balance payment
            if (this.selectedMethod === 'balance') {
                this.processing = false;
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("client.invoices.pay", $invoice->id) }}';
                form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="payment_method" value="balance">';
                document.body.appendChild(form);
                form.submit();
                return;
            }

            // PayPal redirect in new tab
            if (this.selectedMethod === 'paypal') {
                try {
                    const response = await fetch('{{ route("client.invoices.create-checkout-session", $invoice->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ payment_method: 'paypal' }),
                    });

                    const data = await response.json();
                    if (!response.ok || !data.redirectUrl) {
                        throw new Error(data.error || 'Failed to create PayPal session.');
                    }

                    window.open(data.redirectUrl, 'paypal_checkout', 'width=500,height=700,top=100,left=300,scrollbars=yes,resizable=yes');
                    this.processing = false;
                } catch (e) {
                    this.error = e.message || 'Failed to start PayPal payment.';
                    this.processing = false;
                }
                return;
            }

            // Stripe - fetch client secret
            if (this.selectedMethod === 'stripe') {
                if (!this.stripe) {
                    this.error = 'Stripe is not configured. Please contact support.';
                    this.processing = false;
                    return;
                }

                this.stripeLoading = true;

                try {
                    const response = await fetch('{{ route("client.invoices.create-checkout-session", $invoice->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ payment_method: 'stripe' }),
                    });

                    const data = await response.json();
                    if (!response.ok || !data.clientSecret) {
                        throw new Error(data.error || 'Failed to create payment session.');
                    }

                    this.stripeLoading = false;
                    this.stripeReady = true;

                    await this.$nextTick();

                    // Initialize Checkout Elements SDK (synchronous in Dahlia)
                    this.checkout = this.stripe.initCheckoutElementsSdk({
                        clientSecret: data.clientSecret,
                        elementsOptions: {
                            appearance: {
                                theme: 'night',
                                variables: {
                                    colorPrimary: '#6366f1',
                                    colorBackground: '#1a1b2e',
                                    colorText: '#e2e8f0',
                                    colorDanger: '#ef4444',
                                    fontFamily: 'Inter, sans-serif',
                                    borderRadius: '12px',
                                    spacingUnit: '4px',
                                },
                                rules: {
                                    '.Input': {
                                        border: '1px solid rgba(255,255,255,0.1)',
                                        backgroundColor: 'rgba(255,255,255,0.03)',
                                    },
                                    '.Input:focus': {
                                        border: '1px solid #6366f1',
                                        boxShadow: '0 0 0 1px #6366f1',
                                    },
                                },
                            },
                        },
                    });

                    // Create and mount the Payment Element
                    this.paymentElement = this.checkout.createPaymentElement();
                    this.paymentElement.mount('#stripe-payment-element');

                } catch (e) {
                    this.stripeLoading = false;
                    this.error = e.message || 'Failed to initialize payment. Please try again.';
                }

                this.processing = false;
            }
        },

        async confirmPayment() {
            this.processing = true;
            this.error = null;

            try {
                const loadActionsResult = await this.checkout.loadActions();
                if (loadActionsResult.type === 'error') {
                    this.error = loadActionsResult.error.message;
                    this.processing = false;
                    return;
                }

                const result = await loadActionsResult.actions.confirm();

                if (result.type === 'error') {
                    this.error = result.error.message;
                    this.processing = false;
                }
                // On success, Stripe redirects to the return_url set on the Checkout Session
            } catch (e) {
                this.error = e.message || 'Payment failed. Please try again.';
                this.processing = false;
            }
        },

        resetStripe() {
            if (this.paymentElement) {
                this.paymentElement.unmount();
                this.paymentElement = null;
            }
            this.checkout = null;
            this.stripeReady = false;
            this.stripeLoading = false;
            this.error = null;
            this.processing = false;
        }
    }
}
</script>
@endpush
@endsection
