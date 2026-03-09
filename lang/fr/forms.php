<?php

return [
    // ==================== Headers and Titles ====================
    'heading' => 'Formulaires et Interfaces',
    
    // ==================== Common Form Fields ====================
    'form_fields' => [
        'name' => 'Nom',
        'email' => 'Email',
        'phone' => 'Téléphone',
        'address' => 'Adresse',
        'city' => 'Ville',
        'country' => 'Pays',
        'code' => 'Code',
        'description' => 'Description',
        'status' => 'Statut',
        'active' => 'Actif',
        'inactive' => 'Inactif',
    ],
    
    // ==================== Common Actions ====================
    'actions' => [
        'create' => 'Créer',
        'edit' => 'Éditer',
        'delete' => 'Supprimer',
        'update' => 'Mettre à jour',
        'save' => 'Enregistrer',
        'cancel' => 'Annuler',
        'back' => 'Retour',
        'export' => 'Exporter',
        'import' => 'Importer',
        'download' => 'Télécharger',
        'upload' => 'Télécharger',
        'search' => 'Rechercher',
        'filter' => 'Filtrer',
        'sort' => 'Trier',
        'view' => 'Afficher',
        'print' => 'Imprimer',
    ],
    
    // ==================== Common Messages ====================
    'messages' => [
        'success' => 'Opération réussie',
        'error' => 'Une erreur s\'est produite',
        'warning' => 'Attention',
        'info' => 'Information',
        'confirm' => 'Êtes-vous sûr ?',
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer cet élément ?',
        'loading' => 'Chargement...',
        'no_data' => 'Aucune donnée',
        'no_results' => 'Aucun résultat trouvé',
        'required' => 'Ce champ est obligatoire',
        'invalid_email' => 'Email invalide',
        'invalid_phone' => 'Numéro de téléphone invalide',
    ],
    
    // ==================== Validation ====================
    'validation' => [
        'required' => ':attribute est obligatoire',
        'unique' => ':attribute existe déjà',
        'email' => ':attribute doit être une adresse email valide',
        'min' => ':attribute doit contenir au moins :min caractères',
        'max' => ':attribute ne doit pas dépasser :max caractères',
        'confirmed' => 'La confirmation de :attribute ne correspond pas',
        'numeric' => ':attribute doit être un nombre',
        'date' => ':attribute doit être une date valide',
    ],
];
