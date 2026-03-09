@extends('layouts.app')

@section('title', __('Payments'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ __('Payments Management') }}</h1>
            <p class="text-gray-600">{{ __('Manage school fees and payments for your children') }}</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8" id="stats-container">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">{{ __('Total Due') }}</p>
                        <p class="text-2xl font-bold text-gray-900" id="stat-total-due">0</p>
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
                        <p class="text-2xl font-bold text-green-600" id="stat-total-paid">0</p>
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
                        <p class="text-2xl font-bold text-yellow-600" id="stat-pending">0</p>
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
                        <p class="text-2xl font-bold text-blue-600" id="stat-completed">0</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Children Tabs -->
        <div class="bg-white rounded-lg shadow">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6" role="tablist">
                    @foreach($children as $child)
                    <button 
                        onclick="showChildPayments({{ $child->id }})"
                        class="child-tab py-4 px-1 border-b-2 border-transparent font-medium text-sm whitespace-nowrap hover:text-indigo-600 hover:border-gray-300 transition"
                        role="tab"
                        data-child-id="{{ $child->id }}"
                    >
                        <div class="flex items-center space-x-2">
                            <span class="font-semibold">{{ $child->user->name ?? $child->first_name }}</span>
                            <span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700">
                                @if($paymentsSummary[$child->id]['pending_payments'] > 0)
                                    {{ $paymentsSummary[$child->id]['pending_payments'] }} {{ __('pending') }}
                                @else
                                    {{ __('Paid') }}
                                @endif
                            </span>
                        </div>
                    </button>
                    @endforeach
                </nav>
            </div>

            <div class="p-6" id="child-content">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-indigo-50 border-l-4 border-indigo-500 p-6 rounded">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Quick Actions') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button onclick="window.location.href='{{ route('parent.payments.statistics') }}'" class="bg-white hover:bg-gray-50 border border-gray-300 rounded-lg p-4 text-center transition">
                    <svg class="w-6 h-6 mx-auto mb-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <p class="font-medium text-gray-900">{{ __('View Statistics') }}</p>
                </button>
                <button onclick="document.getElementById('filter-modal').classList.remove('hidden')" class="bg-white hover:bg-gray-50 border border-gray-300 rounded-lg p-4 text-center transition">
                    <svg class="w-6 h-6 mx-auto mb-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <p class="font-medium text-gray-900">{{ __('Filter Payments') }}</p>
                </button>
                <button onclick="downloadPaymentReport()" class="bg-white hover:bg-gray-50 border border-gray-300 rounded-lg p-4 text-center transition">
                    <svg class="w-6 h-6 mx-auto mb-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="font-medium text-gray-900">{{ __('Download Report') }}</p>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Load initial statistics
    loadPaymentStatistics();

    // Show first child by default
    @if($children->count() > 0)
    showChildPayments({{ $children->first()->id }});
    @endif

    function loadPaymentStatistics() {
        fetch('{{ route("parent.payments.statistics") }}')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('stat-total-due').textContent = formatCurrency(data.data.total_due);
                    document.getElementById('stat-total-paid').textContent = formatCurrency(data.data.total_paid);
                    document.getElementById('stat-pending').textContent = data.data.pending_payments;
                    document.getElementById('stat-completed').textContent = data.data.completed_payments;
                }
            })
            .catch(err => console.error('Failed to load statistics:', err));
    }

    function showChildPayments(childId) {
        // Update active tab
        document.querySelectorAll('.child-tab').forEach(tab => {
            tab.classList.remove('border-indigo-500', 'text-indigo-600');
            tab.classList.add('border-transparent');
        });
        
        const activeTab = document.querySelector(`[data-child-id="${childId}"]`);
        activeTab.classList.add('border-indigo-500', 'text-indigo-600');

        // Load child payment content
        fetch(`/parent/children/${childId}/payments`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('child-content').innerHTML = html;
            })
            .catch(err => {
                document.getElementById('child-content').innerHTML = `<p class="text-red-600">{{ __('Error loading payment data') }}</p>`;
            });
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'XAF',
            minimumFractionDigits: 0,
        }).format(amount || 0);
    }

    function downloadPaymentReport() {
        alert('{{ __("Feature coming soon") }}');
    }
</script>

@endsection
