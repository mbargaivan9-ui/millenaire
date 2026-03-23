@extends('layouts.app')

@section('title', __('Payments'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/payment-responsive.css') }}">
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="mb-12">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black bg-gradient-to-r from-cyan-400 via-blue-400 to-purple-400 bg-clip-text text-transparent mb-3 break-words">
                        {{ __('Payment Hub') }}
                    </h1>
                    <p class="text-gray-300 text-sm sm:text-base lg:text-lg">{{ __('Manage and track school fees with ease') }}</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3 px-4 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-green-500/20 to-emerald-500/20 rounded-full border border-green-500/30 backdrop-blur flex-shrink-0">
                    <div class="w-2 h-2 sm:w-3 sm:h-3 rounded-full bg-green-400 animate-pulse flex-shrink-0"></div>
                    <span class="text-green-300 text-xs sm:text-sm font-semibold whitespace-nowrap">{{ __('All Systems Active') }}</span>
                </div>
            </div>
        </div>

        <!-- Premium Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12" id="stats-container">
            <!-- Total Due Card -->
            <div class="group relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-red-600 to-pink-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <div class="relative bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-8 border border-slate-700/50 group-hover:border-red-500/50 transition-all duration-300 backdrop-blur">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2 sm:p-3 rounded-xl bg-red-500/20 group-hover:bg-red-500/30 transition-colors">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-red-400 bg-red-500/20 px-3 py-1 rounded-full">CRITICAL</span>
                    </div>
                    <h3 class="text-gray-400 text-xs sm:text-sm font-medium mb-2">{{ __('Total Due') }}</h3>
                    <p class="text-2xl sm:text-3xl lg:text-4xl font-black bg-gradient-to-r from-red-400 to-pink-400 bg-clip-text text-transparent" id="stat-total-due">0 XAF</p>
                    <p class="text-xs text-gray-500 mt-3">{{ __('Update in 24h') }}</p>
                </div>
            </div>

            <!-- Total Paid Card -->
            <div class="group relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-green-600 to-emerald-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <div class="relative bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-8 border border-slate-700/50 group-hover:border-green-500/50 transition-all duration-300 backdrop-blur">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 rounded-xl bg-green-500/20 group-hover:bg-green-500/30 transition-colors">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-green-400 bg-green-500/20 px-3 py-1 rounded-full">SUCCESS</span>
                    </div>
                    <h3 class="text-gray-400 text-sm font-medium mb-2">{{ __('Total Paid') }}</h3>
                    <p class="text-4xl font-black bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent" id="stat-total-paid">0 XAF</p>
                    <p class="text-xs text-gray-500 mt-3">{{ __('Confirmed') }}</p>
                </div>
            </div>

            <!-- Pending Payments Card -->
            <div class="group relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-amber-600 to-orange-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <div class="relative bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-8 border border-slate-700/50 group-hover:border-amber-500/50 transition-all duration-300 backdrop-blur">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2 sm:p-3 rounded-xl bg-amber-500/20 group-hover:bg-amber-500/30 transition-colors">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-amber-400 bg-amber-500/20 px-3 py-1 rounded-full">PENDING</span>
                    </div>
                    <h3 class="text-gray-400 text-xs sm:text-sm font-medium mb-2">{{ __('Pending') }}</h3>
                    <p class="text-2xl sm:text-3xl lg:text-4xl font-black bg-gradient-to-r from-amber-400 to-orange-400 bg-clip-text text-transparent" id="stat-pending">0 XAF</p>
                    <p class="text-xs text-gray-500 mt-3">{{ __('Awaiting') }}</p>
                </div>
            </div>

            <!-- Completed Transactions Card -->
            <div class="group relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-cyan-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <div class="relative bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-8 border border-slate-700/50 group-hover:border-blue-500/50 transition-all duration-300 backdrop-blur">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2 sm:p-3 rounded-xl bg-blue-500/20 group-hover:bg-blue-500/30 transition-colors">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-blue-400 bg-blue-500/20 px-3 py-1 rounded-full">COMPLETED</span>
                    </div>
                    <h3 class="text-gray-400 text-xs sm:text-sm font-medium mb-2">{{ __('Completed') }}</h3>
                    <p class="text-2xl sm:text-3xl lg:text-4xl font-black bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent" id="stat-completed">0</p>
                    <p class="text-xs text-gray-500 mt-3">{{ __('Transactions') }}</p>
                </div>
            </div>
        </div>

        <!-- Children Payment Cards Section -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                <div class="w-1 h-8 bg-gradient-to-b from-cyan-400 to-blue-500 rounded-full"></div>
                {{ __('Your Children') }}
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                @foreach($children as $child)
                <div class="group relative cursor-pointer" onclick="showChildPayments({{ $child->id }})">
                    <!-- Card Background Blur -->
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-600/20 to-pink-600/20 rounded-3xl blur-xl opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
                    
                    <!-- Main Card -->
                    <div class="relative bg-gradient-to-br from-slate-800/95 via-slate-800/90 to-slate-900/95 rounded-3xl p-8 border border-slate-700/50 group-hover:border-purple-500/50 transition-all duration-300 backdrop-blur-xl overflow-hidden">
                        
                        <!-- Animated Background -->
                        <div class="absolute -right-20 -top-20 w-40 h-40 bg-gradient-to-br from-purple-600/10 to-pink-600/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-500"></div>
                        
                        <!-- Top Section with Avatar -->
                        <div class="relative z-10 flex items-start justify-between mb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-2xl font-bold text-white shadow-lg">
                                    {{ substr($child->user->name ?? $child->first_name, 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">{{ $child->user->name ?? $child->first_name . ' ' . ($child->last_name ?? '') }}</h3>
                                    <p class="text-sm text-gray-400">{{ $child->classe->name ?? __('No Class') }}</p>
                                </div>
                            </div>
                            
                            @php
                                $pending = $paymentsSummary[$child->id]['pending_payments'] ?? 0;
                                $statusColor = $pending > 0 ? 'from-red-500 to-pink-500' : 'from-green-500 to-emerald-500';
                                $statusBg = $pending > 0 ? 'bg-red-500/20' : 'bg-green-500/20';
                                $statusText = $pending > 0 ? 'text-red-300' : 'text-green-300';
                            @endphp
                            
                            <div class="px-4 py-2 rounded-full {{ $statusBg }} {{ $statusText }} text-xs font-bold uppercase tracking-wider">
                                {{ $pending > 0 ? $pending . ' Pending' : 'Complete' }}
                            </div>
                        </div>

                        <!-- Payment Stats Grid -->
                        <div class="relative z-10 grid grid-cols-3 gap-4 mb-6 p-4 bg-white/5 rounded-2xl border border-white/10 backdrop-blur">
                            <div class="text-center">
                                <p class="text-gray-500 text-xs font-medium mb-1">{{ __('Due') }}</p>
                                <p class="text-lg font-black text-transparent bg-gradient-to-r from-red-400 to-pink-400 bg-clip-text">
                                    {{ number_format($paymentsSummary[$child->id]['total_due'] ?? 0, 0) }}
                                </p>
                            </div>
                            <div class="text-center border-l border-r border-white/10">
                                <p class="text-gray-500 text-xs font-medium mb-1">{{ __('Paid') }}</p>
                                <p class="text-lg font-black text-transparent bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text">
                                    {{ number_format($paymentsSummary[$child->id]['total_paid'] ?? 0, 0) }}
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-gray-500 text-xs font-medium mb-1">{{ __('Balance') }}</p>
                                <p class="text-lg font-black text-transparent bg-gradient-to-r from-amber-400 to-orange-400 bg-clip-text">
                                    {{ number_format(($paymentsSummary[$child->id]['total_due'] ?? 0) - ($paymentsSummary[$child->id]['total_paid'] ?? 0), 0) }}
                                </p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="relative z-10 mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-gray-400 font-medium">{{ __('Payment Progress') }}</span>
                                <span class="text-xs font-bold bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent">
                                    {{ $paymentsSummary[$child->id]['total_due'] > 0 ? round(($paymentsSummary[$child->id]['total_paid'] ?? 0) / ($paymentsSummary[$child->id]['total_due'] ?? 1) * 100) : 100 }}%
                                </span>
                            </div>
                            <div class="h-2 bg-white/10 rounded-full overflow-hidden border border-white/5">
                                <div class="h-full bg-gradient-to-r from-cyan-500 via-blue-500 to-purple-500 transition-all duration-500"
                                     style="width: {{ $paymentsSummary[$child->id]['total_due'] > 0 ? round(($paymentsSummary[$child->id]['total_paid'] ?? 0) / ($paymentsSummary[$child->id]['total_due'] ?? 1) * 100) : 100 }}%">
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="relative z-10 flex gap-3">
                            <button class="flex-1 px-4 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold hover:shadow-lg hover:shadow-purple-500/50 transition-all duration-300 group/btn">
                                <span class="group-hover/btn:scale-110 transition-transform duration-300 inline-block">{{ __('View Details') }}</span>
                            </button>
                            @if($paymentsSummary[$child->id]['total_due'] > ($paymentsSummary[$child->id]['total_paid'] ?? 0))
                            <a href="{{ route('mobile-money.show', $child) }}" class="px-4 py-3 rounded-xl bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold hover:shadow-lg hover:shadow-green-500/50 transition-all duration-300 group/btn text-center">
                                <span class="group-hover/btn:scale-110 transition-transform duration-300 inline-block">💳</span>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-12">
            <div class="group relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-cyan-600/20 to-blue-600/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <button onclick="window.location.href='{{ route('parent.payments.statistics') }}'" 
                        class="relative w-full bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 border border-slate-700/50 group-hover:border-cyan-500/50 transition-all duration-300 text-left">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm font-medium mb-1">{{ __('Payment Stats') }}</p>
                            <p class="text-white font-semibold">{{ __('View Analytics') }}</p>
                        </div>
                        <div class="p-3 bg-cyan-500/20 rounded-xl group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                </button>
            </div>

            <div class="group relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-purple-600/20 to-pink-600/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <button onclick="document.getElementById('filter-modal').classList.remove('hidden')" 
                        class="relative w-full bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 border border-slate-700/50 group-hover:border-purple-500/50 transition-all duration-300 text-left">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm font-medium mb-1">{{ __('Filter') }}</p>
                            <p class="text-white font-semibold">{{ __('Advanced Filter') }}</p>
                        </div>
                        <div class="p-3 bg-purple-500/20 rounded-xl group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                        </div>
                    </div>
                </button>
            </div>

            <div class="group relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-green-600/20 to-emerald-600/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl"></div>
                <button onclick="downloadPaymentReport()" 
                        class="relative w-full bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 border border-slate-700/50 group-hover:border-green-500/50 transition-all duration-300 text-left">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm font-medium mb-1">{{ __('Report') }}</p>
                            <p class="text-white font-semibold">{{ __('Download PDF') }}</p>
                        </div>
                        <div class="p-3 bg-green-500/20 rounded-xl group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                </button>
            </div>
        </div>

        <!-- Child Content Area (Populated Dynamically) -->
        <div id="child-content" class="mb-12">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    .animate-float {
        animation: float 3s ease-in-out infinite;
    }
</style>

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
        // Load child payment content
        fetch(`/parent/children/${childId}/payments`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('child-content').innerHTML = html;
            })
            .catch(err => {
                document.getElementById('child-content').innerHTML = `<div class="text-center py-12"><p class="text-red-400">{{ __('Error loading payment data') }}</p></div>`;
            });
    }

    function formatCurrency(value) {
        return new Intl.NumberFormat('fr-CM', {
            style: 'currency',
            currency: 'XAF',
            minimumFractionDigits: 0
        }).format(value || 0);
    }

    function downloadPaymentReport() {
        // Implementation for report download
        alert('{{ __("Report generation in progress...") }}');
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
