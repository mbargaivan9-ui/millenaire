@extends('layouts.app')

@section('title', __('Payment Details') . ' - ' . ($student->user->name ?? $student->first_name . ' ' . $student->last_name))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('parent.payments.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-700 font-semibold">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('Back to Payments') }}
            </a>
        </div>

<div>
    <!-- Child Payment Header -->
    <div class="mb-8 p-6 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg border border-indigo-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">
                    {{ $student->user->name ?? $student->first_name . ' ' . $student->last_name }}
                </h3>
                <p class="text-gray-600 mb-4">
                    <span class="font-semibold">{{ __('Class:') }}</span> 
                    {{ $student->classe->name ?? 'N/A' }}
                </p>
                <p class="text-gray-600">
                    <span class="font-semibold">{{ __('Matricule:') }}</span> 
                    {{ $student->matricule }}
                </p>
            </div>
            <div class="flex items-end justify-end">
                <div class="text-right">
                    <p class="text-gray-500 text-sm mb-1">{{ __('Financial Status') }}</p>
                    <span class="inline-block px-4 py-2 rounded-full font-semibold
                        @switch($financialStatus)
                            @case('paid')
                                bg-green-100 text-green-800
                                @break
                            @case('partial')
                                bg-yellow-100 text-yellow-800
                                @break
                            @default
                                bg-red-100 text-red-800
                        @endswitch
                    ">
                        {{ ucfirst($financialStatus) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm mb-2">{{ __('Total Due') }}</p>
            <p class="text-3xl font-bold text-red-600">{{ number_format($totalDue, 0) }} XAF</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm mb-2">{{ __('Total Paid') }}</p>
            <p class="text-3xl font-bold text-green-600">{{ number_format($totalPaid, 0) }} XAF</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm mb-2">{{ __('Balance') }}</p>
            <p class="text-3xl font-bold {{ $balance > 0 ? 'text-orange-600' : 'text-green-600' }}">
                {{ number_format($balance, 0) }} XAF
            </p>
        </div>
    </div>

    <!-- Payment Actions -->
    <div class="mb-8 flex flex-col sm:flex-row gap-4">
        <button onclick="showPaymentModal({{ $student->id }})" 
                class="flex-1 px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition shadow-lg">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            {{ __('Make Payment') }}
        </button>
        
        <!-- PHASE 10: Orange Money & MTN MoMo Payment Button -->
        @if($balance > 0)
        <a href="{{ route('parent.mobile-money.show', ['student' => $student->id]) }}" 
           class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-500 to-red-600 text-white rounded-lg font-semibold hover:shadow-lg transition shadow-lg text-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm0-13c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5z"/>
            </svg>
            {{ __('Pay with Mobile Money') }}
        </a>
        @endif
    </div>

    <!-- Payment History Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900">{{ __('Payment History') }}</h4>
        </div>
        <div class="overflow-x-auto" id="payments-table-container">
            <table class="w-full" id="payments-table">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Date') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Amount') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Purpose') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Provider') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50 transition" id="payment-{{ $payment->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ $payment->initiated_at?->format('d/m/Y H:i') ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                            {{ number_format($payment->amount ?? $payment->amount_paid ?? 0, 0) }} {{ $payment->currency ?? 'XAF' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ ucfirst(str_replace('_', ' ', $payment->purpose ?? $payment->payment_method ?? 'School Fees')) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800">
                                {{ ucfirst($payment->provider ?? 'Mobile Money') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @php
                                $statusClasses = [
                                    'completed' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'partial' => 'bg-blue-100 text-blue-800',
                                ];
                            @endphp
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $statusClasses[$payment->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $payment->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            @if($payment->receipt)
                                <a href="{{ route('parent.payments.download', ['receipt' => $payment->receipt->id]) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 font-semibold"
                                   title="{{ __('Download Receipt') }}">
                                    <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </a>
                            @endif
                            @if($payment->status === 'pending')
                                <button onclick="checkPaymentStatus({{ $payment->id }})" 
                                        class="text-blue-600 hover:text-blue-900 font-semibold"
                                        title="{{ __('Check Status') }}">
                                    <svg class="w-5 h-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="font-medium">{{ __('No payments found') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
@endsection
