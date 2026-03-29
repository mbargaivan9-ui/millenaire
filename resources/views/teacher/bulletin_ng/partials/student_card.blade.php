{{-- Partial: student_card.blade.php --}}
<div class="bng-student-card" data-id="{{ $student->id }}" id="sc-{{ $student->id }}">
    <div class="bng-student-card-header">
        <div class="bng-student-number">{{ $idx + 1 }}</div>
        <div class="bng-student-info">
            <div class="bng-student-name">{{ $student->nom }}</div>
            <div class="bng-student-meta">{{ $student->matricule }}</div>
        </div>
        <div class="bng-student-meta2">
            <span class="bng-badge {{ $student->sexe === 'M' ? 'bng-badge-info' : 'bng-badge-warning' }}">
                {{ $student->sexe === 'M' ? ($isEN ? 'Male' : 'Masculin') : ($isEN ? 'Female' : 'Féminin') }}
            </span>
        </div>
        <button class="bng-btn-icon bng-btn-icon-danger delete-student-btn"
                data-id="{{ $student->id }}" title="Supprimer">🗑</button>
    </div>
    <div class="bng-student-details">
        <span>{{ $isEN ? 'DOB' : 'Naissance' }}: {{ $student->date_naissance ? (\Carbon\Carbon::parse($student->date_naissance)->format('d/m/Y')) : '—' }}</span>
        <span>{{ $isEN ? 'Place' : 'Lieu' }}: {{ $student->lieu_naissance ?: '—' }}</span>
    </div>
</div>
