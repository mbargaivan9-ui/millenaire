@extends('layouts.app')

@section('title', __('Payments'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header with Icon -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <div class="bg-gradient-to-br from-indigo-600 to-blue-600 rounded-lg p-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2.25"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-1.604-8.02a1 1 0 00-.753-.659A4 4 0 0015 2h0a4 4 0 00-3.956 3.751l-1.286 8.05a1 1 0 00.54 1.051 18.05 18.05 0 003.4.795 18.08 18.08 0 003.331-.335c.952-.147 1.823-.663 2.332-1.39z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-4xl font-bold text-gray-900">{{ __('Payments Management') }}</h1>
                    <p class="text-gray-600 mt-2">{{ __('Manage school fees and payments for your children') }}</p>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">{{ __('Total Due') }}</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ number_format($children->sum(fn($c) => $paymentsSummary[$c->id]['total_due']), 0) }} XAF
                        </p>
                    </div>
                    <div class="bg-red-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">{{ __('Total Paid') }}</p>
                        <p class="text-2xl font-bold text-green-600">
                            {{ number_format($children->sum(fn($c) => $paymentsSummary[$c->id]['total_paid']), 0) }} XAF
                        </p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">{{ __('Pending') }}</p>
                        <p class="text-2xl font-bold text-yellow-600">
                            {{ $children->sum(fn($c) => $paymentsSummary[$c->id]['pending_payments']) }}
                        </p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">{{ __('Completed') }}</p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ $children->sum(fn($c) => $paymentsSummary[$c->id]['pending_payments'] == 0 ? 1 : 0) }}/{{ $children->count() }}
                        </p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Children Payment Details -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('Children Payments') }}</h3>
            </div>

            @if ($children->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Child') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Class') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Total Due') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Total Paid') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Balance') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($children as $child)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $child->user->name ?? ($child->first_name . ' ' . $child->last_name) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $child->classe?->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600">
                                {{ number_format($paymentsSummary[$child->id]['total_due'], 0) }} XAF
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                {{ number_format($paymentsSummary[$child->id]['total_paid'], 0) }} XAF
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-orange-600">
                                {{ number_format($paymentsSummary[$child->id]['total_due'] - $paymentsSummary[$child->id]['total_paid'], 0) }} XAF
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium
                                    @if ($paymentsSummary[$child->id]['pending_payments'] == 0)
                                        bg-green-100 text-green-800
                                    @else
                                        bg-yellow-100 text-yellow-800
                                    @endif
                                ">
                                    @if ($paymentsSummary[$child->id]['pending_payments'] > 0)
                                        {{ $paymentsSummary[$child->id]['pending_payments'] }} {{ __('pending') }}
                                    @else
                                        {{ __('Paid') }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <a href="{{ route('parent.child.payments', ['student' => $child->id]) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold inline-block">
                                    {{ __('View') }}
                                </a>
                                @if ($paymentsSummary[$child->id]['total_due'] - $paymentsSummary[$child->id]['total_paid'] > 0)
                                    <a href="{{ route('parent.mobile-money.show', ['student' => $child->id]) }}" class="text-orange-600 hover:text-orange-900 font-semibold inline-block">
                                        {{ __('Pay') }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="px-6 py-8 text-center">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="font-medium text-gray-600">{{ __('No children found') }}</p>
            </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-indigo-50 border-l-4 border-indigo-500 p-6 rounded">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Quick Actions') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('parent.payments.statistics') }}" class="bg-white hover:bg-gray-50 border border-gray-300 rounded-lg p-4 text-center transition">
                    <svg class="w-6 h-6 mx-auto mb-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <p class="font-medium text-gray-900">{{ __('View Statistics') }}</p>
                </a>
                <a href="{{ route('parent.dashboard') }}" class="bg-white hover:bg-gray-50 border border-gray-300 rounded-lg p-4 text-center transition">
                    <svg class="w-6 h-6 mx-auto mb-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 12a1 1 0 11-2 0 1 1 0 012 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="font-medium text-gray-900">{{ __('Back to Dashboard') }}</p>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
