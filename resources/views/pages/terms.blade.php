@extends('layouts.app')

@section('title', 'Terms of Service')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    {{-- Header --}}
    <div class="mb-12">
        <h1 class="text-4xl font-black tracking-tight mb-3">Terms of Service</h1>
        <p class="text-gray-400">Last updated: {{ date('F j, Y') }}</p>
    </div>

    <div class="space-y-8">
        {{-- Section 1 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">1. Acceptance of Terms</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>By accessing and using {{ config('app.name', 'DevlioPay') }} ("the Service"), you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our Service.</p>
                <p>These terms apply to all users of the Service, including browsers, customers, merchants, and contributors of content.</p>
            </div>
        </div>

        {{-- Section 2 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">2. Account Registration</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>To use certain features of the Service, you must register for an account. You agree to provide accurate, current, and complete information during registration.</p>
                <p>You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</p>
                <p>You must notify us immediately of any unauthorized use of your account or any other breach of security.</p>
            </div>
        </div>

        {{-- Section 3 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">3. Services and Products</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>We provide hosting services including but not limited to game servers, web hosting, and VPS solutions. The specific features, pricing, and availability of services are subject to change without notice.</p>
                <p>We reserve the right to modify, suspend, or discontinue any part of the Service at any time without prior notice.</p>
                <p>Server specifications, including but not limited to CPU, RAM, storage, and bandwidth, are as advertised at the time of purchase and may be subject to fair usage policies.</p>
            </div>
        </div>

        {{-- Section 4 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">4. Payments and Billing</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>All services are billed in advance on a recurring basis unless otherwise specified. Prices are listed in the currency specified at checkout and are exclusive of applicable taxes unless stated otherwise.</p>
                <p>Payment must be received in full before services are provisioned or renewed. We accept payment methods as displayed during the checkout process.</p>
                <p>Failed or declined payments may result in service suspension or termination. You are responsible for ensuring your payment information is current and valid.</p>
                <p>Refund requests are handled on a case-by-case basis within our refund policy guidelines.</p>
            </div>
        </div>

        {{-- Section 5 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">5. Acceptable Use</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>You agree not to use the Service for any unlawful purpose or in any way that could damage, disable, overburden, or impair the Service. Prohibited activities include but are not limited to:</p>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li>Hosting, distributing, or transmitting malware, viruses, or malicious code</li>
                    <li>Running unauthorized network scanners, exploit tools, or denial-of-service attacks</li>
                    <li>Hosting adult content, illegal gambling, or fraudulent services</li>
                    <li>Abusing server resources beyond fair usage limits</li>
                    <li>Circumventing security measures or accessing unauthorized areas</li>
                    <li>Spamming, phishing, or engaging in social engineering attacks</li>
                </ul>
                <p>Violation of acceptable use policies may result in immediate service suspension or termination without refund.</p>
            </div>
        </div>

        {{-- Section 6 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">6. Data and Liability</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>We are not liable for any data loss, corruption, or damage arising from your use of the Service, including but not limited to service interruptions, hardware failures, or software issues.</p>
            </div>
        </div>

        {{-- Section 7 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">7. Limitation of Liability</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>To the maximum extent permitted by law, {{ config('app.name', 'DevlioPay') }} shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of or inability to use the Service.</p>
                <p>Our total liability for any claim arising from the Service shall not exceed the amount paid by you for the Service during the twelve (12) months preceding the claim.</p>
            </div>
        </div>

        {{-- Section 8 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">8. Termination</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>We reserve the right to suspend or terminate your access to the Service at any time, with or without cause, and with or without notice. Grounds for termination include but are not limited to violation of these Terms.</p>
                <p>Upon termination, your right to use the Service ceases immediately. We may delete your data and account information after a reasonable period following termination.</p>
            </div>
        </div>

        {{-- Section 9 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">9. Changes to Terms</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>We reserve the right to modify these Terms of Service at any time. Changes will be effective immediately upon posting to our website. Your continued use of the Service after changes are posted constitutes your acceptance of the updated terms.</p>
                <p>We encourage you to review these Terms periodically for any changes.</p>
            </div>
        </div>

        {{-- Section 10 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">10. Contact</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>If you have any questions about these Terms of Service, please contact us through our support system or by email at support@{{ config('app.domain', 'devliopay.com') }}.</p>
            </div>
        </div>
    </div>
</div>
@endsection
