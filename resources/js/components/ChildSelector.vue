<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-bold text-gray-800">👶 Sélectionner un Enfant</h3>
      <button
        v-if="editMode"
        @click="editMode = false"
        class="text-sm px-3 py-1 rounded-lg bg-[#2E5BFF] text-white font-medium"
      >
        ✓ Fermer
      </button>
    </div>

    <!-- Children Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="child in children"
        :key="child.id"
        @click="selectChild(child)"
        :class="[
          'p-6 rounded-xl cursor-pointer transition-all duration-300 border-2',
          selectedChild?.id === child.id
            ? 'bg-gradient-to-br from-[#2E5BFF] to-[#4A7FFF] text-white border-transparent shadow-lg scale-105'
            : 'bg-white text-gray-800 border-gray-200 hover:border-[#2E5BFF] hover:shadow-lg'
        ]"
      >
        <!-- Child Avatar -->
        <div class="flex items-center gap-4 mb-4">
          <div
            :class="[
              'w-12 h-12 rounded-full flex items-center justify-center font-bold text-xl',
              selectedChild?.id === child.id ? 'bg-white/20' : 'bg-[#2E5BFF] text-white'
            ]"
          >
            {{ child.name.charAt(0) }}
          </div>
          <div>
            <h4 class="font-bold text-lg">{{ child.name }}</h4>
            <p :class="['text-sm', selectedChild?.id === child.id ? 'opacity-90' : 'text-gray-600']">
              Classe: {{ child.classe }}
            </p>
          </div>
        </div>

        <!-- Child Info -->
        <div :class="['text-sm space-y-1', selectedChild?.id === child.id ? '' : 'text-gray-600']">
          <div class="flex justify-between">
            <span>ID Étudiant:</span>
            <span class="font-semibold">{{ child.student_id }}</span>
          </div>
          <div class="flex justify-between">
            <span>Sexe:</span>
            <span class="font-semibold">{{ child.gender === 'M' ? '👨' : '👩' }} {{ child.gender === 'M' ? 'Garçon' : 'Fille' }}</span>
          </div>
          <div class="flex justify-between">
            <span>Status:</span>
            <span
              :class="[
                'font-semibold px-2 py-1 rounded text-xs',
                child.financial_status === 'paid'
                  ? 'bg-green-100 text-[#00C48C]'
                  : 'bg-orange-100 text-[#FF9500]'
              ]"
            >
              {{ child.financial_status === 'paid' ? '✓ Payé' : '⚠️ Non Payé' }}
            </span>
          </div>
        </div>

        <!-- Selected Indicator -->
        <div
          v-if="selectedChild?.id === child.id"
          class="absolute top-2 right-2 w-6 h-6 bg-white rounded-full flex items-center justify-center text-[#2E5BFF] font-bold"
        >
          ✓
        </div>
      </div>

      <!-- Add Child Button -->
      <button
        v-if="editMode"
        @click="showAddForm = true"
        class="p-6 rounded-xl border-2 border-dashed border-gray-300 text-gray-600 hover:border-[#2E5BFF] hover:text-[#2E5BFF] transition-colors flex items-center justify-center gap-2 font-medium"
      >
        <span class="text-3xl">+</span> Ajouter un Enfant
      </button>
    </div>

    <!-- Add Child Form -->
    <transition name="fade">
      <div v-if="showAddForm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-8 max-w-md w-full">
          <h4 class="text-xl font-bold text-gray-800 mb-4">Ajouter un Enfant</h4>
          <form @submit.prevent="addChild" class="space-y-4">
            <input
              v-model="newChild.name"
              type="text"
              placeholder="Nom complet"
              class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#2E5BFF] focus:outline-none"
              required
            />
            <input
              v-model="newChild.student_id"
              type="text"
              placeholder="ID Étudiant"
              class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#2E5BFF] focus:outline-none"
              required
            />
            <select
              v-model="newChild.gender"
              class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:border-[#2E5BFF] focus:outline-none"
            >
              <option value="M">Garçon</option>
              <option value="F">Fille</option>
            </select>
            <div class="flex gap-2">
              <button
                type="submit"
                class="flex-1 px-4 py-2 rounded-lg bg-[#00C48C] text-white font-medium hover:shadow-lg transition-colors"
              >
                Ajouter
              </button>
              <button
                type="button"
                @click="showAddForm = false"
                class="flex-1 px-4 py-2 rounded-lg bg-gray-300 text-gray-700 font-medium hover:bg-gray-400 transition-colors"
              >
                Annuler
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Selected Child Display -->
    <div v-if="selectedChild" class="bg-gradient-to-r from-[#2E5BFF] to-[#4A7FFF] text-white rounded-xl p-6">
      <p class="text-sm opacity-90 mb-1">Actuellement en train de visualiser</p>
      <h3 class="text-2xl font-bold">{{ selectedChild.name }}</h3>
      <p class="text-blue-100 mt-2">Classe: {{ selectedChild.classe }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  children: {
    type: Array,
    required: true
  },
  modelValue: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:modelValue'])

const editMode = ref(false)
const showAddForm = ref(false)
const newChild = ref({
  name: '',
  student_id: '',
  gender: 'M'
})

const selectedChild = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const selectChild = (child) => {
  selectedChild.value = child
}

const addChild = async () => {
  // API call would go here
  const newChildData = {
    ...newChild.value,
    id: Date.now(),
    classe: 'À déterminer',
    financial_status: 'unpaid'
  }

  emit('child-added', newChildData)
  showAddForm.value = false
  newChild.value = { name: '', student_id: '', gender: 'M' }
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
</style>
