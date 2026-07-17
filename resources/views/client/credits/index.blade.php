@extends('layouts.client')

@section('title', 'Credit Balance')

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="creditDeposit()" x-init="init()">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Credit Balance</h1>
        <p class="text-sm text-gray-400 mt-1">Manage your account credits and add funds.</p>
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
    @if(session('error'))
    <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-sm text-red-300" x-data="{ show: true }" x-show="show" x-transition>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2"><i data-lucide="alert-circle" class="w-4 h-4"></i><span>{{ session('error') }}</span></div>
            <button @click="show = false" class="text-red-400 hover:text-red-300"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    </div>
    @endif

    {{-- Balance Card --}}
    <div class="glass rounded-2xl p-8 text-center relative overflow-hidden">
        <div class="absolute inset-0 hero-gradient opacity-30"></div>
        <div class="relative">
            <p class="text-sm text-gray-400 mb-2">Available Balance</p>
            <div class="text-5xl font-black mb-1">{{ $defaultCurrencySymbol }}{{ number_format($balance ?? 0, 2) }}</div>
            <p class="text-xs text-gray-500">Credits are applied automatically to invoices</p>
        </div>
    </div>

    {{-- Add Funds Form --}}
    <div x-show="!stripeReady" class="glass rounded-2xl p-6 space-y-5">
        <h2 class="text-sm font-semibold">Add Funds</h2>
        <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Amount</label>
            <div class="flex gap-2">
                @foreach([5, 10, 25, 50] as $amt)
                <button type="button" @click="amount = {{ $amt }}" class="flex-1 px-3 py-2.5 rounded-xl text-xs font-semibold text-gray-400 bg-white/[0.03] border border-white/10 hover:border-brand-500/30 hover:text-brand-400 transition-all" :class="amount === {{ $amt }} ? 'border-brand-500/50 text-brand-400 bg-brand-500/5' : ''">{{ $defaultCurrencySymbol }}{{ $amt }}</button>
                @endforeach
            </div>
        </div>
        <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">{{ $defaultCurrencySymbol }}</span>
            <input x-model.number="amount" type="number" min="5" step="0.01" class="w-full bg-white/[0.03] border border-white/10 rounded-xl pl-8 pr-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20" placeholder="0.00">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Payment Method</label>
            <div class="space-y-2">
                @if(isset($availableGateways) && count($availableGateways) > 0)
                    @foreach($availableGateways as $name => $gateway)
                    <label @click="paymentMethod = '{{ $name }}'" class="flex items-center gap-3 p-4 rounded-xl bg-white/[0.02] border cursor-pointer transition-all"
                        :class="paymentMethod === '{{ $name }}' ? 'border-brand-500/50 bg-brand-500/5' : 'border-white/10 hover:border-brand-500/30'">
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
                            :class="paymentMethod === '{{ $name }}' ? 'border-brand-500' : 'border-gray-600'">
                            <div class="w-2.5 h-2.5 rounded-full bg-brand-500 transition-transform"
                                :class="paymentMethod === '{{ $name }}' ? 'scale-100' : 'scale-0'"></div>
                        </div>
                        <i data-lucide="credit-card" class="w-5 h-5 text-gray-400"></i>
                        <p class="text-sm font-medium">{{ $gateway->getDisplayName() }}</p>
                    </label>
                    @endforeach
                @endif
            </div>
        </div>

        <button @click="startDeposit()" :disabled="processing || amount < 5 || !paymentMethod" class="btn-primary w-full py-3 rounded-xl text-sm font-semibold text-white shadow-lg shadow-brand-500/20 inline-flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-show="!processing" class="inline-flex items-center gap-2"><i data-lucide="credit-card" class="w-4 h-4"></i> Add {{ $defaultCurrencySymbol }}<span x-text="amount ? amount.toFixed(2) : '0.00'"></span></span>
            <span x-show="processing" class="inline-flex items-center gap-2"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Processing...</span>
        </button>
    </div>

    {{-- Stripe Loading --}}
    <div x-show="stripeLoading" class="glass rounded-2xl p-8 text-center">
        <svg class="animate-spin w-8 h-8 text-brand-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        <p class="text-sm text-gray-400">Loading secure payment form...</p>
    </div>

    {{-- Custom Payment Element --}}
    <div x-show="stripeReady" class="glass rounded-2xl p-6 space-y-5">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold">Add Funds - {{ $defaultCurrencySymbol }}<span x-text="amount ? amount.toFixed(2) : '0.00'"></span></h2>
            <button @click="resetStripe()" class="text-xs text-gray-500 hover:text-gray-300 transition-colors"><i data-lucide="arrow-left" class="w-3 h-3 inline mr-1"></i> Back</button>
        </div>

        <div class="rounded-xl border border-white/10 bg-white/[0.02] p-4">
            <div id="stripe-credit-payment-element" class="min-h-[200px]"></div>
        </div>

        <button @click="confirmPayment()" :disabled="processing" class="btn-primary w-full py-3.5 rounded-xl text-sm font-bold text-white shadow-lg shadow-brand-500/20 inline-flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-show="!processing" class="inline-flex items-center gap-2"><i data-lucide="lock" class="w-4 h-4"></i> Add {{ $defaultCurrencySymbol }}<span x-text="amount ? amount.toFixed(2) : '0.00'"></span></span>
            <span x-show="processing" class="inline-flex items-center gap-2"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Processing...</span>
        </button>

        <p class="text-center text-[10px] text-gray-600">Secured by <span class="font-semibold text-gray-500">Stripe</span></p>
    </div>

    {{-- Error --}}
    <div x-show="error" class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-sm text-red-300">
        <div class="flex items-center gap-2"><i data-lucide="alert-circle" class="w-4 h-4"></i><span x-text="error"></span></div>
    </div>

    {{-- Recent Transactions --}}
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5"><h2 class="text-sm font-semibold">Recent Transactions</h2></div>
        @if(isset($history) && count($history) > 0)
            <div class="divide-y divide-white/5">
                @foreach($history as $tx)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl {{ ($tx->amount ?? 0) > 0 ? 'bg-emerald-500/10' : 'bg-red-500/10' }} flex items-center justify-center">
                            <i data-lucide="{{ ($tx->amount ?? 0) > 0 ? 'plus' : 'minus' }}" class="w-4 h-4 {{ ($tx->amount ?? 0) > 0 ? 'text-emerald-400' : 'text-red-400' }}"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium">{{ $tx->description ?? 'Credit transaction' }}</p>
                            <p class="text-xs text-gray-500">{{ $tx->created_at ? $tx->created_at->diffForHumans() : '' }}</p>
                        </div>
                    </div>
                    <span class="text-sm font-semibold {{ ($tx->amount ?? 0) > 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ ($tx->amount ?? 0) > 0 ? '+' : '' }}{{ $defaultCurrencySymbol }}{{ number_format(abs($tx->amount ?? 0), 2) }}
                    </span>
                </div>
                @endforeach
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <i data-lucide="wallet" class="w-10 h-10 text-gray-600 mx-auto mb-3"></i>
                <p class="text-sm text-gray-400">No transactions yet</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function creditDeposit() {
    return {
        amount: 10,
        paymentMethod: null,
        processing: false,
        stripeLoading: false,
        stripeReady: false,
        error: null,
        stripe: null,
        checkout: null,
        paymentElement: null,

        init() {
            this.stripe = window.Stripe ? window.Stripe('{{ $stripePublishableKey }}') : null;
            @if(session('show_stripe_checkout'))
            this.$nextTick(() => { this.startDeposit(); });
            @endif
        },

        async startDeposit() {
            this.error = null;
            if (this.amount < 5) { this.error = 'Minimum deposit amount is {{ $defaultCurrencySymbol }}5.00'; return; }
            this.processing = true;

            if (!this.paymentMethod) { this.error = 'Please select a payment method.'; this.processing = false; return; }

            if (this.paymentMethod !== 'stripe') {
                try {
                    const response = await fetch('{{ route("client.credits.create-checkout-session") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ amount: this.amount, payment_method: this.paymentMethod }),
                    });

                    const data = await response.json();
                    if (!response.ok || !data.redirectUrl) {
                        throw new Error(data.error || 'Failed to create payment session.');
                    }

                    window.open(data.redirectUrl, 'paypal_checkout', 'width=500,height=700,top=100,left=300,scrollbars=yes,resizable=yes');
                    this.processing = false;
                } catch (e) {
                    this.error = e.message || 'Failed to start payment.';
                    this.processing = false;
                }
                return;
            }

            if (!this.stripe) { this.error = 'Stripe is not configured.'; this.processing = false; return; }

            this.stripeLoading = true;
            try {
                const response = await fetch('{{ route("client.credits.create-checkout-session") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ amount: this.amount, payment_method: 'stripe' }),
                });

                const data = await response.json();
                if (!response.ok || !data.clientSecret) throw new Error(data.error || 'Failed to create payment session.');

                this.stripeLoading = false;
                this.stripeReady = true;
                await this.$nextTick();

                this.checkout = this.stripe.initCheckoutElementsSdk({
                    clientSecret: data.clientSecret,
                    elementsOptions: {
                        appearance: {
                            theme: 'night',
                            variables: { colorPrimary: '#6366f1', colorBackground: '#1a1b2e', colorText: '#e2e8f0', colorDanger: '#ef4444', fontFamily: 'Inter, sans-serif', borderRadius: '12px', spacingUnit: '4px' },
                            rules: { '.Input': { border: '1px solid rgba(255,255,255,0.1)', backgroundColor: 'rgba(255,255,255,0.03)' }, '.Input:focus': { border: '1px solid #6366f1', boxShadow: '0 0 0 1px #6366f1' } },
                        },
                    },
                });

                this.paymentElement = this.checkout.createPaymentElement();
                this.paymentElement.mount('#stripe-credit-payment-element');
            } catch (e) {
                this.stripeLoading = false;
                this.error = e.message || 'Failed to initialize payment.';
            }
            this.processing = false;
        },

        async confirmPayment() {
            this.processing = true;
            this.error = null;
            try {
                const loadActionsResult = await this.checkout.loadActions();
                if (loadActionsResult.type === 'error') { this.error = loadActionsResult.error.message; this.processing = false; return; }

                const result = await loadActionsResult.actions.confirm();
                if (result.type === 'error') { this.error = result.error.message; this.processing = false; }
            } catch (e) { this.error = e.message || 'Payment failed.'; this.processing = false; }
        },

        resetStripe() {
            if (this.paymentElement) { this.paymentElement.unmount(); this.paymentElement = null; }
            this.checkout = null; this.stripeReady = false; this.stripeLoading = false; this.error = null; this.processing = false;
        }
    }
}
</script>
@endpush
@endsection
