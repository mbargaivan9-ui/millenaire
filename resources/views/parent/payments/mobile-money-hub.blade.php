@extends('layouts.app')

@section('title', __('Mobile Money Payment'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/payment-responsive.css') }}">
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header Section -->
        <div class="mb-12">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black bg-gradient-to-r from-cyan-400 via-blue-400 to-purple-400 bg-clip-text text-transparent mb-3 break-words">
                        {{ __('Mobile Money Payment') }}
                    </h1>
                    <p class="text-gray-300 text-sm sm:text-base lg:text-lg">{{ __('Pay school fees instantly via mobile money') }}</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3 px-4 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-green-500/20 to-emerald-500/20 rounded-full border border-green-500/30 backdrop-blur flex-shrink-0">
                    <div class="w-2 h-2 sm:w-3 sm:h-3 rounded-full bg-green-400 animate-pulse flex-shrink-0"></div>
                    <span class="text-green-300 text-xs sm:text-sm font-semibold whitespace-nowrap">{{ __('All Systems Active') }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Info Boxes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-12">
            <div class="group bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 border border-slate-700/50 hover:border-blue-500/50 transition-all">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-blue-500/20 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-white font-bold mb-1">{{ __('Fast & Easy') }}</h3>
                        <p class="text-gray-400 text-sm">{{ __('Pay in just 3 steps with instant confirmation') }}</p>
                    </div>
                </div>
            </div>
            <div class="group bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 border border-slate-700/50 hover:border-green-500/50 transition-all">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-green-500/20 rounded-xl group-hover:scale-110 transition-transform flex-shrink-0">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-white font-bold mb-1">{{ __('Secure') }}</h3>
                        <p class="text-gray-400 text-sm">{{ __('All transactions are encrypted and verified') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Choose Child Section -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                <div class="w-1 h-8 bg-gradient-to-b from-cyan-400 to-blue-500 rounded-full"></div>
                {{ __('Select Student to Pay For') }}
            </h2>

            @if($children->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($children as $child)
                        <div class="group relative">
                            <!-- Card Background Blur -->
                            <div class="absolute inset-0 bg-gradient-to-br from-purple-600/20 to-pink-600/20 rounded-2xl blur-xl opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
                            
                            <!-- Main Card -->
                            <a href="{{ route('mobile-money.show', $child) }}" class="relative block bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 border border-slate-700/50 group-hover:border-purple-500/50 transition-all duration-300 backdrop-blur h-full">
                                <!-- Animated Background -->
                                <div class="absolute -right-20 -top-20 w-40 h-40 bg-gradient-to-br from-purple-600/10 to-pink-600/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-500"></div>
                                
                                <div class="relative z-10">
                                    <!-- Avatar and Name -->
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-xl font-bold text-white shadow-lg flex-shrink-0">
                                            {{ substr($child->user->name ?? $child->first_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-white">{{ $child->user->name ?? ($child->first_name . ' ' . ($child->last_name ?? '')) }}</h3>
                                            <p class="text-sm text-gray-400">{{ $child->classe->name ?? __('No Class') }}</p>
                                        </div>
                                    </div>

                                    <!-- Matricule -->
                                    <div class="mb-4 p-3 bg-white/5 rounded-lg border border-white/10">
                                        <p class="text-gray-500 text-xs font-medium">{{ __('Matricule') }}</p>
                                        <p class="text-white font-mono text-sm">{{ $child->matricule }}</p>
                                    </div>

                                    <!-- Amount Due -->
                                    <div class="mb-4 p-3 bg-red-500/10 rounded-lg border border-red-500/20">
                                        <p class="text-gray-400 text-xs font-medium">{{ __('Total Due') }}</p>
                                        <p class="text-red-400 font-black text-lg">{{ number_format($child->getTotalAmountDue() ?? 0, 0) }} XAF</p>
                                    </div>

                                    <!-- Action Button -->
                                    <div class="flex items-center gap-2 text-sm font-bold text-blue-400 group-hover:text-blue-300 transition-colors">
                                        <span>{{ __('Pay Now') }}</span>
                                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- No Children Message -->
                <div class="text-center py-12">
                    <div class="inline-block p-4 bg-amber-500/20 rounded-full mb-4">
                        <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0v2m0-6v-2m0 0V7m0 6H7m5 0h5"></path>
                        </svg>
                    </div>
                    <h3 class="text-white text-xl font-bold mb-2">{{ __('No Students Found') }}</h3>
                    <p class="text-gray-400 mb-6">{{ __('You have no children associated with your account yet.') }}</p>
                    <a href="{{ route('profile.edit') }}" class="inline-block px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-xl hover:shadow-lg hover:shadow-blue-500/50 transition-all">
                        {{ __('Update Profile') }}
                    </a>
                </div>
            @endif
        </div>

        <!-- Supported Operators -->
        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 rounded-2xl p-8 border border-slate-700/50 backdrop-blur">
            <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
                <svg class="w-6 h-6 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18.364 5.636l-3.536 3.536m9.172-9.172l-15.556 15.556M9 3.5a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11z"></path>
                </svg>
                {{ __('Supported Mobile Money Operators') }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Orange Money -->
                <div class="flex items-center gap-4 p-4 bg-white/5 rounded-xl border border-white/10 hover:border-orange-500/30 transition-all">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" opacity="0.3"/>
                            <circle cx="8" cy="8" r="1.5"/>
                            <circle cx="16" cy="8" r="1.5"/>
                            <path d="M12 14c-2 0-3-1-3-2s1-2 3-2 3 1 3 2-1 2-3 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-white font-bold">Orange Money</h4>
                        <p class="text-gray-400 text-sm">Dial <span class="text-orange-300 font-mono">*611#</span> or <span class="text-orange-300 font-mono">#150*</span></p>
                    </div>
                </div>

                <!-- MTN Mobile Money -->
                <div class="flex items-center gap-4 p-4 bg-white/5 rounded-xl border border-white/10 hover:border-yellow-500/30 transition-all">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-yellow-500 to-amber-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" opacity="0.3"/>
                            <circle cx="9" cy="9" r="1.5"/>
                            <circle cx="15" cy="9" r="1.5"/>
                            <path d="M12 15c1.5 0 3 .5 3 1.5s-1.5 1.5-3 1.5-3-.5-3-1.5 1.5-1.5 3-1.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-white font-bold">MTN Mobile Money</h4>
                        <p class="text-gray-400 text-sm">Dial <span class="text-yellow-300 font-mono">*156#</span> or <span class="text-yellow-300 font-mono">#150#</span></p>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mt-6 p-4 bg-blue-500/10 border-l-4 border-blue-500 rounded">
                <p class="text-blue-300 text-sm">
                    <strong>{{ __('💡 Tip:') }}</strong> 
                    {{ __('Make sure your mobile money account is active and has sufficient balance before initiating payment.') }}
                </p>
            </div>
        </div>

        <!-- Footer Navigation -->
        <div class="mt-12 flex justify-between items-center">
            <a href="{{ route('parent.payments.index') }}" class="text-blue-400 hover:text-blue-300 font-semibold flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('Back to Payments') }}
            </a>
            <a href="{{ route('profile.edit') }}" class="text-gray-400 hover:text-gray-300 text-sm transition-colors">
                {{ __('View Profile') }}
            </a>
        </div>
    </div>
</div>

@endsection
