<aside class="sidebar" id="sidebar">

  {{-- Brand --}}
  <div class="sidebar-brand">
    <div class="brand-logo">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
      </svg>
    </div>
    <div class="brand-text">
      <span class="brand-name">{{ config('app.name', 'Millenaire') }}</span>
      <span class="brand-tagline">{{ __('nav.management') ?? 'Connect' }}</span>
    </div>
  </div>

  {{-- Scrollable Nav --}}
  <div class="sidebar-scroll">

    {{-- ═══ DASHBOARD & ADMIN SECTION ═══ --}}
    @if(auth()->user()?->isAdmin())
      <div class="sidebar-section">
        <span class="sidebar-section-label">{{ __('Admin') ?? 'Administration' }}</span>

        <a href="{{ route('admin.dashboard') }}" class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="layout-dashboard"></i></span>
          <span class="sidebar-label">{{ __('Dashboard') ?? 'Dashboard' }}</span>
        </a>

        {{-- Users Management --}}
        <div class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'active open' : '' }}" data-toggle="sub-users">
          <span class="sidebar-icon"><i data-lucide="users"></i></span>
          <span class="sidebar-label">{{ __('Users') ?? 'Utilisateurs' }}</span>
          <svg class="sidebar-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </div>
        <div class="sidebar-submenu {{ request()->routeIs('admin.users.*') ? 'open' : '' }}" id="sub-users">
          <a href="{{ route('admin.users.index') }}" class="sidebar-subitem {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
            {{ __('Users List') ?? 'Liste des utilisateurs' }}
          </a>
          <a href="{{ route('admin.users.create') }}" class="sidebar-subitem {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
            {{ __('Create User') ?? 'Créer utilisateur' }}
          </a>
        </div>

        {{-- Academic Management --}}
        <div class="sidebar-item {{ request()->routeIs('admin.classes.*', 'admin.subjects.*') ? 'active open' : '' }}" data-toggle="sub-academic">
          <span class="sidebar-icon"><i data-lucide="book-open"></i></span>
          <span class="sidebar-label">{{ __('Academic') ?? 'Académique' }}</span>
          <svg class="sidebar-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </div>
        <div class="sidebar-submenu {{ request()->routeIs('admin.classes.*', 'admin.subjects.*') ? 'open' : '' }}" id="sub-academic">
          <a href="{{ route('admin.classes.index') }}" class="sidebar-subitem {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
            {{ __('Classes') ?? 'Classes' }}
          </a>
          <a href="{{ route('admin.subjects.index') }}" class="sidebar-subitem {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
            {{ __('Subjects') ?? 'Matières' }}
          </a>
        </div>

        {{-- Finance --}}
        <a href="{{ route('admin.finance.index') }}" class="sidebar-item {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="wallet-2"></i></span>
          <span class="sidebar-label">{{ __('Finance') ?? 'Finances' }}</span>
        </a>

        {{-- Announcements --}}
        <a href="{{ route('admin.announcements.index') }}" class="sidebar-item {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="megaphone"></i></span>
          <span class="sidebar-label">{{ __('Announcements') ?? 'Annonces' }}</span>
        </a>

        {{-- Students Management --}}
        <a href="{{ route('admin.students.index') }}" class="sidebar-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="user-check"></i></span>
          <span class="sidebar-label">{{ __('Students') ?? 'Étudiants' }}</span>
        </a>

        {{-- Attendance Management --}}
        <a href="{{ route('admin.attendance.index') }}" class="sidebar-item {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="check-square"></i></span>
          <span class="sidebar-label">{{ __('Attendance') ?? 'Absences' }}</span>
        </a>

        {{-- Schedule Management --}}
        <a href="{{ route('admin.schedule.index') }}" class="sidebar-item {{ request()->routeIs('admin.schedule.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="calendar"></i></span>
          <span class="sidebar-label">{{ __('Schedule') ?? 'Emploi du Temps' }}</span>
        </a>

        {{-- Teacher Assignments --}}
        <a href="{{ route('admin.assignments.index') }}" class="sidebar-item {{ request()->routeIs('admin.assignments.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="users-2"></i></span>
          <span class="sidebar-label">{{ __('Assignments') ?? 'Affectations' }}</span>
        </a>

        {{-- Fees Management --}}
        <a href="{{ route('admin.fees.index') }}" class="sidebar-item {{ request()->routeIs('admin.fees.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="dollar-sign"></i></span>
          <span class="sidebar-label">{{ __('Fees') ?? 'Frais' }}</span>
        </a>
        

       

        {{-- Reports --}}
        <a href="{{ route('admin.reports.dashboard') }}" class="sidebar-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="file-text"></i></span>
          <span class="sidebar-label">{{ __('Reports') ?? 'Rapports' }}</span>
        </a>
       

        {{-- Roles & Permissions --}}
        <a href="{{ route('admin.roles.index') }}" class="sidebar-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="lock"></i></span>
          <span class="sidebar-label">{{ __('Roles') ?? 'Rôles' }}</span>
        </a>

        {{-- Settings --}}
        <a href="{{ route('admin.settings.edit') }}" class="sidebar-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="settings"></i></span>
          <span class="sidebar-label">{{ __('Settings') ?? 'Paramètres' }}</span>
        </a>
      </div>
    @endif

    {{-- ═══ TEACHER SECTION ═══ --}}
    @if(auth()->user()?->isTeacher())
      <div class="sidebar-section">
        <span class="sidebar-section-label">{{ __('nav.teaching') ?? 'Enseignement' }}</span>

        {{-- Dashboard --}}
        <a href="{{ route('teacher.dashboard') }}" class="sidebar-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="layout-dashboard"></i></span>
          <span class="sidebar-label">{{ __('nav.dashboard') ?? 'Dashboard' }}</span>
        </a>

        {{-- Bulletin Vivant (Live Grade Grid) --}}
        <div class="sidebar-item {{ request()->routeIs('teacher.bulletin.*', 'teacher.grades.*') ? 'active open' : '' }}" data-toggle="sub-bulletin">
          <span class="sidebar-icon"><i data-lucide="grid-3x3"></i></span>
          <span class="sidebar-label">{{ __('nav.bulletin') ?? 'Bulletin Vivant' }}</span>
          <svg class="sidebar-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </div>
        <div class="sidebar-submenu {{ request()->routeIs('teacher.bulletin.*', 'teacher.grades.*') ? 'open' : '' }}" id="sub-bulletin">
          <a href="{{ route('teacher.bulletin.index') }}" class="sidebar-subitem {{ request()->routeIs('teacher.bulletin.index') ? 'active' : '' }}">
            <i class="fas fa-list me-2"></i>{{ __('nav.my_classes') ?? 'Mes Classes' }}
          </a>
          <a href="{{ route('teacher.bulletin.completion') }}" class="sidebar-subitem {{ request()->routeIs('teacher.bulletin.completion') ? 'active' : '' }}">
            <i class="fas fa-check-circle me-2"></i>{{ __('nav.completion') ?? 'État de Completion' }}
          </a>
          <a href="{{ route('teacher.grades.entry.index', 0) }}" class="sidebar-subitem {{ request()->routeIs('teacher.grades.*') ? 'active' : '' }}">
            <i class="fas fa-edit me-2"></i>{{ __('nav.enter_marks') ?? 'Saisir Notes' }}
          </a>
        </div>

        {{-- Template Grid (Prof Principal ONLY) --}}
        @if(auth()->user()?->isProfPrincipal() || auth()->user()?->isAdmin())
          <div class="sidebar-item {{ request()->routeIs('teacher.report-cards.*', 'teacher.bulletin.template-grid', 'teacher.bulletin-structure-ocr.*') ? 'active open' : '' }}" data-toggle="sub-report-cards">
            <span class="sidebar-icon" style="color: #fbbf24;"><i class="fas fa-crown"></i></span>
            <span class="sidebar-label" style="color: #fbbf24; font-weight: 600;">{{ __('nav.report_cards') ?? 'Bulletins Classe' }}</span>
            <svg class="sidebar-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 18 15 12 9 6"/>
            </svg>
          </div>
          <div class="sidebar-submenu {{ request()->routeIs('teacher.report-cards.*', 'teacher.bulletin.template-grid', 'teacher.bulletin-structure-ocr.*') ? 'open' : '' }}" id="sub-report-cards">
            {{-- Template Grid (Main Feature) --}}
            <a href="{{ route('teacher.bulletin.template-grid') }}" class="sidebar-subitem {{ request()->routeIs('teacher.bulletin.template-grid') ? 'active' : '' }}" style="background: linear-gradient(90deg, rgba(34, 197, 94, 0.1) 0%, transparent 100%); border-left: 3px solid #22c55e; font-weight: 600; color: #22c55e;">
              <i class="fas fa-crown me-2" style="color: #fbbf24;"></i>{{ __('nav.template_grid') ?? 'Grille Template' }}
            </a>
            
            {{-- OCR Wizard --}}
            <a href="{{ route('teacher.bulletin.ocr-wizard') }}" class="sidebar-subitem {{ request()->routeIs('teacher.bulletin.ocr-wizard') ? 'active' : '' }}">
              <i class="fas fa-wand-magic-sparkles me-2"></i>{{ __('nav.ocr_wizard') ?? 'Digitaliseur OCR' }}
            </a>
            
            <a href="{{ route('teacher.report-cards') }}" class="sidebar-subitem {{ request()->routeIs('teacher.report-cards') ? 'active' : '' }}">
              <i class="fas fa-file-pdf me-2"></i>{{ __('nav.list') ?? 'Liste' }}
            </a>
            <a href="{{ route('teacher.bulletin-templates.index') }}" class="sidebar-subitem {{ request()->routeIs('teacher.bulletin-templates.*') ? 'active' : '' }}">
              <i class="fas fa-file-alt me-2"></i>{{ __('nav.templates') ?? 'Modèles' }}
            </a>
            <a href="{{ route('teacher.bulletin-structure-ocr.index') }}" class="sidebar-subitem {{ request()->routeIs('teacher.bulletin-structure-ocr.*') ? 'active' : '' }}">
              <i class="fas fa-image me-2"></i>{{ __('nav.bulletin_ocr') ?? 'Structures OCR' }}
            </a>
            <a href="{{ route('teacher.student-absences.index') }}" class="sidebar-subitem {{ request()->routeIs('teacher.student-absences.*') ? 'active' : '' }}">
              <i class="fas fa-calendar-times me-2"></i>{{ __('nav.absences') ?? 'Absences Étudiants' }}
            </a>
          </div>
        @endif

        {{-- Marks Management --}}
        <div class="sidebar-item {{ request()->routeIs('teacher.marks.*') ? 'active open' : '' }}" data-toggle="sub-marks">
          <span class="sidebar-icon"><i data-lucide="edit-3"></i></span>
          <span class="sidebar-label">{{ __('nav.marks') ?? 'Notes' }}</span>
          <svg class="sidebar-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </div>
        <div class="sidebar-submenu {{ request()->routeIs('teacher.marks.*') ? 'open' : '' }}" id="sub-marks">
          <a href="{{ route('teacher.marks.index') }}" class="sidebar-subitem {{ request()->routeIs('teacher.marks.index') ? 'active' : '' }}">
            <i class="fas fa-table me-2"></i>{{ __('nav.marks_list') ?? 'Gestion Notes' }}
          </a>
        </div>

        {{-- Attendance --}}
        <a href="{{ route('teacher.attendance.index') }}" class="sidebar-item {{ request()->routeIs('teacher.attendance.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="check-square"></i></span>
          <span class="sidebar-label">{{ __('nav.attendance') ?? 'Absences' }}</span>
        </a>

        {{-- Courses --}}
        <a href="{{ route('teacher.courses') }}" class="sidebar-item {{ request()->routeIs('teacher.courses') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="book"></i></span>
          <span class="sidebar-label">{{ __('nav.courses') ?? 'Cours' }}</span>
        </a>

        {{-- Assignments --}}
        <a href="{{ route('teacher.assignments') }}" class="sidebar-item {{ request()->routeIs('teacher.assignments') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="list-checks"></i></span>
          <span class="sidebar-label">{{ __('nav.assignments') ?? 'Affectations' }}</span>
        </a>

        {{-- Advanced Dashboard (Phase 3) --}}
        @if(Route::has('teacher.advanced.dashboard'))
        <a href="{{ route('teacher.advanced.dashboard') }}" class="sidebar-item {{ request()->routeIs('teacher.advanced.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="trending-up"></i></span>
          <span class="sidebar-label">{{ __('nav.advanced') ?? 'Tableau Avancé' }}</span>
        </a>
        @endif
      </div>
    @endif

    {{-- ═══ PARENT SECTION ═══ --}}
    @if(auth()->user()?->isParent())
      <div class="sidebar-section">
        <span class="sidebar-section-label">{{ __('nav.parent') ?? 'Parent' }}</span>

        <a href="{{ route('parent.dashboard') }}" class="sidebar-item {{ request()->routeIs('parent.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="layout-dashboard"></i></span>
          <span class="sidebar-label">{{ __('nav.dashboard') ?? 'Dashboard' }}</span>
        </a>

        <a href="{{ route('parent.children') }}" class="sidebar-item {{ request()->routeIs('parent.children*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="users"></i></span>
          <span class="sidebar-label">{{ __('nav.my_children') ?? 'Mes enfants' }}</span>
        </a>

        <a href="{{ route('parent.payments') }}" class="sidebar-item {{ request()->routeIs('parent.payments') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="credit-card"></i></span>
          <span class="sidebar-label">{{ __('nav.payments') ?? 'Paiements' }}</span>
        </a>
      </div>
    @endif

    {{-- ═══ STUDENT SECTION ═══ --}}
    @if(auth()->user()?->isStudent())
      <div class="sidebar-section">
        <span class="sidebar-section-label">{{ __('nav.student') ?? 'Étudiant' }}</span>

        <a href="{{ route('student.dashboard') }}" class="sidebar-item {{ request()->routeIs('student.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="layout-dashboard"></i></span>
          <span class="sidebar-label">{{ __('nav.dashboard') ?? 'Dashboard' }}</span>
        </a>

        <a href="{{ route('student.marks') }}" class="sidebar-item {{ request()->routeIs('student.marks') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="award"></i></span>
          <span class="sidebar-label">{{ __('nav.my_marks') ?? 'Mes notes' }}</span>
        </a>

        <a href="{{ route('student.attendance') }}" class="sidebar-item {{ request()->routeIs('student.attendance') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="calendar"></i></span>
          <span class="sidebar-label">{{ __('nav.attendance') ?? 'Absences' }}</span>
        </a>

        <a href="{{ route('student.schedule') }}" class="sidebar-item {{ request()->routeIs('student.schedule') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="clock"></i></span>
          <span class="sidebar-label">{{ __('nav.schedule') ?? 'Emploi du temps' }}</span>
        </a>

        @if(Route::has('student.assignments'))
        <a href="{{ route('student.assignments') }}" class="sidebar-item {{ request()->routeIs('student.assignments') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="list-check"></i></span>
          <span class="sidebar-label">{{ __('nav.assignments') ?? 'Devoirs' }}</span>
        </a>
        @endif

        <a href="{{ route('student.report-cards') }}" class="sidebar-item {{ request()->routeIs('student.report-cards*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="file-text"></i></span>
          <span class="sidebar-label">{{ __('nav.report_cards') ?? 'Bulletins' }}</span>
        </a>
      </div>
    @endif

    {{-- ═══ COMMON SECTION ═══ --}}
    <div class="sidebar-section">
      <span class="sidebar-section-label">{{ __('nav.communication') ?? 'Communication' }}</span>

      <a href="{{ route('chat.index') }}" class="sidebar-item {{ request()->routeIs('chat.*') ? 'active' : '' }}">
        <span class="sidebar-icon"><i data-lucide="message-circle"></i></span>
        <span class="sidebar-label">{{ __('nav.chat') ?? 'Chat' }}</span>
        @php
          $unreadChatCount = \App\Models\Message::whereHas('conversation.participants', function($q) {
            $q->where('user_id', auth()->id());
          })->where('is_read', false)->where('sender_id', '!=', auth()->id())->count();
        @endphp
        @if($unreadChatCount > 0)
          <span class="sidebar-badge">{{ $unreadChatCount }}</span>
        @endif
      </a>

      <a href="{{ route('notifications.index') }}" class="sidebar-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
        <span class="sidebar-icon"><i data-lucide="bell"></i></span>
        <span class="sidebar-label">{{ __('nav.notifications') ?? 'Notifications' }}</span>
        @php
          $notificationCount = \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count();
        @endphp
        @if($notificationCount > 0)
          <span class="sidebar-badge">{{ $notificationCount }}</span>
        @endif
      </a>
    </div>

  </div>{{-- end sidebar-scroll --}}

  {{-- User footer --}}
  <div class="sidebar-user" data-dropdown="user-dropdown-sidebar">
    @if(auth()->user()?->profile_photo)
      <img src="{{ auth()->user()->avatar_url }}" class="user-avatar avatar-md" alt="{{ auth()->user()->name }}" style="image-rendering:crisp-edges;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;" id="sidebar-avatar">
    @else
      <div class="user-avatar avatar-md" style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;font-weight:700;font-size:15px;">
        {{ substr(auth()->user()?->name ?? 'U', 0, 1) }}
      </div>
    @endif
    <div class="user-info">
      <div class="user-name" style="font-weight:700;">{{ auth()->user()?->name ?? 'Guest' }}</div>
      <div class="user-role" style="font-size:12px;">{{ ucfirst(auth()->user()?->role ?? 'user') }}</div>
    </div>
  </div>

</aside>
