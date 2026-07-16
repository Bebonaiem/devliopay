@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    {{-- Header --}}
    <div class="mb-12">
        <h1 class="text-4xl font-black tracking-tight mb-3">Privacy Policy</h1>
        <p class="text-gray-400">Last updated: {{ date('F j, Y') }}</p>
    </div>

    <div class="space-y-8">
        {{-- Section 1 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">1. Information We Collect</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>We collect information you provide directly to us when you register an account, purchase services, or contact us for support:</p>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li><span class="text-gray-300 font-medium">Personal Information:</span> name, email address, billing address, and payment information</li>
                    <li><span class="text-gray-300 font-medium">Account Information:</span> username, password (encrypted), and account preferences</li>
                    <li><span class="text-gray-300 font-medium">Server Data:</span> configurations, files, and content you store on our servers</li>
                    <li><span class="text-gray-300 font-medium">Communication:</span> support tickets, emails, and other correspondence</li>
                </ul>
                <p>We also automatically collect certain information when you access the Service, including IP address, browser type, operating system, and usage data.</p>
            </div>
        </div>

        {{-- Section 2 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">2. How We Use Your Information</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>We use the information we collect to:</p>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li>Provide, maintain, and improve our services</li>
                    <li>Process transactions and send related information (receipts, invoices, confirmations)</li>
                    <li>Send technical notices, updates, security alerts, and support messages</li>
                    <li>Respond to your comments, questions, and customer service requests</li>
                    <li>Detect, investigate, and prevent fraudulent transactions and other illegal activities</li>
                    <li>Comply with legal obligations and enforce our terms</li>
                </ul>
            </div>
        </div>

        {{-- Section 3 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">3. Information Sharing</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>We do not sell, trade, or otherwise transfer your personal information to third parties except in the following circumstances:</p>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li><span class="text-gray-300 font-medium">Service Providers:</span> We may share information with third-party vendors who perform services on our behalf (payment processors, hosting providers)</li>
                    <li><span class="text-gray-300 font-medium">Legal Requirements:</span> We may disclose information if required by law, regulation, or valid legal process</li>
                    <li><span class="text-gray-300 font-medium">Business Transfers:</span> In connection with a merger, acquisition, or sale of assets, your information may be transferred</li>
                    <li><span class="text-gray-300 font-medium">With Your Consent:</span> We may share information with your explicit consent</li>
                </ul>
            </div>
        </div>

        {{-- Section 4 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">4. Data Security</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>We implement industry-standard security measures to protect your personal information, including:</p>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li>Encryption of data in transit (TLS/SSL) and at rest</li>
                    <li>Regular security audits and vulnerability assessments</li>
                    <li>Access controls and authentication mechanisms</li>
                    <li>Automated monitoring for suspicious activity</li>
                </ul>
                <p>While we strive to protect your personal information, no method of electronic transmission or storage is 100% secure. We cannot guarantee absolute security.</p>
            </div>
        </div>

        {{-- Section 5 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">5. Data Retention</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>We retain your personal information for as long as your account is active or as needed to provide you services. We will also retain your information as necessary to comply with legal obligations, resolve disputes, and enforce our agreements.</p>
                <p>Upon account deletion, we will remove your personal data within 30 days, except where retention is required by law or for legitimate business purposes.</p>
            </div>
        </div>

        {{-- Section 6 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">6. Your Rights</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>Depending on your location, you may have the following rights regarding your personal data:</p>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li><span class="text-gray-300 font-medium">Access:</span> Request a copy of the personal data we hold about you</li>
                    <li><span class="text-gray-300 font-medium">Correction:</span> Request correction of inaccurate or incomplete data</li>
                    <li><span class="text-gray-300 font-medium">Deletion:</span> Request deletion of your personal data</li>
                    <li><span class="text-gray-300 font-medium">Portability:</span> Request transfer of your data to another service</li>
                    <li><span class="text-gray-300 font-medium">Objection:</span> Object to processing of your personal data</li>
                </ul>
                <p>To exercise any of these rights, please contact us through our support system.</p>
            </div>
        </div>

        {{-- Section 7 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">7. Cookies and Tracking</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>We use cookies and similar tracking technologies to maintain your session, remember your preferences, and analyze usage of the Service. You can instruct your browser to refuse all cookies, though some features of the Service may not function properly without them.</p>
                <p>We use essential cookies for authentication and session management, and optional analytics cookies to understand how our Service is used.</p>
            </div>
        </div>

        {{-- Section 8 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">8. Children's Privacy</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>The Service is not directed to individuals under the age of 18. We do not knowingly collect personal information from children under 18. If we become aware that we have collected personal information from a child under 18, we will take steps to delete such information.</p>
            </div>
        </div>

        {{-- Section 9 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">9. Changes to This Policy</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last updated" date. Your continued use of the Service after any changes constitutes acceptance of the updated policy.</p>
            </div>
        </div>

        {{-- Section 10 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">10. Contact Us</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>If you have any questions about this Privacy Policy, please contact us through our support system or by email at privacy@{{ config('app.domain', 'devliopay.com') }}.</p>
            </div>
        </div>
    </div>
</div>
@endsection
