<?php

return [
    // ==================== Headers and Titles ====================
    'heading' => 'Forms and Interfaces',
    
    // ==================== Common Form Fields ====================
    'form_fields' => [
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'address' => 'Address',
        'city' => 'City',
        'country' => 'Country',
        'code' => 'Code',
        'description' => 'Description',
        'status' => 'Status',
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],
    
    // ==================== Common Actions ====================
    'actions' => [
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'update' => 'Update',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'back' => 'Back',
        'export' => 'Export',
        'import' => 'Import',
        'download' => 'Download',
        'upload' => 'Upload',
        'search' => 'Search',
        'filter' => 'Filter',
        'sort' => 'Sort',
        'view' => 'View',
        'print' => 'Print',
    ],
    
    // ==================== Common Messages ====================
    'messages' => [
        'success' => 'Operation successful',
        'error' => 'An error occurred',
        'warning' => 'Warning',
        'info' => 'Information',
        'confirm' => 'Are you sure?',
        'confirm_delete' => 'Are you sure you want to delete this item?',
        'loading' => 'Loading...',
        'no_data' => 'No data',
        'no_results' => 'No results found',
        'required' => 'This field is required',
        'invalid_email' => 'Invalid email',
        'invalid_phone' => 'Invalid phone number',
    ],
    
    // ==================== Validation ====================
    'validation' => [
        'required' => ':attribute is required',
        'unique' => ':attribute already exists',
        'email' => ':attribute must be a valid email address',
        'min' => ':attribute must be at least :min characters',
        'max' => ':attribute may not be greater than :max characters',
        'confirmed' => 'The :attribute confirmation does not match',
        'numeric' => ':attribute must be a number',
        'date' => ':attribute must be a valid date',
    ],
];
