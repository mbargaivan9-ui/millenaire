<template>
  <div class="bg-white rounded-xl shadow-lg p-8 max-w-md mx-auto">
    <!-- Header -->
    <div class="text-center mb-8">
      <h2 class="text-3xl font-bold text-gray-800 mb-2">💰 Effectuer un Paiement</h2>
      <p class="text-gray-600">Paiement sécurisé par mobile money</p>
    </div>

    <!-- Amount Display -->
    <div v-if="selectedChild" class="bg-gradient-to-r from-[#2E5BFF] to-[#4A7FFF] text-white rounded-lg p-6 mb-6">
      <p class="text-sm opacity-90 mb-1">Paiement pour</p>
      <h3 class="text-xl font-bold mb-4">{{ selectedChild.name }}</h3>

      <div class="mb-6 border-t border-white/20 pt-4">
        <p class="text-sm opacity-90">Montant à payer</p>
        <p class="text-4xl font-bold">{{ amount.toLocaleString('fr-FR') }}<span class="text-lg"> XAF</span></p>
      </div>

      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span>Status:</span>
          <span class="font-semibold">{{ selectedChild.financial_status === 'paid' ? '✓ Payé' : '⚠️ Non Payé' }}</span>
        </div>
        <div class="flex justify-between">
          <span>ID Étudiant:</span>
          <span class="font-semibold">{{ selectedChild.student_id }}</span>
        </div>
      </div>
    </div>

    <!-- Form -->
    <form @submit.prevent="handlePayment" class="space-y-4">
      <!-- Amount Input -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Montant (XAF)</label>
        <div class="relative">
          <span class="absolute left-3 top-3 text-gray-600 font-bold">XAF</span>
          <input
            v-model.number="form.amount"
            type="number"
            min="100"
            max="1000000"
            step="1000"
            placeholder="10000"
            class="w-full pl-12 pr-4 py-3 rounded-lg border-2 border-gray-300 focus:border-[#2E5BFF] focus:outline-none transition-colors"
            :disabled="isProcessing"
          />
          <span v-if="form.amount > 0" class="absolute right-3 top-3 text-sm text-gray-600">
            {{ (form.amount / 1000).toFixed(1) }K
          </span>
        </div>
        <div class="mt-2 text-xs text-gray-600 flex justify-between">
          <span>Min: 100 XAF</span>
          <span>Max: 1,000,000 XAF</span>
        </div>
      </div>

      <!-- Phone Number -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Numéro de Téléphone</label>
        <div class="relative">
          <span class="absolute left-3 top-3 text-gray-600">🇨🇲</span>
          <input
            v-model="form.phone"
            type="tel"
            placeholder="6XX XXX XXX ou +237 6XX XXX XXX"
            class="w-full pl-12 pr-4 py-3 rounded-lg border-2 border-gray-300 focus:border-[#2E5BFF] focus:outline-none transition-colors"
            :disabled="isProcessing"
          />
        </div>
        <span v-if="errors.phone" class="text-sm text-[#FF3B30] mt-1 block">{{ errors.phone }}</span>
        <p class="text-xs text-gray-500 mt-2">Numéro Camerounais requis</p>
      </div>

      <!-- Provider Selection -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-3">Mode de Paiement</label>
        <div class="grid grid-cols-3 gap-3">
          <button
            v-for="provider in providers"
            :key="provider.value"
            type="button"
            @click="form.provider = provider.value"
            :class="[
              'p-4 rounded-lg border-2 transition-all duration-300 text-center',
              form.provider === provider.value
                ? 'border-[#2E5BFF] bg-[#2E5BFF]/10 shadow-lg'
                : 'border-gray-300 hover:border-gray-400'
            ]"
            :disabled="isProcessing"
          >
            <div class="text-3xl mb-2">{{ provider.icon }}</div>
            <div class="text-sm font-bold text-gray-800">{{ provider.label }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ provider.name }}</div>
          </button>
        </div>
      </div>

      <!-- Remember Phone -->
      <label class="flex items-center gap-2 cursor-pointer">
        <input
          v-model="form.remember"
          type="checkbox"
          class="w-4 h-4 rounded border-gray-300 text-[#2E5BFF] focus:ring-[#2E5BFF]"
          :disabled="isProcessing"
        />
        <span class="text-sm text-gray-600">Se souvenir de ce numéro</span>
      </label>

      <!-- Info Box -->
      <div class="bg-[#2E5BFF]/10 border-l-4 border-[#2E5BFF] p-4 rounded-lg">
        <p class="text-xs text-gray-700">
          <span class="font-semibold">🔒 Sécurité:</span> Votre transaction est sécurisée et chiffrée. Vous recevrez une confirmation via SMS.
        </p>
      </div>

      <!-- Error Alert -->
      <transition name="fade">
        <div v-if="errors.general" class="bg-[#FF3B30]/10 border-l-4 border-[#FF3B30] p-4 rounded-lg">
          <p class="text-sm text-[#FF3B30] font-medium">{{ errors.general }}</p>
        </div>
      </transition>

      <!-- Submit Buttons -->
      <div class="flex gap-2 pt-4">
        <button
          type="submit"
          :disabled="isProcessing || !form.phone || !form.amount || !form.provider"
          class="flex-1 py-3 rounded-lg font-bold text-white bg-gradient-to-r from-[#2E5BFF] to-[#4A7FFF] hover:shadow-lg transition-all duration-300 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
        >
          <span v-if="isProcessing" class="animate-spin">⏳</span>
          <span v-else>💳</span>
          {{ isProcessing ? 'Traitement...' : 'Payer maintenant' }}
        </button>
        <button
          type="button"
          @click="$emit('cancel')"
          class="flex-1 py-3 rounded-lg font-bold text-gray-700 bg-gray-200 hover:bg-gray-300 transition-all duration-300 disabled:opacity-70"
          :disabled="isProcessing"
        >
          ✕ Annuler
        </button>
      </div>
    </form>

    <!-- Transaction Status -->
    <transition name="slide-up">
      <div v-if="transaction" class="mt-8 p-6 rounded-lg" :class="{
        'bg-[#00C48C]/10 border-2 border-[#00C48C]': transaction.status === 'success',
        'bg-[#FF9500]/10 border-2 border-[#FF9500]': transaction.status === 'pending',
        'bg-[#FF3B30]/10 border-2 border-[#FF3B30]': transaction.status === 'error'
      }">
        <div class="text-center mb-4">
          <span v-if="transaction.status === 'success'" class="text-4xl">✓</span>
          <span v-else-if="transaction.status === 'pending'" class="text-4xl animate-spin">⏳</span>
          <span v-else class="text-4xl">✕</span>
        </div>
        <h4 class="font-bold text-lg text-gray-800 mb-2">
          {{ transaction.status === 'success' ? 'Paiement Réussi' : transaction.status === 'pending' ? 'Paiement en Cours' : 'Erreur de Paiement' }}
        </h4>
        <p class="text-sm text-gray-600 mb-4">{{ transaction.message }}</p>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-600">Reference:</span>
            <span class="font-monospace font-semibold">{{ transaction.reference }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Montant:</span>
            <span class="font-semibold">{{ form.amount.toLocaleString('fr-FR') }} XAF</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Heure:</span>
            <span class="font-semibold">{{ new Date(transaction.timestamp).toLocaleTimeString('fr-FR') }}</span>
          </div>
        </div>

        <div class="mt-4 flex gap-2">
          <button
            v-if="transaction.status === 'success'"
            @click="downloadReceipt"
            class="flex-1 px-4 py-2 rounded-lg bg-[#00C48C] text-white font-medium hover:shadow-lg transition-colors"
          >
            📥 Télécharger Reçu
          </button>
          <button
            @click="resetForm"
            class="flex-1 px-4 py-2 rounded-lg bg-gray-300 text-gray-700 font-medium hover:bg-gray-400 transition-colors"
          >
            ← Nouveau Paiement
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  selectedChild: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['cancel', 'success'])

const form = ref({
  amount: 10000,
  phone: '',
  provider: 'campay',
  remember: false
})

const errors = ref({
  phone: '',
  general: ''
})

const isProcessing = ref(false)
const transaction = ref(null)

const providers = [
  { value: 'campay', label: 'Campay', name: 'Campay', icon: '📱' },
  { value: 'orange', label: 'Orange', name: 'Orange Money', icon: '🟠' },
  { value: 'mtn', label: 'MTN', name: 'MTN Mobile', icon: '🟡' }
]

const amount = computed(() => props.selectedChild?.amount || 50000)

const validatePhone = () => {
  const phone = form.value.phone.replace(/\D/g, '')
  const cameroonRegex = /^(237)?6\d{8}$/

  if (!cameroonRegex.test(phone)) {
    errors.value.phone = 'Numéro Camerounais invalide (format: 6XX XXX XXX)'
    return false
  }

  errors.value.phone = ''
  return true
}

const handlePayment = async () => {
  errors.value = { phone: '', general: '' }

  if (!validatePhone()) return

  isProcessing.value = true

  try {
    const response = await axios.post('/api/v1/parent/payments/initiate', {
      student_id: props.selectedChild.id,
      amount: form.value.amount,
      phone: form.value.phone,
      provider: form.value.provider
    })

    transaction.value = {
      status: 'pending',
      reference: response.data.transaction_ref,
      message: 'Veuillez confirmer le paiement sur votre téléphone...',
      timestamp: new Date()
    }

    // Poll for status
    setTimeout(() => checkPaymentStatus(response.data.payment_id), 2000)
  } catch (error) {
    errors.value.general = error.response?.data?.message || 'Erreur lors de l\'initiation du paiement'
    console.error('Payment error:', error)
  } finally {
    isProcessing.value = false
  }
}

const checkPaymentStatus = async (paymentId) => {
  try {
    const response = await axios.get(`/api/v1/parent/payments/status/${paymentId}`)

    if (response.data.status === 'completed') {
      transaction.value.status = 'success'
      transaction.value.message = 'Paiement reçu avec succès!'
      emit('success', response.data)
    } else if (response.data.status === 'pending') {
      // Poll again
      setTimeout(() => checkPaymentStatus(paymentId), 3000)
    } else {
      transaction.value.status = 'error'
      transaction.value.message = 'Le paiement a été rejeté ou annulé'
    }
  } catch (error) {
    console.error('Status check error:', error)
  }
}

const downloadReceipt = () => {
  // Implementation for receipt download
  alert('Téléchargement du reçu...')
}

const resetForm = () => {
  transaction.value = null
  form.value = {
    amount: props.selectedChild?.amount || 50000,
    phone: '',
    provider: 'campay',
    remember: false
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(20px);
  opacity: 0;
}

.font-monospace {
  font-family: 'Courier New', monospace;
}
</style>
