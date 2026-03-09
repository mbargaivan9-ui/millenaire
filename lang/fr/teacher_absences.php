<?php

return [
    'title' => 'Gestion des Absences des Enseignants',
    'create_title' => 'Enregistrer une Absence',
    'edit_title' => 'Modifier une Absence',
    'report_title' => 'Rapport d\'Assiduité des Enseignants',

    // Labels
    'teacher' => 'Enseignant',
    'date' => 'Date',
    'status' => 'Statut',
    'reason' => 'Raison',
    'justification_document' => 'Document de Justification',
    'recorded_by' => 'Enregistré par',
    'recorded_at' => 'Date d\'Enregistrement',
    'approved_by' => 'Approuvé par',
    'approved_at' => 'Date d\'Approbation',
    'is_approved' => 'Approuvé',

    // Status Options
    'status_present' => 'Présent',
    'status_absent' => 'Absent',
    'status_late' => 'En Retard',
    'status_justified' => 'Absence Justifiée',
    'status_medical_leave' => 'Congé Médical',
    'status_authorized_leave' => 'Congé Autorisé',

    // Actions
    'add_new' => 'Ajouter une Nouvelle Absence',
    'edit' => 'Modifier',
    'delete' => 'Supprimer',
    'approve' => 'Approuver',
    'reject' => 'Rejeter',
    'view_report' => 'Voir le Rapport',
    'export' => 'Exporter',
    'import' => 'Importer en Masse',
    'back_to_list' => 'Retour à la Liste',

    // Filters
    'filter_by_teacher' => 'Filtrer par Enseignant',
    'filter_by_status' => 'Filtrer par Statut',
    'filter_by_date' => 'Filtrer par Date',
    'filter_by_approval' => 'Filtrer par Approbation',
    'start_date' => 'Date de Début',
    'end_date' => 'Date de Fin',
    'approval_pending' => 'En Attente',
    'approval_approved' => 'Approuvé',
    'approval_all' => 'Tous',

    // Messages
    'created_success' => 'Absence enregistrée avec succès.',
    'updated_success' => 'Absence mise à jour avec succès.',
    'deleted_success' => 'Absence supprimée avec succès.',
    'approved_success' => 'Absence approuvée avec succès.',
    'rejected_success' => 'Absence rejetée avec succès.',
    'bulk_created' => 'absences enregistrées en masse.',
    'no_records' => 'Aucune absence enregistrée.',

    // Statistics
    'total_records' => 'Total d\'Enregistrements',
    'total_absences' => 'Absences Non Justifiées',
    'total_justified' => 'Absences Justifiées',
    'pending_approval' => 'En Attente d\'Approbation',
    'approved_records' => 'Approuvées',

    // Report
    'absence_rate' => 'Taux d\'Absence',
    'attendance_rate' => 'Taux de Présence',
    'summary_by_teacher' => 'Résumé par Enseignant',
    'total_days' => 'Total de Jours',
    'unjustified_absences' => 'Absences Non Justifiées',
    'justified_absences' => 'Absences Justifiées',
    'generate_report' => 'Générer le Rapport',
    'period' => 'Période',
    'from' => 'Du',
    'to' => 'Au',

    // Validation
    'teacher_required' => 'L\'enseignant est requis.',
    'date_required' => 'La date est requise.',
    'status_required' => 'Le statut est requis.',
    'invalid_status' => 'Statut invalide.',
    'document_invalid' => 'Le document doit être un fichier PDF, JPG ou PNG.',
    'document_size' => 'Le document ne doit pas dépasser 5 MB.',

    // Table Headers
    'header_teacher' => 'Enseignant',
    'header_date' => 'Date',
    'header_status' => 'Statut',
    'header_reason' => 'Raison',
    'header_approval' => 'Approbation',
    'header_actions' => 'Actions',

    // Breadcrumb
    'breadcrumb_home' => 'Accueil',
    'breadcrumb_admin' => 'Administration',
    'breadcrumb_absences' => 'Absences des Enseignants',
];
