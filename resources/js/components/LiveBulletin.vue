<template>
  <div class="min-h-screen bg-gradient-to-br from-[#F5F7FA] to-[#E8ECEF]">
    <!-- Header -->
    <div class="sticky top-0 z-40 bg-white border-b-2 border-gray-200 shadow-md">
      <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex items-center justify-between mb-4">
          <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
            📚 Bulletin de Notes
            <span v-if="isSequenceLocked" class="text-sm bg-[#FF9500] text-white px-3 py-1 rounded-full">
              🔒 Verrouillé
            </span>
          </h1>
          <div class="flex gap-4">
            <button
              @click="exportGrades"
              :disabled="!currentBulletin || isExporting"
              class="px-4 py-2 rounded-lg bg-[#2E5BFF] text-white font-medium hover:shadow-lg transition-all duration-300 disabled:opacity-50 flex items-center gap-2"
            >
              <span v-if="isExporting">⏳</span>
              <span v-else>📥</span>
              {{ isExporting ? 'Export...' : 'Exporter' }}
            </button>
            <button
              @click="printBulletin"
              :disabled="!currentBulletin"
              class="px-4 py-2 rounded-lg bg-[#00C48C] text-white font-medium hover:shadow-lg transition-all duration-300 disabled:opacity-50 flex items-center gap-2"
            >
              🖨️ Imprimer
            </button>
          </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Student Selector with Quick-Find -->
          <div class="relative">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Chercher Étudiant:</label>
            <div class="relative">
              <input
                v-model="studentSearch"
                type="text"
                placeholder="Nom, prénom, ou ID..."
                class="w-full px-4 py-2 rounded-lg border-2 border-gray-300 focus:border-[#2E5BFF] focus:outline-none transition-colors"
                @focus="showStudentDropdown = true"
              />
              <span class="absolute right-3 top-2.5">🔍</span>

              <!-- Dropdown -->
              <div
                v-if="showStudentDropdown"
                class="absolute top-full left-0 right-0 mt-1 bg-white border-2 border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto z-50"
              >
                <div
                  v-for="student in filteredStudents"
                  :key="student.id"
                  @click="selectStudent(student)"
                  class="px-4 py-2 hover:bg-[#2E5BFF] hover:text-white cursor-pointer transition-colors border-b border-gray-100"
                >
                  <div class="font-medium">{{ student.name }}</div>
                  <div class="text-xs opacity-70">ID: {{ student.id }} | Classe: {{ student.classe }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sequence Selector -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Séquence:</label>
            <select
              v-model.number="selectedSequence"
              @change="loadBulletin"
              class="w-full px-4 py-2 rounded-lg border-2 border-gray-300 focus:border-[#2E5BFF] focus:outline-none transition-colors font-medium"
            >
              <option value="">-- Sélectionner --</option>
              <option value="1">Séquence 1</option>
              <option value="2">Séquence 2</option>
              <option value="3">Séquence 3</option>
            </select>
          </div>

          <!-- Academic Year -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Année Académique:</label>
            <input
              v-model.number="academicYear"
              type="number"
              @change="loadBulletin"
              class="w-full px-4 py-2 rounded-lg border-2 border-gray-300 focus:border-[#2E5BFF] focus:outline-none transition-colors font-medium"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div v-if="currentBulletin" class="max-w-7xl mx-auto px-6 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Bulletin Table -->
        <div class="lg:col-span-3">
          <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Student Info Header -->
            <div class="bg-gradient-to-r from-[#2E5BFF] to-[#4A7FFF] text-white p-6">
              <h2 class="text-2xl font-bold mb-2">{{ currentBulletin.student_name }}</h2>
              <p class="text-blue-100">
                Classe: <span class="font-semibold">{{ currentBulletin.classe }}</span> |
                Séquence: <span class="font-semibold">{{ selectedSequence }}</span> |
                Année: <span class="font-semibold">{{ academicYear }}</span>
              </p>
            </div>

            <!-- Grade Entry Table -->
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="bg-gray-100 border-b-2 border-gray-300">
                    <th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Matière</th>
                    <th class="px-4 py-3 text-center text-sm font-bold text-gray-700">Note Saisie</th>
                    <th class="px-4 py-3 text-center text-sm font-bold text-gray-700">Zone</th>
                    <th class="px-4 py-3 text-center text-sm font-bold text-gray-700">Note Précédente</th>
                    <th class="px-4 py-3 text-center text-sm font-bold text-gray-700">Rang</th>
                    <th class="px-4 py-3 text-center text-sm font-bold text-gray-700">Appréciation</th>
                    <th class="px-4 py-3 text-center text-sm font-bold text-gray-700">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(grade, idx) in currentBulletin.grades"
                    :key="grade.subject_id"
                    class="border-b border-gray-200 hover:bg-gray-50 transition-colors"
                  >
                    <!-- Subject -->
                    <td class="px-4 py-4 font-medium text-gray-800">
                      {{ grade.subject_name }}
                    </td>

                    <!-- Grade Input -->
                    <td class="px-4 py-4 text-center">
                      <input
                        v-model.number="editingGrades[grade.subject_id]"
                        type="number"
                        min="0"
                        max="20"
                        step="0.5"
                        :disabled="isSequenceLocked"
                        @input="() => updateGradeMetrics(grade.subject_id)"
                        class="w-20 px-2 py-2 rounded-lg border-2 border-gray-300 focus:border-[#2E5BFF] focus:outline-none text-center font-bold text-lg disabled:bg-gray-100 disabled:cursor-not-allowed"
                      />
                    </td>

                    <!-- Zone Indicator (0-5, 5-10, 10-14, 14-20) -->
                    <td class="px-4 py-4 text-center">
                      <span
                        v-if="getZoneColor(editingGrades[grade.subject_id])"
                        :class="`px-3 py-1 rounded-full font-semibold text-white text-sm ${getZoneColor(editingGrades[grade.subject_id])}`"
                      >
                        {{ getZoneLabel(editingGrades[grade.subject_id]) }}
                      </span>
                    </td>

                    <!-- Previous Grade -->
                    <td class="px-4 py-4 text-center font-medium">
                      <span v-if="grade.previous_grade !== null" class="text-gray-600">
                        {{ grade.previous_grade }}/20
                      </span>
                      <span v-else class="text-gray-400 italic">—</span>
                    </td>

                    <!-- Rank -->
                    <td class="px-4 py-4 text-center">
                      <span class="font-bold text-lg">
                        <span v-if="gradeMetrics[grade.subject_id]?.rank" class="text-[#2E5BFF]">
                          #{{ gradeMetrics[grade.subject_id].rank }}
                        </span>
                        <span v-else class="text-gray-400">—</span>
                      </span>
                      <div class="text-xs text-gray-500 mt-1">
                        <span v-if="gradeMetrics[grade.subject_id]?.percentile">
                          {{ gradeMetrics[grade.subject_id].percentile }}%
                        </span>
                      </div>
                    </td>

                    <!-- Appreciation -->
                    <td class="px-4 py-4 text-center">
                      <div
                        v-if="gradeMetrics[grade.subject_id]?.appreciation"
                        :style="{ backgroundColor: gradeMetrics[grade.subject_id].appreciation.color }"
                        class="px-2 py-1 rounded-lg text-white font-medium text-sm"
                      >
                        {{ gradeMetrics[grade.subject_id].appreciation.emoji }}
                        {{ gradeMetrics[grade.subject_id].appreciation.label }}
                      </div>
                      <span v-else class="text-gray-400">—</span>
                    </td>

                    <!-- Actions -->
                    <td class="px-4 py-4 text-center">
                      <button
                        v-if="editingGrades[grade.subject_id] !== grade.current_grade"
                        @click="saveGrade(grade, idx)"
                        :disabled="isSaving"
                        class="px-3 py-1 rounded-lg bg-[#00C48C] text-white font-medium text-sm hover:shadow-lg transition-all disabled:opacity-50"
                      >
                        <span v-if="isSaving">⏳</span>
                        <span v-else>💾</span>
                      </button>
                      <button
                        v-else
                        @click="cancelEdit(grade.subject_id)"
                        class="px-3 py-1 rounded-lg bg-gray-300 text-gray-700 font-medium text-sm hover:bg-gray-400 transition-colors"
                      >
                        ↺
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Save All Button -->
            <div class="bg-gray-50 px-6 py-4 border-t-2 border-gray-200 flex justify-between items-center">
              <div class="text-sm text-gray-600">
                <span v-if="modifiedCount > 0" class="font-semibold text-[#FF9500]">
                  {{ modifiedCount }} note(s) modifiée(s)
                </span>
              </div>
              <button
                @click="saveAllGrades"
                :disabled="modifiedCount === 0 || isSaving || isSequenceLocked"
                class="px-6 py-2 rounded-lg bg-gradient-to-r from-[#2E5BFF] to-[#4A7FFF] text-white font-bold hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ isSaving ? '⏳ Sauvegarde...' : '💾 Sauvegarder tout' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Statistics Sidebar -->
        <div class="lg:col-span-1">
          <!-- Overall Stats Card -->
          <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">📊 Statistiques</h3>

            <!-- Overall Average -->
            <div class="mb-6 p-4 rounded-lg bg-gradient-to-br from-[#2E5BFF] to-[#4A7FFF] text-white">
              <p class="text-sm opacity-90 mb-1">Moyenne Générale</p>
              <p class="text-3xl font-bold">
                {{ currentBulletin.overall_average?.toFixed(2) || '—' }}<span class="text-lg">/20</span>
              </p>
            </div>

            <!-- Overall Rank -->
            <div class="mb-6 p-4 rounded-lg bg-gradient-to-br from-[#00C48C] to-[#00A870] text-white">
              <p class="text-sm opacity-90 mb-1">Classement General</p>
              <p class="text-3xl font-bold">#{{ currentBulletin.overall_rank || '—' }}</p>
            </div>

            <!-- Critical Grades Alert -->
            <div v-if="criticalGrades.length > 0" class="mb-6 p-4 rounded-lg bg-[#FF3B30] bg-opacity-10 border-l-4 border-[#FF3B30]">
              <p class="text-sm font-bold text-[#FF3B30] mb-2">⚠️ Notes Critiques</p>
              <div v-for="grade in criticalGrades" :key="grade.subject_id" class="text-xs text-gray-700 mb-1">
                {{ grade.subject_name }}: {{ editingGrades[grade.subject_id] }}/20
              </div>
            </div>

            <!-- Distribution -->
            <div class="border-t-2 border-gray-200 pt-4">
              <p class="text-sm font-bold text-gray-700 mb-3">Distribution des Notes</p>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">0-5</span>
                  <div class="w-16 h-2 bg-[#FF3B30] rounded-full opacity-20"></div>
                  <span class="text-gray-700 font-bold">{{ distribution['0-5'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">5-10</span>
                  <div class="w-16 h-2 bg-[#FF9500] rounded-full opacity-20"></div>
                  <span class="text-gray-700 font-bold">{{ distribution['5-10'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">10-14</span>
                  <div class="w-16 h-2 bg-[#2E5BFF] rounded-full opacity-20"></div>
                  <span class="text-gray-700 font-bold">{{ distribution['10-14'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">14-20</span>
                  <div class="w-16 h-2 bg-[#00C48C] rounded-full opacity-20"></div>
                  <span class="text-gray-700 font-bold">{{ distribution['14-20'] }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Class Stats Card -->
          <div v-if="classStats" class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">📈 Stats Classe</h3>
            <div class="space-y-3 text-sm">
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Minimum</span>
                <span class="font-bold text-[#FF3B30]">{{ classStats.min }}/20</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Maximum</span>
                <span class="font-bold text-[#00C48C]">{{ classStats.max }}/20</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Moyenne</span>
                <span class="font-bold text-[#2E5BFF]">{{ classStats.average?.toFixed(2) }}/20</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Médiane</span>
                <span class="font-bold">{{ classStats.median?.toFixed(2) }}/20</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-else-if="isLoading" class="flex items-center justify-center min-h-[60vh]">
      <div class="text-center">
        <div class="text-4xl mb-4 animate-bounce">📚</div>
        <p class="text-gray-600 font-medium">Chargement du bulletin...</p>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="flex items-center justify-center min-h-[60vh]">
      <div class="text-center">
        <div class="text-6xl mb-4">📋</div>
        <p class="text-gray-600 font-medium">Sélectionnez un étudiant et une séquence pour commencer</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useGradesStore } from '@/stores/grades'
import axios from 'axios'

const gradesStore = useGradesStore()

// Refs
const studentSearch = ref('')
const selectedStudent = ref(null)
const selectedSequence = ref('')
const academicYear = ref(new Date().getFullYear())
const showStudentDropdown = ref(false)
const currentBulletin = ref(null)
const editingGrades = ref({})
const gradeMetrics = ref({})
const classStats = ref(null)
const students = ref([])
const isLoading = ref(false)
const isSaving = ref(false)
const isExporting = ref(false)
const distribution = ref({ '0-5': 0, '5-10': 0, '10-14': 0, '14-20': 0 })

// Computed
const filteredStudents = computed(() => {
  if (!studentSearch.value) return students.value
  const query = studentSearch.value.toLowerCase()
  return students.value.filter(s =>
    s.name.toLowerCase().includes(query) ||
    s.id.toString().includes(query)
  )
})

const modifiedCount = computed(() => {
  if (!currentBulletin.value) return 0
  return currentBulletin.value.grades.filter(
    g => editingGrades.value[g.subject_id] !== g.current_grade
  ).length
})

const criticalGrades = computed(() => {
  if (!currentBulletin.value) return []
  return currentBulletin.value.grades.filter(
    g => (editingGrades.value[g.subject_id] || 0) < 7
  )
})

const isSequenceLocked = computed(() => {
  return currentBulletin.value?.is_locked === true
})

// Methods
const selectStudent = (student) => {
  selectedStudent.value = student
  studentSearch.value = student.name
  showStudentDropdown.value = false
  loadBulletin()
}

const loadBulletin = async () => {
  if (!selectedStudent.value || !selectedSequence.value) return

  isLoading.value = true
  try {
    // Get bulletin
    const bulletinResponse = await axios.get('/api/v1/teacher/grades/bulletin', {
      params: {
        student_id: selectedStudent.value.id,
        sequence: selectedSequence.value,
        academic_year: academicYear.value
      }
    })

    currentBulletin.value = bulletinResponse.data

    // Initialize editing grades
    editingGrades.value = {}
    gradeMetrics.value = {}
    currentBulletin.value.grades.forEach(grade => {
      editingGrades.value[grade.subject_id] = grade.current_grade
      updateGradeMetrics(grade.subject_id)
    })

    // Get class stats
    const statsResponse = await axios.get('/api/v1/teacher/grades/class-stats', {
      params: {
        class_id: selectedStudent.value.classe_id,
        sequence: selectedSequence.value,
        academic_year: academicYear.value
      }
    })

    classStats.value = statsResponse.data
    calculateDistribution()
  } catch (error) {
    console.error('Error loading bulletin:', error)
  } finally {
    isLoading.value = false
  }
}

const updateGradeMetrics = (subjectId) => {
  const grade = editingGrades.value[subjectId]
  if (grade === null || grade === undefined) {
    gradeMetrics.value[subjectId] = {}
    return
  }

  // Calculate appreciation
  const appreciation = getAppreciation(grade)

  // Simulate rank calculation (would come from API)
  const rank = Math.floor(Math.random() * 35) + 1 // Demo rank

  gradeMetrics.value[subjectId] = {
    appreciation,
    rank,
    percentile: Math.floor((35 - rank) / 35 * 100)
  }
}

const getAppreciation = (grade) => {
  if (grade === null || grade === undefined) return null

  const appreciations = {
    'critical': { emoji: '😟', label: 'Travail insuffisant', color: '#FF3B30' },
    'poor': { emoji: '🤔', label: 'Passable', color: '#FF9500' },
    'good': { emoji: '😊', label: 'Bien', color: '#2E5BFF' },
    'very-good': { emoji: '😃', label: 'Très bien', color: '#00C48C' },
    'excellent': { emoji: '🎉', label: 'Excellent', color: '#FFD700' }
  }

  if (grade < 7) return appreciations.critical
  if (grade < 10) return appreciations.poor
  if (grade < 14) return appreciations.good
  if (grade < 16) return appreciations['very-good']
  return appreciations.excellent
}

const getZoneColor = (grade) => {
  if (grade === null || grade === undefined) return null
  if (grade < 5) return 'bg-[#FF3B30]'
  if (grade < 10) return 'bg-[#FF9500]'
  if (grade < 14) return 'bg-[#2E5BFF]'
  return 'bg-[#00C48C]'
}

const getZoneLabel = (grade) => {
  if (grade === null || grade === undefined) return '—'
  if (grade < 5) return 'Critique'
  if (grade < 10) return 'Faible'
  if (grade < 14) return 'Moyen'
  return 'Excellent'
}

const saveGrade = async (grade, index) => {
  isSaving.value = true
  try {
    await axios.post('/api/v1/teacher/grades/save', {
      student_id: selectedStudent.value.id,
      subject_id: grade.subject_id,
      grade: editingGrades.value[grade.subject_id],
      sequence: selectedSequence.value,
      academic_year: academicYear.value
    })

    // Update current grade
    currentBulletin.value.grades[index].current_grade = editingGrades.value[grade.subject_id]
    calculateDistribution()
  } catch (error) {
    console.error('Error saving grade:', error)
    alert('Erreur lors de la sauvegarde. Veuillez réessayer.')
  } finally {
    isSaving.value = false
  }
}

const saveAllGrades = async () => {
  isSaving.value = true
  try {
    const gradesToSave = currentBulletin.value.grades
      .filter(g => editingGrades.value[g.subject_id] !== g.current_grade)
      .map(g => ({
        subject_id: g.subject_id,
        grade: editingGrades.value[g.subject_id]
      }))

    if (gradesToSave.length === 0) return

    await axios.post('/api/v1/teacher/grades/save-bulk', {
      student_id: selectedStudent.value.id,
      grades: gradesToSave,
      sequence: selectedSequence.value,
      academic_year: academicYear.value
    })

    // Update all grades
    currentBulletin.value.grades.forEach(g => {
      g.current_grade = editingGrades.value[g.subject_id]
    })

    calculateDistribution()
    alert('✅ Toutes les notes ont été sauvegardées avec succès!')
  } catch (error) {
    console.error('Error saving grades:', error)
    alert('Erreur lors de la sauvegarde. Veuillez réessayer.')
  } finally {
    isSaving.value = false
  }
}

const cancelEdit = (subjectId) => {
  const grade = currentBulletin.value.grades.find(g => g.subject_id === subjectId)
  if (grade) {
    editingGrades.value[subjectId] = grade.current_grade
    updateGradeMetrics(subjectId)
  }
}

const calculateDistribution = () => {
  const grades = currentBulletin.value.grades.map(g => editingGrades.value[g.subject_id])
  distribution.value = {
    '0-5': grades.filter(g => g >= 0 && g < 5).length,
    '5-10': grades.filter(g => g >= 5 && g < 10).length,
    '10-14': grades.filter(g => g >= 10 && g < 14).length,
    '14-20': grades.filter(g => g >= 14 && g <= 20).length
  }
}

const exportGrades = async () => {
  isExporting.value = true
  try {
    const response = await axios.get('/api/v1/teacher/grades/export', {
      params: {
        student_id: selectedStudent.value.id,
        sequence: selectedSequence.value,
        academic_year: academicYear.value,
        format: 'csv'
      },
      responseType: 'blob'
    })

    const url = window.URL.createObjectURL(response.data)
    const a = document.createElement('a')
    a.href = url
    a.download = `bulletin_${selectedStudent.value.id}_seq${selectedSequence.value}.csv`
    a.click()
  } catch (error) {
    console.error('Error exporting:', error)
    alert('Erreur lors de l\'exportation.')
  } finally {
    isExporting.value = false
  }
}

const printBulletin = () => {
  window.print()
}

// Lifecycle
onMounted(async () => {
  isLoading.value = true
  try {
    // Load students
    const response = await axios.get('/api/v1/teacher/my-students')
    students.value = response.data.students || []
  } catch (error) {
    console.error('Error loading students:', error)
  } finally {
    isLoading.value = false
  }
})
</script>

<style scoped>
@media print {
  .sticky,
  button,
  input[type="number"],
  select {
    display: none !important;
  }

  body {
    background: white !important;
  }

  .bg-white,
  .rounded-xl {
    box-shadow: none !important;
    border: 1px solid #ccc;
  }
}
</style>
