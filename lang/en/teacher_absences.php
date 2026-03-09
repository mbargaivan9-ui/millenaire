<?php

return [
    'title' => 'Teacher Absence Management',
    'create_title' => 'Record Absence',
    'edit_title' => 'Edit Absence',
    'report_title' => 'Teacher Attendance Report',

    // Labels
    'teacher' => 'Teacher',
    'date' => 'Date',
    'status' => 'Status',
    'reason' => 'Reason',
    'justification_document' => 'Justification Document',
    'recorded_by' => 'Recorded By',
    'recorded_at' => 'Recording Date',
    'approved_by' => 'Approved By',
    'approved_at' => 'Approval Date',
    'is_approved' => 'Approved',

    // Status Options
    'status_present' => 'Present',
    'status_absent' => 'Absent',
    'status_late' => 'Late',
    'status_justified' => 'Justified Absence',
    'status_medical_leave' => 'Medical Leave',
    'status_authorized_leave' => 'Authorized Leave',

    // Actions
    'add_new' => 'Add New Absence',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'approve' => 'Approve',
    'reject' => 'Reject',
    'view_report' => 'View Report',
    'export' => 'Export',
    'import' => 'Bulk Import',
    'back_to_list' => 'Back to List',

    // Filters
    'filter_by_teacher' => 'Filter by Teacher',
    'filter_by_status' => 'Filter by Status',
    'filter_by_date' => 'Filter by Date',
    'filter_by_approval' => 'Filter by Approval',
    'start_date' => 'Start Date',
    'end_date' => 'End Date',
    'approval_pending' => 'Pending',
    'approval_approved' => 'Approved',
    'approval_all' => 'All',

    // Messages
    'created_success' => 'Absence recorded successfully.',
    'updated_success' => 'Absence updated successfully.',
    'deleted_success' => 'Absence deleted successfully.',
    'approved_success' => 'Absence approved successfully.',
    'rejected_success' => 'Absence rejected successfully.',
    'bulk_created' => 'absences recorded.',
    'no_records' => 'No absences recorded.',

    // Statistics
    'total_records' => 'Total Records',
    'total_absences' => 'Unjustified Absences',
    'total_justified' => 'Justified Absences',
    'pending_approval' => 'Pending Approval',
    'approved_records' => 'Approved',

    // Report
    'absence_rate' => 'Absence Rate',
    'attendance_rate' => 'Attendance Rate',
    'summary_by_teacher' => 'Summary by Teacher',
    'total_days' => 'Total Days',
    'unjustified_absences' => 'Unjustified Absences',
    'justified_absences' => 'Justified Absences',
    'generate_report' => 'Generate Report',
    'period' => 'Period',
    'from' => 'From',
    'to' => 'To',

    // Validation
    'teacher_required' => 'Teacher is required.',
    'date_required' => 'Date is required.',
    'status_required' => 'Status is required.',
    'invalid_status' => 'Invalid status.',
    'document_invalid' => 'Document must be a PDF, JPG, or PNG file.',
    'document_size' => 'Document cannot exceed 5 MB.',

    // Table Headers
    'header_teacher' => 'Teacher',
    'header_date' => 'Date',
    'header_status' => 'Status',
    'header_reason' => 'Reason',
    'header_approval' => 'Approval',
    'header_actions' => 'Actions',

    // Breadcrumb
    'breadcrumb_home' => 'Home',
    'breadcrumb_admin' => 'Administration',
    'breadcrumb_absences' => 'Teacher Absences',
];
