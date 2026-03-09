@extends('layouts.app')
@section('title', __('nav.dashboard'))

@section('content')

{{-- Page Header --}}
<div class="page-header">
  <div>
    <div class="breadcrumb">
      <span>{{ __('app.home') }}</span>
      <span class="breadcrumb-sep">/</span>
      <span class="breadcrumb-current">{{ __('nav.dashboard') }}</span>
    </div>
    <h1 class="page-title">Tableau de Bord Administrateur</h1>
    <p class="page-subtitle">Bienvenue, {{ auth()->user()?->name ?? 'Administrateur' }}</p>
  </div>
  <div class="page-actions">
    <button class="btn btn-outline">
      <i data-lucide="download" style="width:14px;height:14px"></i>
      Exporter rapports
    </button>
    
  </div>
</div>

{{-- Daily Briefing --}}
<div class="briefing-card mb-20">
  <div class="briefing-header">
    <span class="briefing-label">RÉSUMÉ DU JOUR</span>
    <span class="briefing-badge">
      <i data-lucide="trending-up" style="width:13px;height:13px"></i>
      8.2% vs hier
    </span>
  </div>
  <h2 class="briefing-title">{{ $briefing['title'] ?? 'Système performant' }}</h2>
  <p class="briefing-text">{{ $briefing['text'] ?? 'État du système optimal avec une activité normale' }}</p>

  <div class="briefing-stats">
    <div class="briefing-stat">
      <div class="briefing-stat-label">Utilisateurs Total</div>
      <div class="briefing-stat-value">{{ $totalUsers ?? 0 }}</div>
      <div class="briefing-stat-change text-success">+12.5%</div>
    </div>
    <div class="briefing-stat">
      <div class="briefing-stat-label">Élèves Actifs</div>
      <div class="briefing-stat-value">{{ $activeStudents ?? 0 }}</div>
      <div class="briefing-stat-change text-success">+8.3%</div>
    </div>
    <div class="briefing-stat">
      <div class="briefing-stat-label">Enseignants</div>
      <div class="briefing-stat-value">{{ $totalTeachers ?? 0 }}</div>
      <div class="briefing-stat-change text-success">+2.1%</div>
    </div>
    <div class="briefing-stat">
      <div class="briefing-stat-label">Activité</div>
      <div class="briefing-stat-value">98.2%</div>
      <div class="briefing-stat-change text-success">+1.2%</div>
    </div>
  </div>

  <div class="briefing-tags">
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <span class="briefing-tag">
        <i data-lucide="zap" style="width:12px;height:12px;color:var(--warning)"></i>
        Système stable
      </span>
      <span class="briefing-tag">
        <i data-lucide="shield-check" style="width:12px;height:12px;color:var(--success)"></i>
        SLA 99.1%
      </span>
      <span class="briefing-tag">
        <i data-lucide="trending-up" style="width:12px;height:12px;color:var(--primary)"></i>
        Croissance positive
      </span>
    </div>
    <a class="briefing-link" href="">
      Voir analytics
      <i data-lucide="arrow-right" style="width:13px;height:13px"></i>
    </a>
  </div>
</div>

{{-- KPI Row --}}
<div class="kpi-grid mb-20">
  <div class="kpi-card">
    <div class="kpi-label">Total Utilisateurs</div>
    <div class="kpi-value">{{ $totalUsers ?? 0 }}</div>
    <div class="kpi-change up">
      <i data-lucide="trending-up" style="width:12px;height:12px"></i>
      12.5%
    </div>
  </div>
  <div class="kpi-card">
    <div class="kpi-label">Total Étudiants</div>
    <div class="kpi-value">{{ $totalStudents ?? 0 }}</div>
    <div class="kpi-change up">
      <i data-lucide="trending-up" style="width:12px;height:12px"></i>
      8.3%
    </div>
  </div>
  <div class="kpi-card">
    <div class="kpi-label">Revenus Encaissés</div>
    <div class="kpi-value">{{ number_format($totalRevenue ?? 0, 0, ',', ' ') }}</div>
    <div class="kpi-change up">
      <i data-lucide="trending-up" style="width:12px;height:12px"></i>
      5.1%
    </div>
  </div>
  <div class="kpi-card">
    <div class="kpi-label">Paiements En Attente</div>
    <div class="kpi-value">{{ $pendingPayments ?? 0 }}</div>
    <div class="kpi-change" style="color:var(--warning)">
      <i data-lucide="alert-circle" style="width:12px;height:12px"></i>
      {{ number_format($unpaidAmount ?? 0, 0) }}
    </div>
  </div>
</div>

@if($inactiveStudents > 0)
<div style="background:var(--info-bg);border:1px solid var(--info);border-radius:8px;
            padding:12px 16px;margin-bottom:20px;font-size:13px;color:var(--info);
            display:flex;align-items:center;gap:10px">
  <i data-lucide="info" style="width:16px;height:16px;flex-shrink:0"></i>
  <div>
    <strong>{{ $inactiveStudents }}</strong> nouveaux étudiants en attente de validation
    <a href="{{ route('admin.users.index', ['role' => 'student', 'status' => 'inactive']) }}"
       style="color:var(--info);font-weight:600;margin-left:10px">Voir →</a>
  </div>
</div>
@endif

{{-- Module Navigation Grid --}}
<h2 style="margin-bottom:20px;font-size:16px;font-weight:600">Modules de Gestion</h2>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:30px">
  
  {{-- Users Management --}}
  <a href="{{ route('admin.users.index') }}" class="module-card" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;border:1px solid var(--border);border-radius:12px;background:var(--card-bg);transition:all 0.2s ease;cursor:pointer">
    <div style="width:48px;height:48px;background:rgba(99, 102, 241, 0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
      <i data-lucide="users" style="width:24px;height:24px;color:#6366f1"></i>
    </div>
    <span style="font-weight:600;font-size:14px;text-align:center">Utilisateurs</span>
    <span style="font-size:12px;color:var(--text-muted);margin-top:4px">{{ $totalUsers ?? 0 }} total</span>
  </a>

  {{-- Classes Management --}}
  <a href="{{ route('admin.classes.index') }}" class="module-card" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;border:1px solid var(--border);border-radius:12px;background:var(--card-bg);transition:all 0.2s ease;cursor:pointer">
    <div style="width:48px;height:48px;background:rgba(34, 197, 94, 0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
      <i data-lucide="book-open" style="width:24px;height:24px;color:#22c55e"></i>
    </div>
    <span style="font-weight:600;font-size:14px;text-align:center">Classes</span>
    <span style="font-size:12px;color:var(--text-muted);margin-top:4px">{{ $totalClasses ?? 0 }} actives</span>
  </a>

  {{-- Students Management --}}
  <a href="{{ route('admin.students.index') }}" class="module-card" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;border:1px solid var(--border);border-radius:12px;background:var(--card-bg);transition:all 0.2s ease;cursor:pointer">
    <div style="width:48px;height:48px;background:rgba(59, 130, 246, 0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
      <i data-lucide="user-check" style="width:24px;height:24px;color:#3b82f6"></i>
    </div>
    <span style="font-weight:600;font-size:14px;text-align:center">Étudiants</span>
    <span style="font-size:12px;color:var(--text-muted);margin-top:4px">{{ $totalStudents ?? 0 }} inscrits</span>
  </a>

  {{-- Finance Management --}}
  <a href="{{ route('admin.finance.index') }}" class="module-card" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;border:1px solid var(--border);border-radius:12px;background:var(--card-bg);transition:all 0.2s ease;cursor:pointer">
    <div style="width:48px;height:48px;background:rgba(168, 85, 247, 0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
      <i data-lucide="credit-card" style="width:24px;height:24px;color:#a855f7"></i>
    </div>
    <span style="font-weight:600;font-size:14px;text-align:center">Finances</span>
    <span style="font-size:12px;color:var(--text-muted);margin-top:4px">{{ number_format($totalRevenue ?? 0, 0) }}</span>
  </a>

  {{-- Subjects Management --}}
  <a href="{{ route('admin.subjects.index') }}" class="module-card" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;border:1px solid var(--border);border-radius:12px;background:var(--card-bg);transition:all 0.2s ease;cursor:pointer">
    <div style="width:48px;height:48px;background:rgba(249, 115, 22, 0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
      <i data-lucide="bookmark" style="width:24px;height:24px;color:#f97316"></i>
    </div>
    <span style="font-weight:600;font-size:14px;text-align:center">Matières</span>
    <span style="font-size:12px;color:var(--text-muted);margin-top:4px">Gestion</span>
  </a>

  {{-- Schedule Management --}}
  <a href="{{ route('admin.schedule.index') }}" class="module-card" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;border:1px solid var(--border);border-radius:12px;background:var(--card-bg);transition:all 0.2s ease;cursor:pointer">
    <div style="width:48px;height:48px;background:rgba(236, 72, 153, 0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
      <i data-lucide="calendar" style="width:24px;height:24px;color:#ec4899"></i>
    </div>
    <span style="font-weight:600;font-size:14px;text-align:center">Emploi du Temps</span>
    <span style="font-size:12px;color:var(--text-muted);margin-top:4px">Planification</span>
  </a>

  {{-- Announcements --}}
  <a href="{{ route('admin.announcements.index') }}" class="module-card" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;border:1px solid var(--border);border-radius:12px;background:var(--card-bg);transition:all 0.2s ease;cursor:pointer">
    <div style="width:48px;height:48px;background:rgba(14, 165, 233, 0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
      <i data-lucide="megaphone" style="width:24px;height:24px;color:#0ea5e9"></i>
    </div>
    <span style="font-weight:600;font-size:14px;text-align:center">Annonces</span>
    <span style="font-size:12px;color:var(--text-muted);margin-top:4px">Communications</span>
  </a>

  {{-- Roles Management --}}
  <a href="{{ route('admin.roles.index') }}" class="module-card" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;border:1px solid var(--border);border-radius:12px;background:var(--card-bg);transition:all 0.2s ease;cursor:pointer">
    <div style="width:48px;height:48px;background:rgba(139, 92, 246, 0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
      <i data-lucide="lock" style="width:24px;height:24px;color:#8b5cf6"></i>
    </div>
    <span style="font-weight:600;font-size:14px;text-align:center">Rôles</span>
    <span style="font-size:12px;color:var(--text-muted);margin-top:4px">Permissions</span>
  </a>

  {{-- Attendance Management --}}
  <a href="{{ route('admin.attendance.index') }}" class="module-card" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;border:1px solid var(--border);border-radius:12px;background:var(--card-bg);transition:all 0.2s ease;cursor:pointer">
    <div style="width:48px;height:48px;background:rgba(34, 197, 94, 0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
      <i data-lucide="check-square" style="width:24px;height:24px;color:#22c55e"></i>
    </div>
    <span style="font-weight:600;font-size:14px;text-align:center">Absences</span>
    <span style="font-size:12px;color:var(--text-muted);margin-top:4px">Contrôle</span>
  </a>

  {{-- Assignments Management --}}
  <a href="{{ route('admin.assignments.index') }}" class="module-card" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;border:1px solid var(--border);border-radius:12px;background:var(--card-bg);transition:all 0.2s ease;cursor:pointer">
    <div style="width:48px;height:48px;background:rgba(59, 130, 246, 0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
      <i data-lucide="users-2" style="width:24px;height:24px;color:#3b82f6"></i>
    </div>
    <span style="font-weight:600;font-size:14px;text-align:center">Affectations</span>
    <span style="font-size:12px;color:var(--text-muted);margin-top:4px">Prof-Classe</span>
  </a>

  {{-- Fees Management --}}
  <a href="{{ route('admin.fees.index') }}" class="module-card" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;border:1px solid var(--border);border-radius:12px;background:var(--card-bg);transition:all 0.2s ease;cursor:pointer">
    <div style="width:48px;height:48px;background:rgba(168, 85, 247, 0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
      <i data-lucide="dollar-sign" style="width:24px;height:24px;color:#a855f7"></i>
    </div>
    <span style="font-weight:600;font-size:14px;text-align:center">Frais</span>
    <span style="font-size:12px;color:var(--text-muted);margin-top:4px">Définis par classe</span>
  </a>

  {{-- KPI Dashboard --}}
  <a href="{{ route('admin.kpi.index') }}" class="module-card" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;border:1px solid var(--border);border-radius:12px;background:var(--card-bg);transition:all 0.2s ease;cursor:pointer">
    <div style="width:48px;height:48px;background:rgba(249, 115, 22, 0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
      <i data-lucide="bar-chart-3" style="width:24px;height:24px;color:#f97316"></i>
    </div>
    <span style="font-weight:600;font-size:14px;text-align:center">KPI</span>
    <span style="font-size:12px;color:var(--text-muted);margin-top:4px">Analytics</span>
  </a>

  {{-- Reports --}}
  <a href="{{ route('admin.reports.dashboard') }}" class="module-card" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;border:1px solid var(--border);border-radius:12px;background:var(--card-bg);transition:all 0.2s ease;cursor:pointer">
    <div style="width:48px;height:48px;background:rgba(236, 72, 153, 0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
      <i data-lucide="file-text" style="width:24px;height:24px;color:#ec4899"></i>
    </div>
    <span style="font-weight:600;font-size:14px;text-align:center">Rapports</span>
    <span style="font-size:12px;color:var(--text-muted);margin-top:4px">Analytiques</span>
  </a>

  {{-- Settings --}}
  <a href="{{ route('admin.settings.edit') }}" class="module-card" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;border:1px solid var(--border);border-radius:12px;background:var(--card-bg);transition:all 0.2s ease;cursor:pointer">
    <div style="width:48px;height:48px;background:rgba(100, 116, 139, 0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
      <i data-lucide="settings" style="width:24px;height:24px;color:#64748b"></i>
    </div>
    <span style="font-weight:600;font-size:14px;text-align:center">Paramètres</span>
    <span style="font-size:12px;color:var(--text-muted);margin-top:4px">Configuration</span>
  </a>

</div>

<style>
  .module-card {
    transition: all 0.2s ease;
  }
  .module-card:hover {
    border-color: var(--primary) !important;
    background: var(--primary-bg) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
</style>

{{-- Stats Grid --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:20px">

  {{-- Top Classes --}}
  <div class="card">
    <div class="card-header">
      <i data-lucide="star" style="width:16px;height:16px"></i>
      <span>Top Classes (effectif)</span>
    </div>
    <div class="card-body">
      <div class="table">
        @forelse($topClasses ?? [] as $class)
        <div class="table-row">
          <div class="table-cell" style="font-weight:600">{{ $class->name ?? 'N/A' }}</div>
          <div class="table-cell" style="text-align:right;color:var(--text-muted);font-size:12px">
            @php
              $capacity = $class->capacity ?? 40;
              $count = $class->students_count ?? 0;
              $percent = $capacity > 0 ? ($count / $capacity) * 100 : 0;
            @endphp
            {{ $count }}/{{ $capacity }}
          </div>
          <div class="table-cell" style="text-align:right">
            <span style="font-weight:600;color:{{ $percent > 90 ? 'var(--warning)' : 'var(--success)' }}">
              {{ number_format($percent, 0) }}%
            </span>
          </div>
        </div>
        @empty
        <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:13px">
          Aucune classe
        </div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Recent Activities --}}
  <div class="card">
    <div class="card-header">
      <i data-lucide="history" style="width:16px;height:16px"></i>
      <span>Activités Récentes</span>
    </div>
    <div class="card-body">
      <div style="max-height:300px;overflow-y:auto">
        @forelse($recentActivities ?? [] as $activity)
        <div style="display:flex;align-items:flex-start;gap:10px;padding:10px 0;border-bottom:1px solid var(--border)">
          <div style="flex-shrink:0;width:32px;height:32px;border-radius:50%;background:{{ $activity->status === 'paid' ? 'var(--success-bg)' : 'var(--warning-bg)' }};
                      display:flex;align-items:center;justify-content:center">
            <i data-lucide="{{ $activity->status === 'paid' ? 'check-circle' : 'clock' }}"
               style="width:14px;height:14px;color:{{ $activity->status === 'paid' ? 'var(--success)' : 'var(--warning)' }}"></i>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-weight:600;font-size:12px">{{ substr($activity->student?->user?->name ?? 'N/A', 0, 20) }}</div>
            <div style="font-size:11px;color:var(--text-muted)">{{ $activity->fee?->name ?? 'Frais' }}</div>
            <div style="font-size:10px;color:var(--text-muted)">{{ $activity->created_at->diffForHumans() }}</div>
          </div>
        </div>
        @empty
        <div style="padding:20px;text-align:center;color:var(--text-muted);font-size:13px">
          Aucune activité
        </div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Quick Actions --}}
  <div class="card">
    <div class="card-header">
      <i data-lucide="zap" style="width:16px;height:16px"></i>
      <span>Actions Rapides</span>
    </div>
    <div class="card-body">
      <div style="display:flex;flex-direction:column;gap:8px">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary" style="font-size:12px">
          <i data-lucide="user-plus" style="width:13px;height:13px"></i>
          Ajouter Utilisateur
        </a>
        <a href="{{ route('admin.classes.index') }}" class="btn btn-primary" style="font-size:12px">
          <i data-lucide="book-open" style="width:13px;height:13px"></i>
          Gérer Classes
        </a>
        <a href="{{ route('admin.finance.index') }}" class="btn btn-primary" style="font-size:12px">
          <i data-lucide="credit-card" style="width:13px;height:13px"></i>
          Gérer Paiements
        </a>
      </div>
    </div>
  </div>

</div>

@endsection

