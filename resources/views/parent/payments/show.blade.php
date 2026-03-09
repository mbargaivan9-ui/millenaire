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
    <div class="mb-8">
        <button onclick="showPaymentModal({{ $student->id }})" 
                class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition shadow-lg">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            {{ __('Make Payment') }}
        </button>
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
                                <a href="{{ route('parent.receipts.download', $payment->receipt) }}" 
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

<!-- Payment Modal -->
<div id="payment-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">{{ __('Make Payment') }}</h3>
            <button onclick="closePaymentModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="payment-form" class="p-6">
            @csrf
            <input type="hidden" name="student_id" value="{{ $student->id }}">

            <!-- Amount Input -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Amount (XAF)') }}</label>
                <input type="number" name="amount" required step="1000" min="1000" max="5000000" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-indigo-600 focus:outline-none"
                       placeholder="Enter amount">
            </div>

            <!-- Phone Number Input -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Phone Number') }}</label>
                <input type="tel" name="phone_number" required 
                       pattern="^([+]|00)?[0-9\s\-()]{9,15}$"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-indigo-600 focus:outline-none"
                       placeholder="+237 6XX XXX XXX">
            </div>

            <!-- Purpose Select -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Payment Purpose') }}</label>
                <select name="purpose" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-indigo-600 focus:outline-none">
                    <option value="">{{ __('Select purpose') }}</option>
                    <option value="tuition_fees">{{ __('School Tuition Fees') }}</option>
                    <option value="exam_fees">{{ __('Examination Fees') }}</option>
                    <option value="uniform">{{ __('School Uniform') }}</option>
                    <option value="books">{{ __('Books & Materials') }}</option>
                    <option value="other">{{ __('Other') }}</option>
                </select>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Description') }}</label>
                <textarea name="description" rows="3" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-indigo-600 focus:outline-none"
                          placeholder="{{ __('Optional description') }}"></textarea>
            </div>

            <!-- Buttons -->
            <div class="flex space-x-4">
                <button type="button" onclick="closePaymentModal()" 
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold transition">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold transition">
                    {{ __('Initiate Payment') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function showPaymentModal(childId) {
        document.getElementById('payment-modal').classList.remove('hidden');
        document.querySelector('input[name="student_id"]').value = childId;
    }

    function closePaymentModal() {
        document.getElementById('payment-modal').classList.add('hidden');
        document.getElementById('payment-form').reset();
    }

    document.getElementById('payment-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);

        try {
            const response = await fetch('{{ route("parent.payments.initiate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();

            if (result.success) {
                alert('{{ __("Payment initiated successfully!") }}');
                closePaymentModal();
                location.reload();
            } else {
                alert('{{ __("Error:") }} ' + (result.message || '{{ __("Unknown error") }}'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('{{ __("Failed to initiate payment") }}');
        }
    });

    function checkPaymentStatus(paymentId) {
        fetch(`/parent/payments/${paymentId}/status`)
            .then(res => res.json())
            .then(data => {
                alert(`{{ __('Status:') }} ${data.status_label}\n{{ __('Completed:') }} ${data.completed_at || '{{ __('Not yet') }}'}`);
            })
            .catch(err => alert('{{ __("Error checking status") }}'));
    }
</script>
