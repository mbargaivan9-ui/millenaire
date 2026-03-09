<template>
  <div :class="['rounded-xl p-6 shadow-lg transition-all duration-300 hover:shadow-xl', bgColor]">
    <!-- Icon -->
    <div class="flex items-center justify-between mb-4">
      <span class="text-4xl">{{ icon }}</span>
      <span v-if="trend" :class="['px-3 py-1 rounded-full text-xs font-bold', trendColor]">
        {{ trend > 0 ? '📈' : '📉' }} {{ Math.abs(trend) }}%
      </span>
    </div>

    <!-- Title and Value -->
    <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ title }}</h3>
    <p class="text-3xl font-bold text-gray-800 mb-3">
      {{ formattedValue }}
    </p>

    <!-- Subtitle -->
    <p v-if="subtitle" class="text-sm text-gray-600 mb-4">{{ subtitle }}</p>

    <!-- Progress Bar -->
    <div v-if="percentage !== undefined" class="mb-4">
      <div class="flex justify-between items-center mb-2">
        <span class="text-xs font-semibold text-gray-700">Progression</span>
        <span class="text-xs text-gray-600">{{ percentage }}%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div
          :style="{ width: percentage + '%' }"
          :class="['h-2 rounded-full transition-all duration-500', progressColor]"
        ></div>
      </div>
    </div>

    <!-- Footer Stats -->
    <div v-if="footerStats" class="pt-4 border-t border-gray-200 grid grid-cols-2 gap-2">
      <div v-for="stat in footerStats" :key="stat.label" class="text-center">
        <p class="text-2xl font-bold text-gray-800">{{ stat.value }}</p>
        <p class="text-xs text-gray-600">{{ stat.label }}</p>
      </div>
    </div>

    <!-- Action Button -->
    <button
      v-if="action"
      @click="$emit('action')"
      :class="['w-full mt-4 px-4 py-2 rounded-lg font-medium transition-all duration-300', actionButtonColor]"
    >
      {{ action }}
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  icon: {
    type: String,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  value: {
    type: [Number, String],
    required: true
  },
  subtitle: {
    type: String,
    default: null
  },
  trend: {
    type: Number,
    default: null
  },
  percentage: {
    type: Number,
    default: undefined
  },
  color: {
    type: String,
    default: 'blue',
    validator: (val) => ['blue', 'green', 'orange', 'red', 'purple'].includes(val)
  },
  format: {
    type: String,
    default: 'text',
    validator: (val) => ['text', 'currency', 'percentage', 'number'].includes(val)
  },
  footerStats: {
    type: Array,
    default: null
  },
  action: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['action'])

const bgColor = computed(() => {
  const colors = {
    blue: 'bg-gradient-to-br from-[#2E5BFF] to-[#4A7FFF] text-white',
    green: 'bg-gradient-to-br from-[#00C48C] to-[#00A870] text-white',
    orange: 'bg-gradient-to-br from-[#FF9500] to-[#E67E22] text-white',
    red: 'bg-gradient-to-br from-[#FF3B30] to-[#E74C3C] text-white',
    purple: 'bg-gradient-to-br from-[#9B59B6] to-[#8E44AD] text-white'
  }
  return colors[props.color]
})

const trendColor = computed(() => {
  if (!props.trend) return ''
  const baseColor = props.color
  if (baseColor === 'blue' || baseColor === 'green') {
    return props.trend > 0 ? 'bg-[#00C48C] text-white' : 'bg-[#FF3B30] text-white'
  }
  return 'bg-white/30 text-white'
})

const progressColor = computed(() => {
  const colors = {
    blue: 'bg-[#2E5BFF]',
    green: 'bg-[#00C48C]',
    orange: 'bg-[#FF9500]',
    red: 'bg-[#FF3B30]',
    purple: 'bg-[#9B59B6]'
  }
  return colors[props.color]
})

const actionButtonColor = computed(() => {
  const baseColor = props.color
  const colors = {
    blue: 'bg-[#2E5BFF] text-white hover:bg-[#1E4BB4]',
    green: 'bg-[#00C48C] text-white hover:bg-[#00A870]',
    orange: 'bg-[#FF9500] text-white hover:bg-[#E67E22]',
    red: 'bg-[#FF3B30] text-white hover:bg-[#E74C3C]',
    purple: 'bg-[#9B59B6] text-white hover:bg-[#8E44AD]'
  }
  return colors[baseColor]
})

const formattedValue = computed(() => {
  const value = props.value
  switch (props.format) {
    case 'currency':
      return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XAF',
        minimumFractionDigits: 0
      }).format(value)
    case 'percentage':
      return `${value}%`
    case 'number':
      return new Intl.NumberFormat('fr-FR').format(value)
    default:
      return value
  }
})
</script>

<style scoped>
/* Smooth animations */
:deep(.animated) {
  animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
