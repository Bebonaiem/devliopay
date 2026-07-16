@extends('layouts.app')

@section('title', 'Home')

@section('content')
{{-- Hero --}}
<section class="relative min-h-[90vh] flex items-center overflow-hidden">
    {{-- Background --}}
    <div class="absolute inset-0 hero-gradient"></div>
    <div class="absolute inset-0">
        <div class="absolute top-20 left-1/4 w-96 h-96 bg-brand-500/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-20 right-1/4 w-80 h-80 bg-purple-500/10 rounded-full blur-[100px]"></div>
    </div>
    {{-- Grid pattern --}}
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.3) 1px, transparent 0); background-size: 48px 48px;"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center max-w-4xl mx-auto">
            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass mb-8 fade-in">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-medium text-gray-300">All Systems Operational</span>
            </div>

            {{-- Heading --}}
            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black tracking-tight leading-[1.1] mb-6 fade-in">
                Premium Hosting<br>
                <span class="bg-gradient-to-r from-brand-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">Built for Speed</span>
            </h1>

            {{-- Sub --}}
            <p class="text-lg sm:text-xl text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed fade-in">
                High-performance game servers, web hosting, and VPS solutions powered by enterprise-grade infrastructure. Deploy in seconds.
            </p>

            {{-- CTA --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 fade-in">
                <a href="{{ route('store.index') }}" class="btn-primary px-8 py-3.5 rounded-2xl text-sm font-bold text-white shadow-xl shadow-brand-500/25 inline-flex items-center gap-2">
                    Browse Store <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                <a href="{{ route('knowledgebase.index') }}" class="btn-outline px-8 py-3.5 rounded-2xl text-sm font-semibold text-gray-300 hover:text-white inline-flex items-center gap-2">
                    <i data-lucide="book-open" class="w-4 h-4"></i> Knowledge Base
                </a>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-6 max-w-lg mx-auto mt-16 fade-in">
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-black text-white mb-1">99.9%</div>
                    <div class="text-[11px] text-gray-500 uppercase tracking-wider font-medium">Uptime SLA</div>
                </div>
                <div class="text-center border-x border-white/10">
                    <div class="text-2xl sm:text-3xl font-black text-white mb-1">&lt;10ms</div>
                    <div class="text-[11px] text-gray-500 uppercase tracking-wider font-medium">Latency</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-black text-white mb-1">24/7</div>
                    <div class="text-[11px] text-gray-500 uppercase tracking-wider font-medium">Expert Support</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Features --}}
<section class="py-24 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-black tracking-tight mb-4">Why DevlioPay?</h2>
            <p class="text-gray-400 max-w-xl mx-auto">Everything you need to power your projects, games, and businesses.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Feature 1 --}}
            <div class="glass rounded-2xl p-7 card-hover group">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center mb-5 group-hover:bg-brand-500/20 transition-colors">
                    <i data-lucide="cpu" class="w-6 h-6 text-brand-400"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">Enterprise Hardware</h3>
                <p class="text-sm text-gray-400 leading-relaxed">Latest AMD EPYC and Intel Xeon processors with NVMe SSD storage for maximum performance.</p>
            </div>

            {{-- Feature 2 --}}
            <div class="glass rounded-2xl p-7 card-hover group">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center mb-5 group-hover:bg-emerald-500/20 transition-colors">
                    <i data-lucide="shield-check" class="w-6 h-6 text-emerald-400"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">DDoS Protection</h3>
                <p class="text-sm text-gray-400 leading-relaxed">Advanced DDoS mitigation across all services. Your servers stay online no matter what.</p>
            </div>

            {{-- Feature 3 --}}
            <div class="glass rounded-2xl p-7 card-hover group">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center mb-5 group-hover:bg-purple-500/20 transition-colors">
                    <i data-lucide="gamepad-2" class="w-6 h-6 text-purple-400"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">Game Servers</h3>
                <p class="text-sm text-gray-400 leading-relaxed">One-click Pterodactyl deployment for Minecraft, Palworld, ARK, and dozens more games.</p>
            </div>

            {{-- Feature 4 --}}
            <div class="glass rounded-2xl p-7 card-hover group">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center mb-5 group-hover:bg-amber-500/20 transition-colors">
                    <i data-lucide="zap" class="w-6 h-6 text-amber-400"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">Instant Deployment</h3>
                <p class="text-sm text-gray-400 leading-relaxed">Servers provisioned in under 30 seconds. Get up and running immediately after purchase.</p>
            </div>

            {{-- Feature 5 --}}
            <div class="glass rounded-2xl p-7 card-hover group">
                <div class="w-12 h-12 rounded-xl bg-pink-500/10 flex items-center justify-center mb-5 group-hover:bg-pink-500/20 transition-colors">
                    <i data-lucide="globe" class="w-6 h-6 text-pink-400"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">Global Network</h3>
                <p class="text-sm text-gray-400 leading-relaxed">Strategically located data centers across North America, Europe, and Asia-Pacific.</p>
            </div>

            {{-- Feature 6 --}}
            <div class="glass rounded-2xl p-7 card-hover group">
                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center mb-5 group-hover:bg-cyan-500/20 transition-colors">
                    <i data-lucide="life-buoy" class="w-6 h-6 text-cyan-400"></i>
                </div>
                <h3 class="text-lg font-bold mb-2">24/7 Support</h3>
                <p class="text-sm text-gray-400 leading-relaxed">Expert technical support around the clock. Average response time under 15 minutes.</p>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-24 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative glass rounded-3xl p-12 sm:p-16 overflow-hidden text-center">
            <div class="absolute inset-0 hero-gradient opacity-50"></div>
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-48 bg-brand-500/20 rounded-full blur-[100px]"></div>
            <div class="relative">
                <h2 class="text-3xl sm:text-4xl font-black tracking-tight mb-4">Ready to Get Started?</h2>
                <p class="text-gray-400 max-w-lg mx-auto mb-8">Join thousands of customers who trust DevlioPay for their hosting needs.</p>
                <a href="{{ route('store.index') }}" class="btn-primary px-10 py-4 rounded-2xl text-sm font-bold text-white shadow-xl shadow-brand-500/25 inline-flex items-center gap-2">
                    Browse Plans <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
    });
</script>
@endpush
@endsection
