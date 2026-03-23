@extends('layouts.app')

@section('title', __('Premium Mobile Money Payment'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-950 via-blue-950 to-slate-950 py-8 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-500/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="max-w-4xl mx-auto relative z-10">
        <!-- Top Security Badge -->
        <div class="mb-8 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-green-500/20 to-emerald-500/20 border border-green-500/30 rounded-full backdrop-blur w-fit mx-auto">
            <div class="w-2.5 h-2.5 rounded-full bg-green-400 animate-pulse"></div>
            <span class="text-green-300 text-xs font-bold tracking-wider">{{ __('SECURE SSL ENCRYPTED TRANSACTION') }}</span>
        </div>

        <!-- Main Container -->
        <div class="bg-gradient-to-b from-slate-800/80 via-slate-800/50 to-slate-900/80 rounded-3xl shadow-2xl overflow-hidden border border-slate-700/50 backdrop-blur-xl">
            
            <!-- Header with Gradient -->
            <div class="relative overflow-hidden h-32 sm:h-40">
                <div class="absolute inset-0 bg-gradient-to-r from-cyan-500 via-blue-600 to-purple-600 opacity-90"></div>
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute -top-20 -right-20 w-40 h-40 bg-white/20 rounded-full blur-2xl"></div>
                    <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                </div>
                
                <div class="relative h-full flex flex-col items-center justify-center px-6 text-center">
                    <div class="flex items-center gap-3 mb-3">
                        <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 8H4V6h16m0 12H4v-2h16m0-6H4c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-8c0-1.1-.9-2-2-2z"/>
                        </svg>
                        <h1 class="text-3xl sm:text-4xl font-black text-white">{{ __('Mobile Money Payment') }}</h1>
                    </div>
                    <p class="text-blue-100 text-sm font-semibold">{{ __('Fast • Secure • Transparent') }}</p>
                </div>
            </div>

            <!-- Progress Section -->
            <div class="px-6 sm:px-8 py-8 border-b border-slate-700/50">
                <div class="space-y-4">
                    <!-- Step Counter -->
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="text-sm text-gray-400">{{ __('Progress') }}</p>
                            <p class="text-2xl font-black text-white"><span id="current-step">1</span> / 7</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-400">{{ __('Completion') }}</p>
                            <p class="text-2xl font-black bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent"><span id="progress-percent">14</span>%</p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="relative h-2 bg-slate-700/60 rounded-full overflow-hidden border border-slate-600/40">
                        <div id="progress-bar" class="h-full bg-gradient-to-r from-cyan-500 via-blue-500 to-purple-500 transition-all duration-700 ease-out shadow-lg shadow-blue-500/50" style="width: 14%"></div>
                    </div>

                    <!-- Step Indicators -->
                    <div class="flex justify-between items-center mt-6">
                        <div class="step-indicator step-1 flex flex-col items-center group cursor-pointer">
                            <div class="relative mb-2">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 text-white text-xs leading-10 font-black text-center group-hover:scale-110 transition-transform">1</div>
                                <div class="absolute inset-0 rounded-full border-2 border-cyan-400 opacity-0 group-hover:opacity-100 scale-150 transition-all"></div>
                            </div>
                            <span class="text-xs text-gray-400 font-medium">{{ __('Details') }}</span>
                        </div>
                        <div class="flex-1 mx-1 h-1 bg-slate-700/40 rounded-full"></div>
                        
                        <div class="step-indicator step-2 flex flex-col items-center group cursor-pointer">
                            <div class="w-10 h-10 rounded-full bg-slate-700 text-gray-500 text-xs leading-10 font-black text-center group-hover:scale-110 transition-transform">2</div>
                            <span class="text-xs text-gray-500 font-medium">{{ __('Operator') }}</span>
                        </div>
                        <div class="flex-1 mx-1 h-1 bg-slate-700/40 rounded-full"></div>

                        <div class="step-indicator step-3 flex flex-col items-center group cursor-pointer">
                            <div class="w-10 h-10 rounded-full bg-slate-700 text-gray-500 text-xs leading-10 font-black text-center group-hover:scale-110 transition-transform">3</div>
                            <span class="text-xs text-gray-500 font-medium">{{ __('Phone') }}</span>
                        </div>
                        <div class="flex-1 mx-1 h-1 bg-slate-700/40 rounded-full"></div>

                        <div class="step-indicator step-4 flex flex-col items-center group cursor-pointer">
                            <div class="w-10 h-10 rounded-full bg-slate-700 text-gray-500 text-xs leading-10 font-black text-center group-hover:scale-110 transition-transform">4</div>
                            <span class="text-xs text-gray-500 font-medium">{{ __('Confirm') }}</span>
                        </div>
                        <div class="flex-1 mx-1 h-1 bg-slate-700/40 rounded-full"></div>

                        <div class="step-indicator step-5 flex flex-col items-center group cursor-pointer">
                            <div class="w-10 h-10 rounded-full bg-slate-700 text-gray-500 text-xs leading-10 font-black text-center group-hover:scale-110 transition-transform">5</div>
                            <span class="text-xs text-gray-500 font-medium">{{ __('Process') }}</span>
                        </div>
                        <div class="flex-1 mx-1 h-1 bg-slate-700/40 rounded-full"></div>

                        <div class="step-indicator step-6 flex flex-col items-center group cursor-pointer">
                            <div class="w-10 h-10 rounded-full bg-slate-700 text-gray-500 text-xs leading-10 font-black text-center group-hover:scale-110 transition-transform">6</div>
                            <span class="text-xs text-gray-500 font-medium">{{ __('Verify') }}</span>
                        </div>
                        <div class="flex-1 mx-1 h-1 bg-slate-700/40 rounded-full"></div>

                        <div class="step-indicator step-7 flex flex-col items-center group cursor-pointer">
                            <div class="w-10 h-10 rounded-full bg-slate-700 text-gray-500 text-xs leading-10 font-black text-center group-hover:scale-110 transition-transform">✓</div>
                            <span class="text-xs text-gray-500 font-medium">{{ __('Result') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="px-6 sm:px-8 py-8 min-h-96">
                
                <!-- STEP 1: Student & Amount Info -->
                <div id="step-1" class="step-content space-y-6">
                    <div>
                        <h2 class="text-3xl font-black text-white mb-8">{{ __('Payment Summary') }}</h2>
                        
                        <!-- Student Info Card -->
                        <div class="mb-6 group">
                            <div class="h-1 w-12 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-full mb-4 group-hover:w-24 transition-all"></div>
                            <div class="bg-gradient-to-br from-slate-700/50 to-slate-800/50 rounded-2xl p-6 border border-slate-600/40 hover:border-cyan-500/40 transition-all">
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white text-2xl font-black flex items-center justify-center shadow-lg">
                                        {{ substr($student->user->name ?? $student->first_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-gray-400 text-xs font-medium mb-1">{{ __('Student') }}</p>
                                        <p class="text-white text-xl font-bold">{{ $student->user->name ?? ($student->first_name . ' ' . $student->last_name) }}</p>
                                        <p class="text-gray-500 text-sm">{{ __('Matricule') }}: <span class="text-cyan-300 font-mono">{{ $student->matricule }}</span></p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-600/40">
                                    <div>
                                        <p class="text-gray-500 text-xs font-medium">{{ __('Class') }}</p>
                                        <p class="text-white font-semibold">{{ $student->classe->name ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500 text-xs font-medium">{{ __('Status') }}</p>
                                        <span class="inline-block px-3 py-1 bg-amber-500/20 text-amber-300 text-xs font-bold rounded-full">{{ __('ACTIVE') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Amount Card -->
                        <div class="mb-6 group">
                            <div class="h-1 w-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full mb-4 group-hover:w-24 transition-all"></div>
                            <div class="bg-gradient-to-br from-green-600/30 to-emerald-600/20 rounded-2xl p-6 border border-green-500/40 hover:border-green-400/60 transition-all">
                                <p class="text-green-300 text-xs font-bold uppercase tracking-wider mb-2">{{ __('Amount to Pay') }}</p>
                                <p class="text-5xl font-black bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent" id="amount-display">
                                    {{ number_format($amount ?? 500000, 0, ',', ' ') }} <span class="text-2xl">XAF</span>
                                </p>
                                <p class="text-green-300/70 text-sm mt-2">{{ __('School Tuition Fees') }}</p>
                            </div>
                        </div>

                        <!-- Summary Stats -->
                        <div class="grid grid-cols-3 gap-4 mb-8">
                            <div class="bg-slate-700/40 rounded-xl p-4 border border-slate-600/40 text-center hover:border-red-500/40 transition-all">
                                <p class="text-gray-500 text-xs font-medium mb-2">{{ __('Total Due') }}</p>
                                <p class="text-red-400 text-xl font-black">{{ number_format($student->getTotalAmountDue() ?? 0, 0) }} XAF</p>
                            </div>
                            <div class="bg-slate-700/40 rounded-xl p-4 border border-slate-600/40 text-center hover:border-green-500/40 transition-all">
                                <p class="text-gray-500 text-xs font-medium mb-2">{{ __('Total Paid') }}</p>
                                <p class="text-green-400 text-xl font-black">{{ number_format($student->getTotalAmountPaid() ?? 0, 0) }} XAF</p>
                            </div>
                            <div class="bg-slate-700/40 rounded-xl p-4 border border-slate-600/40 text-center hover:border-amber-500/40 transition-all">
                                <p class="text-gray-500 text-xs font-medium mb-2">{{ __('Balance') }}</p>
                                <p class="text-amber-400 text-xl font-black">{{ number_format(($student->getTotalAmountDue() ?? 0) - ($student->getTotalAmountPaid() ?? 0), 0) }} XAF</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <button onclick="goToStep(2)" class="w-full px-6 py-4 bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-bold rounded-xl hover:shadow-lg hover:shadow-blue-500/50 transition-all duration-300 text-lg group flex items-center justify-center gap-2">
                        <span>{{ __('Proceed to Payment') }}</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </button>
                </div>

                <!-- STEP 2: Operator Selection -->
                <div id="step-2" class="step-content hidden space-y-6">
                    <h2 class="text-3xl font-black text-white">{{ __('Select Payment Operator') }}</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                        <!-- Orange Money -->
                        <div onclick="selectOperator('orange')" class="group cursor-pointer">
                            <div id="orange-card" class="relative overflow-hidden bg-gradient-to-br from-orange-600/30 to-red-600/20 rounded-2xl p-8 border-2 border-slate-600/40 hover:border-orange-500/60 transition-all duration-300 group-hover:scale-105 transform">
                                <div class="absolute -top-20 -right-20 w-40 h-40 bg-orange-500/10 rounded-full blur-3xl group-hover:scale-150 transition-transform"></div>
                                
                                <div class="relative z-10 text-center">
                                    <div class="w-20 h-20 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:shadow-orange-500/50">
                                        <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" fill="currentColor" opacity="0.1"/>
                                            <circle cx="8" cy="8" r="2" fill="white"/>
                                            <circle cx="16" cy="8" r="2" fill="white"/>
                                            <path d="M12 16c-2 0-3-1-3-2s1-2 3-2 3 1 3 2-1 2-3 2z" fill="white"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-2xl font-black text-white mb-2">Orange Money</h3>
                                    <p class="text-orange-300 text-sm font-semibold mb-4">{{ __('Fast & Reliable') }}</p>
                                    <div class="space-y-2 text-left text-sm">
                                        <p class="text-gray-400"><span class="text-yellow-300">•</span> {{ __('Dial') }} <span class="font-bold text-yellow-300">#150*</span> {{ __('or') }} <span class="font-bold text-yellow-300">*611#</span></p>
                                        <p class="text-gray-400"><span class="text-yellow-300">•</span> {{ __('Available on all networks') }}</p>
                                        <p class="text-gray-400"><span class="text-yellow-300">•</span> {{ __('Instant confirmation') }}</p>
                                    </div>
                                    
                                    <div class="mt-6 inline-block px-6 py-2 bg-orange-500/30 text-orange-300 rounded-full text-xs font-bold uppercase tracking-wider border border-orange-500/50">
                                        {{ __('Select') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MTN MoMo -->
                        <div onclick="selectOperator('mtn')" class="group cursor-pointer">
                            <div id="mtn-card" class="relative overflow-hidden bg-gradient-to-br from-yellow-600/30 to-amber-600/20 rounded-2xl p-8 border-2 border-slate-600/40 hover:border-yellow-500/60 transition-all duration-300 group-hover:scale-105 transform">
                                <div class="absolute -top-20 -right-20 w-40 h-40 bg-yellow-500/10 rounded-full blur-3xl group-hover:scale-150 transition-transform"></div>
                                
                                <div class="relative z-10 text-center">
                                    <div class="w-20 h-20 bg-gradient-to-br from-yellow-500 to-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:shadow-yellow-500/50">
                                        <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" fill="currentColor" opacity="0.1"/>
                                            <circle cx="9" cy="9" r="2" fill="white"/>
                                            <circle cx="15" cy="9" r="2" fill="white"/>
                                            <path d="M12 15c1.5 0 3 .5 3 1.5S13.5 18 12 18s-3-.5-3-1.5S10.5 15 12 15z" fill="white"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-2xl font-black text-white mb-2">MTN MoMo</h3>
                                    <p class="text-yellow-300 text-sm font-semibold mb-4">{{ __('Quick Transfer') }}</p>
                                    <div class="space-y-2 text-left text-sm">
                                        <p class="text-gray-400"><span class="text-amber-300">•</span> {{ __('Dial') }} <span class="font-bold text-amber-300">#156#</span> {{ __('or') }} <span class="font-bold text-amber-300">*150#</span></p>
                                        <p class="text-gray-400"><span class="text-amber-300">•</span> {{ __('All MTN subscribers') }}</p>
                                        <p class="text-gray-400"><span class="text-amber-300">•</span> {{ __('Instant settlement') }}</p>
                                    </div>
                                    
                                    <div class="mt-6 inline-block px-6 py-2 bg-yellow-500/30 text-yellow-300 rounded-full text-xs font-bold uppercase tracking-wider border border-yellow-500/50">
                                        {{ __('Select') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex gap-4">
                        <button onclick="goToStep(1)" class="flex-1 px-6 py-3 border-2 border-slate-600 text-gray-300 font-bold rounded-xl hover:border-slate-500 hover:bg-slate-700/30 transition-all">
                            ← {{ __('Back') }}
                        </button>
                        <button id="next-step-2" onclick="goToStep(3)" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-xl hover:shadow-lg hover:shadow-blue-500/50 transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            {{ __('Continue') }} →
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Phone Number Input -->
                <div id="step-3" class="step-content hidden space-y-6">
                    <h2 class="text-3xl font-black text-white">{{ __('Enter Your Phone Number') }}</h2>
                    
                    <!-- Info Box -->
                    <div class="bg-blue-500/20 border-l-4 border-blue-500 rounded-xl p-4">
                        <p class="text-blue-300 text-sm">
                            <strong>{{ __('Required Format:') }}</strong> 6XXXXXXXX {{ __('(9 digits starting with 6)') }}
                        </p>
                    </div>

                    <!-- Phone Input -->
                    <div class="space-y-4">
                        <label class="block">
                            <span class="text-gray-300 text-sm font-bold uppercase tracking-wider mb-3 block">{{ __('Mobile Number') }}</span>
                            <div class="relative">
                                <span class="absolute left-4 top-4 text-gray-500 font-bold text-lg">+237</span>
                                <input 
                                    type="tel" 
                                    id="phone-input"
                                    placeholder="6XXXXXXXX"
                                    maxlength="9"
                                    class="w-full pl-20 pr-4 py-4 bg-slate-700/50 border-2 border-slate-600/60 text-white rounded-xl focus:border-blue-500/60 focus:outline-none focus:ring-2 focus:ring-blue-500/20 text-lg tracking-widest font-mono transition-all"
                                    oninput="validatePhoneFormat(this.value)"
                                />
                            </div>
                            <p id="phone-error" class="text-red-400 text-xs mt-2 hidden font-semibold">
                                ✗ {{ __('Invalid format. Please use 6XXXXXXXX') }}
                            </p>
                            <p id="phone-success" class="text-green-400 text-xs mt-2 hidden font-semibold">
                                ✓ {{ __('Valid phone number') }}
                            </p>
                        </label>
                    </div>

                    <!-- Important Note -->
                    <div class="bg-amber-500/20 border-l-4 border-amber-500 rounded-xl p-4">
                        <p class="text-amber-300 text-sm font-semibold">
                            <strong>{{ __('Important:') }}</strong> {{ __('The phone must be your registered account with Orange or MTN') }}
                        </p>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex gap-4 pt-4">
                        <button onclick="goToStep(2)" class="flex-1 px-6 py-3 border-2 border-slate-600 text-gray-300 font-bold rounded-xl hover:border-slate-500 hover:bg-slate-700/30 transition-all">
                            ← {{ __('Back') }}
                        </button>
                        <button id="next-step-3" onclick="goToStep(4)" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-xl hover:shadow-lg hover:shadow-blue-500/50 transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                            {{ __('Verify') }} →
                        </button>
                    </div>
                </div>

                <!-- STEP 4: Confirmation -->
                <div id="step-4" class="step-content hidden space-y-6">
                    <h2 class="text-3xl font-black text-white">{{ __('Confirm Payment') }}</h2>

                    <!-- Summary Card -->
                    <div class="bg-gradient-to-br from-slate-700/60 to-slate-800/60 rounded-2xl p-8 border border-slate-600/40 space-y-4">
                        <div class="flex justify-between items-center pb-4 border-b border-slate-600/40">
                            <span class="text-gray-400 font-medium">{{ __('Student') }}</span>
                            <span class="text-white font-bold text-lg" id="confirm-student"></span>
                        </div>
                        <div class="flex justify-between items-center pb-4 border-b border-slate-600/40">
                            <span class="text-gray-400 font-medium">{{ __('Class') }}</span>
                            <span class="text-white font-bold">{{ $student->classe->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center pb-4 border-b border-slate-600/40">
                            <span class="text-gray-400 font-medium">{{ __('Operator') }}</span>
                            <span class="text-white font-bold text-lg" id="confirm-operator"></span>
                        </div>
                        <div class="flex justify-between items-center pb-4 border-b border-slate-600/40">
                            <span class="text-gray-400 font-medium">{{ __('Phone Number') }}</span>
                            <span class="text-white font-bold font-mono">+237 <span id="confirm-phone"></span></span>
                        </div>
                        <div class="flex justify-between items-center pt-4 border-t-2 border-blue-500/40">
                            <span class="text-lg font-bold text-gray-300">{{ __('Amount') }}</span>
                            <span class="text-3xl font-black bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent" id="confirm-amount"></span>
                        </div>
                    </div>

                    <!-- Warning -->
                    <div class="bg-red-500/20 border-l-4 border-red-500 rounded-xl p-4">
                        <p class="text-red-300 text-sm font-semibold flex items-start gap-2">
                            <span class="text-lg">⚠️</span>
                            <span>{{ __('Do NOT close this browser or go back once payment starts. Wait for confirmation.') }}</span>
                        </p>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex gap-4 pt-4">
                        <button onclick="goToStep(3)" class="flex-1 px-6 py-3 border-2 border-slate-600 text-gray-300 font-bold rounded-xl hover:border-slate-500 hover:bg-slate-700/30 transition-all">
                            ← {{ __('Review') }}
                        </button>
                        <button onclick="goToStep(5)" class="flex-1 px-6 py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold rounded-xl hover:shadow-lg hover:shadow-green-500/50 transition-all text-lg">
                            ✓ {{ __('Initiate Payment') }}
                        </button>
                    </div>
                </div>

                <!-- STEP 5: Processing -->
                <div id="step-5" class="step-content hidden text-center space-y-8">
                    <!-- Animated Loading -->
                    <div class="flex justify-center mb-8">
                        <div class="relative w-28 h-28">
                            <svg class="absolute inset-0 animate-spin" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="rgba(148, 163, 184, 0.2)" stroke-width="4"/>
                                <circle cx="50" cy="50" r="45" fill="none" stroke="url(#gradient)" stroke-width="4" stroke-dasharray="141 282" stroke-linecap="round"/>
                                <defs>
                                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" style="stop-color:#00d4ff;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#7c3aed;stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-12 h-12 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20 8H4V6h16m0 12H4v-2h16m0-6H4c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2v-8c0-1.1-.9-2-2-2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-2xl font-black text-white mb-3">{{ __('Processing Payment...') }}</h3>
                        <p class="text-gray-400 text-lg mb-4">{{ __('A payment prompt will appear on your phone') }}</p>
                        <p class="text-blue-400 font-bold text-xl font-mono" id="operator-ussd"></p>
                    </div>

                    <!-- Status Info -->
                    <div class="bg-slate-700/40 rounded-xl p-6 border border-slate-600/40">
                        <p class="text-gray-400 text-sm mb-2">{{ __('Status') }}:</p>
                        <p id="ussd-status" class="text-white text-lg font-bold">{{ __('Initiating...') }}</p>
                        <p class="text-gray-500 text-xs mt-3">{{ __('Please wait while we connect to your operator') }}</p>
                    </div>

                    <!-- Progress Bar -->
                    <div>
                        <p class="text-gray-400 text-xs mb-2">{{ __('Auto-retry in progress...') }}</p>
                        <div class="h-2 bg-slate-700/60 rounded-full overflow-hidden border border-slate-600/40">
                            <div id="retry-progress" class="h-full bg-gradient-to-r from-cyan-500 to-blue-500 transition-all duration-100" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <!-- STEP 6: Verification -->
                <div id="step-6" class="step-content hidden text-center space-y-8">
                    <!-- Checking Spinner -->
                    <div class="flex justify-center">
                        <div class="relative w-28 h-28">
                            <svg class="absolute inset-0 animate-pulse opacity-50" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="rgba(59, 130, 246, 0.3)" stroke-width="4"/>
                            </svg>
                            <svg class="absolute inset-0 animate-spin" style="animation-duration: 3s;" viewBox="0 0 100 100">
                                <path d="M 50 10 A 40 40 0 0 1 85 50" fill="none" stroke="url(#gradient2)" stroke-width="4" stroke-linecap="round"/>
                                <defs>
                                    <linearGradient id="gradient2" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#06b6d4;stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-12 h-12 text-blue-400 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-2xl font-black text-white mb-3">{{ __('Verifying Payment') }}</h3>
                        <p class="text-gray-400 text-lg mb-4">{{ __('Please confirm on your phone when prompted') }}</p>
                        <p class="text-gray-500 text-sm">{{ __('Enter your PIN and wait for confirmation') }}</p>
                    </div>

                    <!-- Polling Info -->
                    <div class="bg-slate-700/40 rounded-xl p-6 border border-slate-600/40">
                        <p class="text-gray-400 text-sm mb-3">{{ __('Checking status every 3 seconds...') }}</p>
                        <p class="text-white text-lg font-bold">{{ __('Attempt') }} <span id="polling-attempt">1</span> {{ __('of') }} 40</p>
                        <div class="h-2 bg-slate-700/60 rounded-full overflow-hidden border border-slate-600/40 mt-3">
                            <div id="timeout-progress" class="h-full bg-gradient-to-r from-cyan-500 to-blue-500 transition-all duration-100" style="width: 0%"></div>
                        </div>
                        <p class="text-gray-500 text-xs mt-2">{{ __('Timeout in 2 minutes') }}</p>
                    </div>
                </div>

                <!-- STEP 7: Result -->
                <div id="step-7" class="step-content hidden">
                    <!-- Success Result -->
                    <div id="success-result" class="hidden text-center space-y-8">
                        <!-- Success Animation -->
                        <div class="flex justify-center">
                            <div class="relative w-32 h-32">
                                <div class="absolute inset-0 rounded-full bg-green-500/20 animate-ping" style="animation-duration: 2s;"></div>
                                <div class="absolute inset-0 rounded-full bg-green-500 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h2 class="text-4xl font-black text-white mb-3">{{ __('Payment Successful!') }}</h2>
                            <p class="text-green-400 text-xl font-bold">{{ __('Your payment has been processed successfully') }}</p>
                        </div>

                        <!-- Receipt -->
                        <div class="bg-gradient-to-br from-green-600/20 to-emerald-600/20 rounded-2xl p-8 border border-green-500/40 text-left space-y-4">
                            <div class="flex justify-between items-center pb-4 border-b border-green-500/40">
                                <span class="text-gray-400 text-sm">{{ __('Transaction ID') }}</span>
                                <span id="receipt-transaction-id" class="text-white font-mono font-bold text-sm"></span>
                            </div>
                            <div class="flex justify-between items-center pb-4 border-b border-green-500/40">
                                <span class="text-gray-400 text-sm">{{ __('Amount') }}</span>
                                <span id="receipt-amount" class="text-green-400 font-black text-xl"></span>
                            </div>
                            <div class="flex justify-between items-center pb-4 border-b border-green-500/40">
                                <span class="text-gray-400 text-sm">{{ __('Date & Time') }}</span>
                                <span id="receipt-datetime" class="text-white font-mono text-sm"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400 text-sm">{{ __('Status') }}</span>
                                <span class="inline-block px-4 py-1 bg-green-500/40 text-green-300 rounded-full text-xs font-bold">{{ __('COMPLETED') }}</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-4 flex-col sm:flex-row pt-4">
                            <button onclick="downloadReceipt()" class="flex-1 px-6 py-4 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold rounded-xl hover:shadow-lg hover:shadow-blue-500/50 transition-all">
                                📥 {{ __('Download Receipt') }}
                            </button>
                            <button onclick="returnToDashboard()" class="flex-1 px-6 py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold rounded-xl hover:shadow-lg hover:shadow-green-500/50 transition-all">
                                ✓ {{ __('Back to Dashboard') }}
                            </button>
                        </div>
                    </div>

                    <!-- Failure Result -->
                    <div id="failure-result" class="hidden text-center space-y-8">
                        <!-- Failure Animation -->
                        <div class="flex justify-center">
                            <div class="relative w-32 h-32">
                                <div class="absolute inset-0 rounded-full bg-red-500/20 animate-pulse"></div>
                                <div class="absolute inset-0 rounded-full bg-red-500 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h2 class="text-4xl font-black text-white mb-3">{{ __('Payment Failed') }}</h2>
                            <p id="failure-message" class="text-red-400 text-xl font-bold"></p>
                        </div>

                        <!-- Failure Reasons -->
                        <div class="bg-red-500/20 border-l-4 border-red-500 rounded-xl p-6 text-left">
                            <p class="text-red-300 font-bold mb-3">{{ __('Possible reasons') }}:</p>
                            <ul class="space-y-2 text-red-300 text-sm">
                                <li class="flex items-center gap-2"><span>•</span> {{ __('Insufficient account balance') }}</li>
                                <li class="flex items-center gap-2"><span>•</span> {{ __('Invalid phone number entered') }}</li>
                                <li class="flex items-center gap-2"><span>•</span> {{ __('Request timeout') }}</li>
                                <li class="flex items-center gap-2"><span>•</span> {{ __('Network connection error') }}</li>
                                <li class="flex items-center gap-2"><span>•</span> {{ __('Payment cancelled') }}</li>
                            </ul>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-4 flex-col sm:flex-row pt-4">
                            <button onclick="goToStep(3)" class="flex-1 px-6 py-4 border-2 border-red-600 text-red-400 font-bold rounded-xl hover:bg-red-500/20 transition-all">
                                🔄 {{ __('Try Again') }}
                            </button>
                            <button onclick="returnToDashboard()" class="flex-1 px-6 py-4 bg-gradient-to-r from-gray-600 to-slate-700 text-white font-bold rounded-xl hover:shadow-lg transition-all">
                                ← {{ __('Return to Payments') }}
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-gray-500 text-sm">
                {{ __('Need assistance?') }} 
                <a href="#" class="text-blue-400 hover:text-blue-300 font-semibold">{{ __('Contact Support') }}</a>
                {{ __('or call') }} <span class="text-blue-400 font-semibold">+237 XXX XXX XXX</span>
            </p>
            <p class="text-gray-600 text-xs mt-2">{{ __('All transactions are encrypted and secure') }}</p>
        </div>
    </div>
</div>

<script>
let currentStep = 1;
let selectedOperator = null;
let selectedPhone = null;
let selectedAmount = {{ $amount ?? 500000 }};
let transactionId = null;
let pollingInterval = null;
let pollingCount = 0;
const MAX_POLLING = 40;

const studentData = {
    name: '{{ $student->user->name ?? "N/A" }}',
    class: '{{ $student->classe->name ?? "N/A" }}',
    matricule: '{{ $student->matricule }}',
};

function goToStep(step) {
    if (step === 3 && !selectedOperator) {
        alert('{{ __("Please select a payment operator") }}');
        return;
    }
    if (step === 4 && !selectedPhone) {
        alert('{{ __("Please enter a valid phone number") }}');
        return;
    }

    document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
    document.getElementById(`step-${step}`).classList.remove('hidden');

    currentStep = step;
    const percentage = (step / 7) * 100;
    document.getElementById('progress-bar').style.width = percentage + '%';
    document.getElementById('current-step').textContent = step;
    document.getElementById('progress-percent').textContent = Math.round(percentage);

    document.querySelectorAll('.step-indicator').forEach((indicator, idx) => {
        const stepNum = idx + 1;
        const span = indicator.querySelector('div');
        if (stepNum <= step) {
            span.classList.add('bg-gradient-to-br', 'from-cyan-500', 'to-blue-600', 'text-white');
            span.classList.remove('bg-slate-700', 'text-gray-500');
        } else {
            span.classList.remove('bg-gradient-to-br', 'from-cyan-500', 'to-blue-600', 'text-white');
            span.classList.add('bg-slate-700', 'text-gray-500');
        }
    });

    if (step === 4) {
        document.getElementById('confirm-student').textContent = studentData.name;
        document.getElementById('confirm-operator').textContent = selectedOperator === 'orange' ? 'Orange Money' : 'MTN MoMo';
        document.getElementById('confirm-phone').textContent = selectedPhone;
        document.getElementById('confirm-amount').textContent = new Intl.NumberFormat('fr-CM', {
            style: 'currency',
            currency: 'XAF',
            minimumFractionDigits: 0
        }).format(selectedAmount);
    } else if (step === 5) {
        initiatePayment();
    } else if (step === 6) {
        startPolling();
    }
}

function selectOperator(operator) {
    selectedOperator = operator;
    
    const orangeCard = document.getElementById('orange-card');
    const mtnCard = document.getElementById('mtn-card');
    
    if (operator === 'orange') {
        orangeCard.classList.add('border-orange-500/60', 'bg-orange-600/40', 'scale-105');
        orangeCard.classList.remove('border-slate-600/40', 'hover:border-orange-500/60');
        mtnCard.classList.remove('border-yellow-500/60', 'bg-yellow-600/40', 'scale-105');
        mtnCard.classList.add('border-slate-600/40', 'hover:border-yellow-500/60');
    } else {
        mtnCard.classList.add('border-yellow-500/60', 'bg-yellow-600/40', 'scale-105');
        mtnCard.classList.remove('border-slate-600/40', 'hover:border-yellow-500/60');
        orangeCard.classList.remove('border-orange-500/60', 'bg-orange-600/40', 'scale-105');
        orangeCard.classList.add('border-slate-600/40', 'hover:border-orange-500/60');
    }
    
    document.getElementById('next-step-2').disabled = false;
    document.getElementById('next-step-2').classList.remove('opacity-50', 'cursor-not-allowed');
}

function validatePhoneFormat(value) {
    const phoneInput = document.getElementById('phone-input');
    const phoneError = document.getElementById('phone-error');
    const phoneSuccess = document.getElementById('phone-success');
    const nextBtn = document.getElementById('next-step-3');
    
    const cleaned = value.replace(/\D/g, '');
    phoneInput.value = cleaned;
    
    const isValid = cleaned.length === 9 && cleaned.startsWith('6');
    
    if (cleaned.length > 0 && !isValid) {
        phoneError.classList.remove('hidden');
        phoneSuccess.classList.add('hidden');
        nextBtn.disabled = true;
        nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
    } else if (isValid) {
        phoneError.classList.add('hidden');
        phoneSuccess.classList.remove('hidden');
        selectedPhone = cleaned;
        nextBtn.disabled = false;
        nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

async function initiatePayment() {
    const operatorUssd = selectedOperator === 'orange' ? '*611#' : '*156#';
    document.getElementById('operator-ussd').textContent = `{{ __('Dial') }} ${operatorUssd}`;

    try {
        const response = await fetch('{{ route("mobile-money.initiate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                student_id: {{ $student->id }},
                operator: selectedOperator,
                phone_number: '+237' + selectedPhone,
                amount: selectedAmount,
                purpose: 'tuition_fees',
                description: 'School Tuition Fees'
            })
        });

        const data = await response.json();
        
        if (data.success) {
            transactionId = data.transaction_id;
            document.getElementById('ussd-status').textContent = '{{ __("Payment initiated, waiting for confirmation...") }}';
        } else {
            alert('{{ __("Payment initiation failed") }}: ' + (data.message || 'Unknown error'));
            goToStep(4);
        }
    } catch (error) {
        console.error('Payment initiation error:', error);
        alert('{{ __("Error initiating payment") }}');
        goToStep(4);
    }
}

function startPolling() {
    pollingCount = 0;
    let elapsedSeconds = 0;

    pollingInterval = setInterval(async () => {
        pollingCount++;
        elapsedSeconds += 3;
        const percentage = (elapsedSeconds / 120) * 100;
        
        document.getElementById('polling-attempt').textContent = pollingCount;
        document.getElementById('timeout-progress').style.width = percentage + '%';

        try {
            const response = await fetch(`{{ route("mobile-money.check-status") }}/?transaction_id=${transactionId}`);
            const data = await response.json();

            if (data.status === 'completed') {
                clearInterval(pollingInterval);
                showSuccessResult(data);
                goToStep(7);
            } else if (data.status === 'failed') {
                clearInterval(pollingInterval);
                showFailureResult(data.message || '{{ __("Payment was rejected") }}');
                goToStep(7);
            }
        } catch (error) {
            console.error('Polling error:', error);
        }

        if (pollingCount >= MAX_POLLING) {
            clearInterval(pollingInterval);
            showFailureResult('{{ __("Payment timeout. Please try again.") }}');
            goToStep(7);
        }
    }, 3000);
}

function showSuccessResult(data) {
    document.getElementById('success-result').classList.remove('hidden');
    document.getElementById('failure-result').classList.add('hidden');
    
    document.getElementById('receipt-transaction-id').textContent = data.transaction_id || transactionId;
    document.getElementById('receipt-amount').textContent = new Intl.NumberFormat('fr-CM', {
        style: 'currency',
        currency: 'XAF',
        minimumFractionDigits: 0
    }).format(selectedAmount);
    document.getElementById('receipt-datetime').textContent = new Date().toLocaleString();
}

function showFailureResult(message) {
    document.getElementById('success-result').classList.add('hidden');
    document.getElementById('failure-result').classList.remove('hidden');
    document.getElementById('failure-message').textContent = message;
}

function downloadReceipt() {
    window.print();
}

function returnToDashboard() {
    window.location.href = '{{ route("parent.payments") }}';
}

document.addEventListener('DOMContentLoaded', function() {
    goToStep(1);
});
</script>

@endsection
