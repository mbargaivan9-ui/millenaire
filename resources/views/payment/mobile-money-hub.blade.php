{{--
    |--------------------------------------------------------------------------
    | payment/mobile-money-hub.blade.php — Mobile Money Payment Hub
    |--------------------------------------------------------------------------
    | Hub de sélection des enfants pour paiement Mobile Money
    | Permet au parent de choisir quel enfant payer
    --}}

@extends('layouts.app')

@section('title', __('Mobile Money Payment'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-lg p-4">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm0-13c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-4xl font-bold text-gray-900">{{ __('Mobile Money Payment') }}</h1>
                    <p class="text-gray-600 mt-2">{{ __('Select a child to pay school fees') }}</p>
                </div>
            </div>
        </div>

        <!-- Info Banner -->
        <div class="mb-8 bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
            <div class="flex gap-3">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div class="text-blue-800">
                    <p class="font-semibold">{{ __('Payment Methods Available') }}</p>
                    <p class="text-sm mt-1">🟠 Orange Money • 🟡 MTN MoMo</p>
                    <p class="text-sm mt-1">{{ __('Fast, secure, and hassle-free mobile payments') }}</p>
                </div>
            </div>
        </div>

        <!-- Children Selection Grid -->
        @if ($children->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($children as $child)
                    @php
                        $totalDue = $child->getTotalAmountDue();
                        $totalPaid = $child->getTotalAmountPaid();
                        $balance = $totalDue - $totalPaid;
                        $percentagePaid = $totalDue > 0 ? (($totalPaid / $totalDue) * 100) : 0;
                    @endphp

                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition transform hover:scale-105 overflow-hidden">
                        <!-- Card Header -->
                        <div class="bg-gradient-to-r from-indigo-500 to-blue-600 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">
                                {{ $child->user->name ?? ($child->first_name . ' ' . $child->last_name) }}
                            </h3>
                            <p class="text-indigo-100 text-sm">
                                {{ $child->classe?->name ?? 'N/A' }}
                            </p>
                        </div>

                        <!-- Card Body -->
                        <div class="px-6 py-4">
                            <!-- Financial Status -->
                            <div class="mb-4">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide mb-2">
                                    {{ __('Payment Status') }}
                                </p>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">
                                        XAF {{ number_format($totalPaid, 0) }} / {{ number_format($totalDue, 0) }}
                                    </span>
                                    <span class="text-sm font-bold text-indigo-600">
                                        {{ number_format($percentagePaid, 0) }}%
                                    </span>
                                </div>
                                <!-- Progress Bar -->
                                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-green-400 to-green-600 transition-all duration-300"
                                         style="width: {{ $percentagePaid }}%"></div>
                                </div>
                            </div>

                            <!-- Amount Due -->
                            <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
                                <div class="bg-green-50 rounded p-3">
                                    <p class="text-gray-500 text-xs">{{ __('Paid') }}</p>
                                    <p class="text-green-600 font-bold">
                                        {{ number_format($totalPaid, 0) }} XAF
                                    </p>
                                </div>
                                <div class="bg-red-50 rounded p-3">
                                    <p class="text-gray-500 text-xs">{{ __('Due') }}</p>
                                    <p class="text-red-600 font-bold">
                                        {{ number_format($balance, 0) }} XAF
                                    </p>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            @if ($balance <= 0)
                                <div class="mb-4 inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Fully Paid') }}
                                </div>
                            @else
                                <div class="mb-4 inline-flex items-center rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Pending') }}
                                </div>
                            @endif
                        </div>

                        <!-- Card Footer / Actions -->
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                            <div class="flex gap-2">
                                <!-- View Details Button -->
                                <a href="{{ route('parent.child.payments', ['student' => $child->id]) }}"
                                   class="flex-1 inline-flex items-center justify-center text-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View
                                </a>

                                <!-- Pay Button -->
                                @if ($balance > 0)
                                    <a href="{{ route('parent.mobile-money.show', ['student' => $child->id]) }}"
                                       class="flex-1 inline-flex items-center justify-center text-center px-4 py-2 text-sm font-bold text-white bg-gradient-to-r from-orange-500 to-red-600 rounded-lg hover:shadow-lg transition">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.6915026,12.4744748 L3.50612381,13.2599618 C3.19218622,13.2599618 3.03521743,13.4170592 3.03521743,13.5741566 L1.15159189,20.0151496 C0.8376543,20.8006365 0.99,21.89 1.77946707,22.52 C2.41,22.99 3.50612381,23.1 4.13399899,22.9429026 L21.714504,14.0454487 C22.6563168,13.5741566 23.1272231,12.6315722 22.9702544,11.6889879 L20.35946707,1.77946707 C20.2024983,0.994580233 19.2606856,0.5 18.3188727,0.5 C17.6910536,0.5 17.0632344,0.837160287 16.7492968,1.30846667 L0.8376543,11.6889879 C0.99,12.6315722 1.77946707,13.5741566 2.87559289,13.2599618 L12.5496181,12.4744748 C12.6915026,12.4744748 12.8484713,12.4744748 12.8484713,12.3173773 L12.8484713,3.03521743 C12.8484713,2.40734225 13.3193777,1.77946707 14.0048662,1.77946707 C14.6327854,1.77946707 15.103691,2.40734225 15.103691,3.03521743 L15.103691,12.3173773 C15.2606596,12.4744748 14.9467219,12.4744748 14.6327854,12.4744748 Z"/>
                                        </svg>
                                        Pay Now
                                    </a>
                                @else
                                    <button disabled
                                            class="flex-1 px-4 py-2 text-sm font-bold text-gray-400 bg-gray-200 rounded-lg cursor-not-allowed">
                                        Paid ✓
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
                <p class="text-gray-600 font-medium text-lg mb-2">{{ __('No children found') }}</p>
                <p class="text-gray-500 text-sm mb-6">{{ __('Please add children to your account to make payments') }}</p>
                <a href="{{ route('parent.dashboard') }}" class="inline-flex items-center px-6 py-3 text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition font-semibold">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        @endif

        <!-- Back Button -->
        <div class="mt-8 text-center">
            <a href="{{ route('parent.payments.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-700 font-semibold">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('Back to Payments Dashboard') }}
            </a>
        </div>
    </div>
</div>
@endsection
