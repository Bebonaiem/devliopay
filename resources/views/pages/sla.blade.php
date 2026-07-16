@extends('layouts.app')

@section('title', 'Service Level Agreement')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    {{-- Header --}}
    <div class="mb-12">
        <h1 class="text-4xl font-black tracking-tight mb-3">Service Level Agreement</h1>
        <p class="text-gray-400">Last updated: {{ date('F j, Y') }}</p>
    </div>

    <div class="space-y-8">
        {{-- Section 1 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">1. Uptime Guarantee</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>{{ config('app.name', 'DevlioPay') }} guarantees a minimum uptime of <span class="text-brand-400 font-semibold">99.9%</span> for all paid services on a monthly basis. Uptime is calculated as total minutes in the month minus downtime minutes, divided by total minutes in the month.</p>
                <p>Scheduled maintenance windows are excluded from uptime calculations. We will provide at least 48 hours notice for planned maintenance.</p>
            </div>
        </div>

        {{-- Section 2 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">2. Uptime Monitoring</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>Our monitoring systems continuously check server availability. Downtime is defined as a period where the service is completely unavailable and unresponsive to legitimate requests.</p>
                <p>The following are excluded from downtime calculations:</p>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li>Scheduled maintenance with advance notice</li>
                    <li>Force majeure events (natural disasters, power outages, etc.)</li>
                    <li>Issues caused by the customer's own configuration or software</li>
                    <li>Network issues upstream from our infrastructure</li>
                </ul>
            </div>
        </div>

        {{-- Section 3 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">3. Service Credits</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>If we fail to meet our 99.9% uptime guarantee, customers are eligible for service credits based on the following schedule:</p>
                <div class="mt-4 glass-light rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="text-left px-4 py-3 text-gray-300 font-semibold">Monthly Uptime</th>
                                <th class="text-right px-4 py-3 text-gray-300 font-semibold">Credit Percentage</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-400">
                            <tr class="border-b border-white/5">
                                <td class="px-4 py-2.5">99.0% - 99.9%</td>
                                <td class="text-right px-4 py-2.5">10%</td>
                            </tr>
                            <tr class="border-b border-white/5">
                                <td class="px-4 py-2.5">95.0% - 99.0%</td>
                                <td class="text-right px-4 py-2.5">25%</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2.5">Below 95.0%</td>
                                <td class="text-right px-4 py-2.5">50%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p>Credits are calculated based on the monthly fee paid for the affected service and are applied as account credit toward future invoices. Credits must be requested within 7 days of the incident.</p>
            </div>
        </div>

        {{-- Section 4 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">4. Support Response Times</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>We aim to respond to support requests within the following timeframes:</p>
                <div class="mt-4 glass-light rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="text-left px-4 py-3 text-gray-300 font-semibold">Priority</th>
                                <th class="text-left px-4 py-3 text-gray-300 font-semibold">Response Time</th>
                                <th class="text-left px-4 py-3 text-gray-300 font-semibold">Resolution Target</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-400">
                            <tr class="border-b border-white/5">
                                <td class="px-4 py-2.5"><span class="text-red-400 font-medium">Critical</span> - Service down</td>
                                <td class="px-4 py-2.5">1 hour</td>
                                <td class="px-4 py-2.5">4 hours</td>
                            </tr>
                            <tr class="border-b border-white/5">
                                <td class="px-4 py-2.5"><span class="text-amber-400 font-medium">High</span> - Major issue</td>
                                <td class="px-4 py-2.5">4 hours</td>
                                <td class="px-4 py-2.5">12 hours</td>
                            </tr>
                            <tr class="border-b border-white/5">
                                <td class="px-4 py-2.5"><span class="text-brand-400 font-medium">Medium</span> - General inquiry</td>
                                <td class="px-4 py-2.5">8 hours</td>
                                <td class="px-4 py-2.5">24 hours</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2.5"><span class="text-gray-300 font-medium">Low</span> - Question</td>
                                <td class="px-4 py-2.5">24 hours</td>
                                <td class="px-4 py-2.5">48 hours</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p>Response times are measured during business hours (9 AM - 6 PM UTC, Monday - Friday) for standard support. Critical issues are monitored 24/7.</p>
            </div>
        </div>

        {{-- Section 5 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">5. Scheduled Maintenance</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>We perform regular maintenance to ensure optimal performance and security. Maintenance windows are:</p>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li>Announced at least 48 hours in advance via email and our status page</li>
                    <li>Scheduled during off-peak hours when possible</li>
                    <li>Limited to a maximum of 4 hours per maintenance window</li>
                </ul>
                <p>Emergency maintenance for critical security patches may be performed with shorter notice when necessary to protect our infrastructure and customers.</p>
            </div>
        </div>

        {{-- Section 6 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">6. Exclusions</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>This SLA does not cover downtime or issues resulting from:</p>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li>Customer-side configuration errors or software issues</li>
                    <li>Third-party software or services not provided by us</li>
                    <li>Force majeure events beyond our reasonable control</li>
                    <li>Customer actions that violate our Terms of Service</li>
                    <li>Beta or experimental features explicitly marked as such</li>
                </ul>
            </div>
        </div>

        {{-- Section 7 --}}
        <div class="glass rounded-2xl p-8">
            <h2 class="text-lg font-bold mb-4">7. Contact</h2>
            <div class="text-sm text-gray-400 leading-relaxed space-y-3">
                <p>To request a service credit or for questions about this SLA, please contact us through our support system.</p>
            </div>
        </div>
    </div>
</div>
@endsection
