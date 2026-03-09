@extends('layouts.app')

@section('title', __('Grade Entry - ') . $subject->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900">{{ __('Grade Entry') }}</h1>
                    <p class="text-lg text-gray-600 mt-2">
                        {{ $classe->name }} • {{ $subject->name }} • {{ __('Sequence') }} {{ $currentSequence }} • {{ __('Term') }} {{ $currentTerm }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('teacher.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        {{ __('Back') }}
                    </a>
                    <button id="exportBtn" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        {{ __('Export CSV') }}
                    </button>
                </div>
            </div>

            <!-- Session Info -->
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-gray-600 text-sm">{{ __('Total Students') }}</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $students->count() }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-gray-600 text-sm">{{ __('Grades Entered') }}</div>
                    <div class="text-3xl font-bold text-blue-600" id="gradesEntered">0</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-gray-600 text-sm">{{ __('Class Average') }}</div>
                    <div class="text-3xl font-bold text-indigo-600" id="classAverage">-</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-gray-600 text-sm">{{ __('Pass Rate') }}</div>
                    <div class="text-3xl font-bold text-green-600" id="passRate">-</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Main Content Area -->
            <div class="lg:col-span-3">
                <!-- Quick-Find Search -->
                <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" id="quickFind" placeholder="{{ __('Quick-Find: Search by name, email or matricule...') }}"
                               class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div id="searchResults" class="mt-3 hidden max-h-48 overflow-y-auto border border-gray-200 rounded-lg">
                        <!-- Results populated by JS -->
                    </div>
                </div>

                <!-- Living Bulletin View -->
                @if ($template && $fieldZone)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 p-4 border-b border-gray-200">
                            {{ __('Living Bulletin') }}
                        </h3>
                        
                        <div class="p-4">
                            <div class="relative inline-block w-full bg-gray-100 rounded-lg overflow-hidden" style="max-height: 600px;">
                                <!-- Bulletin Background Image -->
                                <img src="{{ asset('storage/' . $template->template_image_path) }}"
                                     alt="{{ $template->name }}"
                                     class="w-full h-auto opacity-60"
                                     id="bulletinImage">

                                <!-- Overlay Input Fields -->
                                <div class="absolute top-0 left-0 w-full h-full" style="pointer-events: none;">
                                    <div id="inputOverlay" style="position: relative; width: 100%; height: 100%;">
                                        <!-- Inputs will be positioned here via JS -->
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">{{ __('Click on student name below and grades will auto-populate here') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Student List - Tabular View -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Matricule') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student Name') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Seq') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Score') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Avg') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="studentsList">
                                @forelse ($students as $student)
                                    <tr class="hover:bg-blue-50 transition student-row" data-student-id="{{ $student['id'] }}"
                                        data-matricule="{{ $student['matricule'] }}" data-name="{{ $student['name'] }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $student['matricule'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $student['name'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $student['email'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <select class="sequence-select border border-gray-300 rounded px-2 py-1 text-sm">
                                                <option value="1" {{ $currentSequence === 1 ? 'selected' : '' }}>1</option>
                                                <option value="2" {{ $currentSequence === 2 ? 'selected' : '' }}>2</option>
                                                <option value="3" {{ $currentSequence === 3 ? 'selected' : '' }}>3</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" min="0" max="20" step="0.5"
                                                   class="score-input border border-gray-300 rounded px-2 py-1 text-sm w-16 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   placeholder="0-20">
                                            <div class="save-feedback text-green-600 text-xs font-bold hidden mt-1">✓ {{ __('Saved') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="student-average text-sm font-semibold text-gray-700">-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="student-status px-2 py-1 text-xs font-semibold rounded text-white bg-gray-400">
                                                -
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <button class="btn-details text-blue-600 hover:text-blue-900 font-semibold">
                                                {{ __('Details') }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                            {{ __('No students in this class') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar - Class Statistics & Details -->
            <div class="lg:col-span-1 space-y-4">
                <!-- Statistics Card -->
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Class Statistics') }}</h3>
                    
                    <div id="statsPanel" class="space-y-3">
                        <div class="flex justify-between items-center p-3 bg-blue-50 rounded">
                            <span class="text-gray-700">{{ __('Highest') }}</span>
                            <span class="text-lg font-bold text-blue-600" id="stat-highest">-</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-red-50 rounded">
                            <span class="text-gray-700">{{ __('Lowest') }}</span>
                            <span class="text-lg font-bold text-red-600" id="stat-lowest">-</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-purple-50 rounded">
                            <span class="text-gray-700">{{ __('Average') }}</span>
                            <span class="text-lg font-bold text-purple-600" id="stat-average">-</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-green-50 rounded">
                            <span class="text-gray-700">{{ __('Passing') }}</span>
                            <span class="text-lg font-bold text-green-600" id="stat-passing">-</span>
                        </div>
                    </div>
                </div>

                <!-- Selected Student Details -->
                <div class="bg-white rounded-lg shadow-md p-4 hidden" id="detailsPanel">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Student Details') }}</h3>
                    
                    <div id="studentDetails" class="space-y-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600">{{ __('Name') }}</label>
                            <p id="detail-name" class="text-gray-900">-</p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600">{{ __('Matricule') }}</label>
                            <p id="detail-matricule" class="text-gray-900">-</p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600">{{ __('Fin. Status') }}</label>
                            <p id="detail-status" class="text-gray-900">-</p>
                        </div>
                        <div class="border-t pt-3 mt-3">
                            <label class="text-xs font-semibold text-gray-600">{{ __('Subject Average') }}</label>
                            <p id="detail-average" class="text-2xl font-bold text-indigo-600">-</p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600">{{ __('Performance') }}</label>
                            <p id="detail-performance" class="text-sm">-</p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600">{{ __('Rank') }}</label>
                            <p id="detail-rank" class="text-lg font-bold text-blue-600">-</p>
                        </div>
                    </div>
                </div>

                <!-- Filter Controls -->
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Filters') }}</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Term') }}</label>
                            <select id="filterTerm" class="w-full border border-gray-300 rounded px-2 py-2">
                                <option value="1" {{ $currentTerm === 1 ? 'selected' : '' }}>{{ __('Term 1') }}</option>
                                <option value="2" {{ $currentTerm === 2 ? 'selected' : '' }}>{{ __('Term 2') }}</option>
                                <option value="3" {{ $currentTerm === 3 ? 'selected' : '' }}>{{ __('Term 3') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Sequence') }}</label>
                            <select id="filterSequence" class="w-full border border-gray-300 rounded px-2 py-2">
                                <option value="1" {{ $currentSequence === 1 ? 'selected' : '' }}>{{ __('Seq 1') }}</option>
                                <option value="2" {{ $currentSequence === 2 ? 'selected' : '' }}>{{ __('Seq 2') }}</option>
                                <option value="3" {{ $currentSequence === 3 ? 'selected' : '' }}>{{ __('Seq 3') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('Academic Year') }}</label>
                            <select id="filterYear" class="w-full border border-gray-300 rounded px-2 py-2">
                                <option value="{{ now()->year }}" selected>{{ now()->year }}</option>
                                <option value="{{ now()->year - 1 }}">{{ now()->year - 1 }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden meta data for JavaScript -->
<script>
    window.appData = {
        classSubjectTeacherId: {{ $classSubjectTeacher->id }},
        classeId: {{ $classe->id }},
        subjectId: {{ $subject->id }},
        currentTerm: {{ $currentTerm }},
        currentSequence: {{ $currentSequence }},
        academicYear: {{ $academicYear }},
        template: @json($template),
        fieldZone: @json($fieldZone),
        students: @json($students),
        apiBase: '{{ route("teacher.grades.entry.index", $classSubjectTeacher) }}',
        translations: {
            saving: '{{ __("Saving...") }}',
            saved: '{{ __("Saved") }}',
            error: '{{ __("Error") }}',
            confirmed: '{{ __("Confirmed") }}',
        }
    };
</script>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeGradeEntry();
});

function initializeGradeEntry() {
    const data = window.appData;
    let currentStudent = null;

    // Quick-Find functionality
    document.getElementById('quickFind').addEventListener('input', debounce(function(e) {
        const query = e.target.value;
        if (!query) {
            document.getElementById('searchResults').classList.add('hidden');
            return;
        }

        axios.get(`/teacher/grades/${data.classSubjectTeacherId}/search`, {
            params: { q: query }
        })
        .then(res => {
            const results = res.data.students;
            const resultsDiv = document.getElementById('searchResults');
            
            if (results.length === 0) {
                resultsDiv.innerHTML = '<div class="p-4 text-gray-500">{{ __("No students found") }}</div>';
                resultsDiv.classList.remove('hidden');
                return;
            }

            resultsDiv.innerHTML = results.map(s => `
                <div class="p-2 border-b hover:bg-blue-50 cursor-pointer search-result" data-id="${s.id}">
                    <strong>${s.matricule}</strong> - ${s.name}
                    <div class="text-xs text-gray-500">${s.email}</div>
                </div>
            `).join('');

            resultsDiv.classList.remove('hidden');

            // Add click handlers
            resultsDiv.querySelectorAll('.search-result').forEach(el => {
                el.addEventListener('click', function() {
                    const studentId = this.dataset.id;
                    selectStudent(studentId);
                    document.getElementById('searchResults').classList.add('hidden');
                    document.getElementById('quickFind').value = '';
                });
            });
        })
        .catch(err => console.error('Search error:', err));
    }, 300));

    // Student row click
    document.querySelectorAll('.student-row').forEach(row => {
        row.addEventListener('click', function() {
            const studentId = this.dataset.studentId;
            selectStudent(studentId);
        });
    });

    // Select student and load details
    function selectStudent(studentId) {
        currentStudent = studentId;

        // Remove previous highlight
        document.querySelectorAll('.student-row').forEach(r => r.classList.remove('bg-blue-100'));
        
        // Highlight current
        const row = document.querySelector(`[data-student-id="${studentId}"]`);
        row.classList.add('bg-blue-100');

        // Load student details
        loadStudentDetails(studentId);

        // Scroll to row
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function loadStudentDetails(studentId) {
        axios.get(`/teacher/grades/${data.classSubjectTeacherId}/student/${studentId}`, {
            params: {
                term: data.currentTerm,
                year: data.academicYear
            }
        })
        .then(res => {
            const student = res.data;
            const row = document.querySelector(`[data-student-id="${studentId}"]`);

            // Update row display
            row.querySelector('.student-average').textContent = student.average.toFixed(2);
            
            const status = student.passing ? 'Pass' : 'Fail';
            const statusColor = student.passing ? 'bg-green-600' : 'bg-red-600';
            row.querySelector('.student-status').textContent = status;
            row.querySelector('.student-status').className = `student-status px-2 py-1 text-xs font-semibold rounded text-white ${statusColor}`;

            // Update sidebar
            document.getElementById('detail-name').textContent = student.student_name;
            document.getElementById('detail-matricule').textContent = student.matricule;
            document.getElementById('detail-average').textContent = student.average.toFixed(2);
            document.getElementById('detail-performance').textContent = student.performance_level;
            document.getElementById('detail-status').textContent = student.student_status || 'N/A';

            // Get rank
            getStudentRank(studentId);

            // Show details panel
            document.getElementById('detailsPanel').classList.remove('hidden');
        })
        .catch(err => console.error('Load student error:', err));
    }

    function getStudentRank(studentId) {
        axios.get(`/teacher/grades/${data.classSubjectTeacherId}/class-stats`, {
            params: {
                term: data.currentTerm,
                year: data.academicYear
            }
        })
        .then(res => {
            const stats = res.data.stats;
            // Rank calculation would be done server-side, show if available
            document.getElementById('detail-rank').textContent = '...';
        });
    }

    // Score input - Save on blur
    document.querySelectorAll('.score-input').forEach(input => {
        input.addEventListener('blur', function() {
            const row = this.closest('.student-row');
            const studentId = row.dataset.studentId;
            const score = this.value;
            const sequence = row.querySelector('.sequence-select').value;
            const feedback = row.querySelector('.save-feedback');

            if (!score) return;

            saveGrade(studentId, score, sequence, feedback);
        });
    });

    function saveGrade(studentId, score, sequence, feedbackEl) {
        feedbackEl.classList.remove('hidden', 'text-red-600');
        feedbackEl.classList.add('text-gray-500');
        feedbackEl.textContent = '{{ __("Saving...") }}';

        axios.post(`/teacher/grades/${data.classSubjectTeacherId}/save`, {
            student_id: studentId,
            score: parseFloat(score),
            sequence: parseInt(sequence),
            term: data.currentTerm,
            academic_year: data.academicYear
        })
        .then(res => {
            feedbackEl.textContent = '✓ {{ __("Saved") }}';
            feedbackEl.classList.remove('text-gray-500');
            feedbackEl.classList.add('text-green-600');

            // Update student details if this student is selected
            if (currentStudent == studentId) {
                loadStudentDetails(studentId);
            }

            // Update class statistics
            loadClassStatistics();

            setTimeout(() => {
                feedbackEl.classList.add('hidden');
            }, 2000);
        })
        .catch(err => {
            feedbackEl.innerHTML = '✗ {{ __("Error") }}';
            feedbackEl.classList.add('text-red-600');
            console.error('Save error:', err);
        });
    }

    // Load class statistics
    function loadClassStatistics() {
        axios.get(`/teacher/grades/${data.classSubjectTeacherId}/class-stats`, {
            params: {
                term: data.currentTerm,
                year: data.academicYear
            }
        })
        .then(res => {
            const stats = res.data.stats;
            document.getElementById('stat-highest').textContent = stats.highest_average?.toFixed(2) || '-';
            document.getElementById('stat-lowest').textContent = stats.lowest_average?.toFixed(2) || '-';
            document.getElementById('stat-average').textContent = stats.average_class?.toFixed(2) || '-';
            document.getElementById('classAverage').textContent = stats.average_class?.toFixed(2) || '-';
            document.getElementById('passRate').textContent = stats.passing_rate?.toFixed(1) + '%' || '-';
            document.getElementById('stat-passing').textContent = `${stats.passing_count}/${stats.total_students}`;
        });
    }

    // Export button
    document.getElementById('exportBtn').addEventListener('click', function() {
        window.location.href = `/teacher/grades/${data.classSubjectTeacherId}/export?term=${data.currentTerm}&sequence=${data.currentSequence}&year=${data.academicYear}`;
    });

    // Filter changes
    document.querySelectorAll('#filterTerm, #filterSequence, #filterYear').forEach(sel => {
        sel.addEventListener('change', function() {
            const term = document.getElementById('filterTerm').value;
            const sequence = document.getElementById('filterSequence').value;
            const year = document.getElementById('filterYear').value;
            window.location.href = `?term=${term}&sequence=${sequence}&year=${year}`;
        });
    });

    // Load initial statistics
    loadClassStatistics();
}

// Utility function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>

<style>
    .save-feedback {
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
</style>
@endsection
