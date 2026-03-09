import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

/**
 * Grade Store (Pinia)
 * 
 * Manages grade data and operations
 * Powers the Live Bulletin feature
 */
export const useGradeStore = defineStore('grades', () => {
    // State
    const bulletin = ref(null);
    const currentStudent = ref(null);
    const classStats = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const sequence = ref(1);
    const academicYear = ref(new Date().getFullYear());

    // Computed
    const overallAverage = computed(() => bulletin.value?.overall_average ?? null);
    const overallRank = computed(() => bulletin.value?.overall_rank ?? null);
    const isLocked = computed(() => {
        return bulletin.value?.bulletin?.some(item => item.is_locked) || false;
    });

    /**
     * Get bulletin for a specific student
     * 
     * @param {int} studentId
     * @param {int} seq
     * @returns {Promise}
     */
    const getBulletin = async (studentId, seq = sequence.value) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get(
                `/api/v1/teacher/grades/bulletin/${studentId}/${seq}`
            );

            if (response.data.success) {
                bulletin.value = response.data;
                currentStudent.value = response.data.student;
                return response.data;
            }

            throw new Error('Failed to load bulletin');
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Save a grade
     * 🔥 Core operation for Live Bulletin
     * 
     * @param {object} gradeData
     * @returns {Promise}
     */
    const saveGrade = async (gradeData) => {
        error.value = null;

        try {
            const response = await axios.post('/api/v1/teacher/grades/save', {
                student_id: gradeData.student_id,
                subject_id: gradeData.subject_id,
                grade: gradeData.grade,
                sequence: gradeData.sequence || sequence.value,
                academic_year: gradeData.academic_year || academicYear.value,
                appreciation: gradeData.appreciation,
                comments: gradeData.comments,
            });

            if (response.data.success) {
                // Update local bulletin data
                if (bulletin.value) {
                    const subjectIndex = bulletin.value.bulletin.findIndex(
                        b => b.subject_id === gradeData.subject_id
                    );

                    if (subjectIndex !== -1) {
                        bulletin.value.bulletin[subjectIndex].grade = response.data.grade.score;
                        bulletin.value.bulletin[subjectIndex].rank = response.data.rank;
                        bulletin.value.bulletin[subjectIndex].appreciation = response.data.appreciation;
                    }
                }

                return response.data;
            }

            throw new Error(response.data.message || 'Failed to save grade');
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            throw err;
        }
    };

    /**
     * Get class statistics
     * Used for sidebar display
     * 
     * @param {int} classId
     * @param {int} subjectId Optional
     * @returns {Promise}
     */
    const getClassStatistics = async (classId, subjectId = null) => {
        loading.value = true;
        error.value = null;

        try {
            const params = { sequence: sequence.value };
            if (subjectId) params.subject_id = subjectId;

            const response = await axios.get(
                `/api/v1/teacher/grades/class-stats/${classId}`,
                { params }
            );

            if (response.data.success) {
                classStats.value = response.data;
                return response.data;
            }

            throw new Error('Failed to load statistics');
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Export grades
     * 
     * @param {int} classId
     * @param {string} format 'csv' or 'excel'
     * @returns {Promise}
     */
    const exportGrades = async (classId, format = 'csv') => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get(
                `/api/v1/teacher/grades/export/${classId}`,
                {
                    params: { format },
                    responseType: 'blob',
                }
            );

            // Create download link
            const url = window.URL.createObjectURL(response.data);
            const link = document.createElement('a');
            link.href = url;
            link.download = `grades_${classId}_seq${sequence.value}.${format === 'excel' ? 'xlsx' : 'csv'}`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);

            return true;
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Set current sequence
     * 
     * @param {int} seq
     */
    const setSequence = (seq) => {
        sequence.value = seq;
    };

    /**
     * Set academic year
     * 
     * @param {int} year
     */
    const setAcademicYear = (year) => {
        academicYear.value = year;
    };

    /**
     * Clear bulletin data
     */
    const clearBulletin = () => {
        bulletin.value = null;
        currentStudent.value = null;
    };

    return {
        // State
        bulletin,
        currentStudent,
        classStats,
        loading,
        error,
        sequence,
        academicYear,

        // Computed
        overallAverage,
        overallRank,
        isLocked,

        // Methods
        getBulletin,
        saveGrade,
        getClassStatistics,
        exportGrades,
        setSequence,
        setAcademicYear,
        clearBulletin,
    };
});
