{{-- Partial: subject_row.blade.php --}}
<tr class="bng-subject-row" data-index="{{ $i }}">
    <td><span class="bng-badge bng-badge-primary">{{ $i + 1 }}</span></td>
    <td>
        <input type="text" name="subjects[{{ $i }}][nom]" class="bng-input bng-input-sm"
               value="{{ $sub->nom ?? '' }}"
               placeholder="{{ $isEN ? 'Subject name...' : 'Nom de la matière...' }}" required>
    </td>
    <td>
        <input type="number" name="subjects[{{ $i }}][coefficient]" class="bng-input bng-input-sm"
               min="0.5" max="20" step="0.5"
               value="{{ $sub->coefficient ?? 1 }}" required style="text-align:center;">
    </td>
    <td>
        <input type="text" name="subjects[{{ $i }}][nom_prof]" class="bng-input bng-input-sm"
               value="{{ $sub->nom_prof ?? '' }}"
               placeholder="{{ $isEN ? 'Teacher name...' : 'Nom du professeur...' }}">
    </td>
    <td>
        <button type="button" class="bng-btn-icon bng-btn-icon-danger remove-row-btn" title="Supprimer">✕</button>
    </td>
</tr>
