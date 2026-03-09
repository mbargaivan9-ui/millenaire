@php $grade = $grade ?? null; @endphp
<div class="mb-3">
    <label class="form-label">Élève</label>
    <select name="student_id" class="form-select" required>
        @foreach($students ?? [] as $student)
        <option value="{{ $student->id }}" {{ old('student_id', $grade?->student_id) == $student->id ? 'selected' : '' }}>
            {{ $student->user->name }}
        </option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Matière</label>
    <select name="subject_id" class="form-select" required>
        @foreach($subjects ?? [] as $subject)
        <option value="{{ $subject->id }}" {{ old('subject_id', $grade?->subject_id) == $subject->id ? 'selected' : '' }}>
            {{ $subject->name }}
        </option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Note (/20)</label>
    <input type="number" name="score" step="0.5" min="0" max="20" class="form-control" value="{{ old('score', $grade?->score) }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Trimestre</label>
    <select name="term" class="form-select" required>
        <option value="1" {{ old('term', $grade?->term) == 1 ? 'selected' : '' }}>1er Trimestre</option>
        <option value="2" {{ old('term', $grade?->term) == 2 ? 'selected' : '' }}>2ème Trimestre</option>
        <option value="3" {{ old('term', $grade?->term) == 3 ? 'selected' : '' }}>3ème Trimestre</option>
    </select>
</div>
