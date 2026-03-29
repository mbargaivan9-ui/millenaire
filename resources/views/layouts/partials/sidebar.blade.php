<aside class="sidebar" id="sidebar">

  {{-- Brand --}}
  <div class="sidebar-brand">
    <div class="brand-logo">
      @php 
        $settings = $globalSettings ?? App\Models\EstablishmentSetting::getInstance();
        $logoUrl = \App\Helpers\SettingsHelper::logoUrl();
      @endphp
      @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $settings->platform_name }}" style="width:40px;height:40px;object-fit:contain;">
      @else
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
          <path d="M6 12v5c3 3 9 3 12 0v-5"/>
        </svg>
      @endif
    </div>
    <div class="brand-text">
      <span class="brand-name">{{ $settings->platform_name ?? config('app.name', 'Millenaire') }}</span>
      <span class="brand-tagline">{{ __('Management') ?? 'Connect' }}</span>
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
          <span class="sidebar-label">{{ __('Tableau de bord') ?? 'Tableau de bord' }}</span>
        </a>

        {{-- Users Management --}}
        <div class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'active open' : '' }}" data-toggle="sub-users">
          <span class="sidebar-icon"><i data-lucide="users"></i></span>
          <span class="sidebar-label">{{ __('Utilisateurs')  }}</span>
          <svg class="sidebar-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </div>
        <div class="sidebar-submenu {{ request()->routeIs('admin.users.*') ? 'open' : '' }}" id="sub-users">
          <a href="{{ route('admin.users.index') }}" class="sidebar-subitem {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
            {{ __('Liste des utilisateurs')  }}
          </a>
          <a href="{{ route('admin.users.create') }}" class="sidebar-subitem {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
            {{ __('Créer utilisateur')  }}
          </a>
        </div>

        {{-- Academic Management --}}
        <div class="sidebar-item {{ request()->routeIs('admin.classes.*', 'admin.subjects.*') ? 'active open' : '' }}" data-toggle="sub-academic">
          <span class="sidebar-icon"><i data-lucide="book-open"></i></span>
          <span class="sidebar-label">{{ __('Académique')  }}</span>
          <svg class="sidebar-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </div>
        <div class="sidebar-submenu {{ request()->routeIs('admin.classes.*', 'admin.subjects.*') ? 'open' : '' }}" id="sub-academic">
          <a href="{{ route('admin.classes.index') }}" class="sidebar-subitem {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
            {{ __('Classes') }}
          </a>
          <a href="{{ route('admin.subjects.index') }}" class="sidebar-subitem {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
            {{ __('Matières')  }}
          </a>
        </div>

        {{-- Finance --}}
        <a href="{{ route('admin.finance.index') }}" class="sidebar-item {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="wallet-2"></i></span>
          <span class="sidebar-label">{{ __('Finances')  }}</span>
        </a>

        {{-- Announcements --}}
        <a href="{{ route('admin.announcements.index') }}" class="sidebar-item {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="megaphone"></i></span>
          <span class="sidebar-label">{{ __('Annonces')  }}</span>
        </a>

        {{-- Teachers Management --}}
        <a href="{{ route('admin.teachers.index') }}" class="sidebar-item {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="users"></i></span>
          <span class="sidebar-label">{{ __('Enseignants') }}</span>
        </a>

        {{-- Students Management --}}
        <a href="{{ route('admin.students.index') }}" class="sidebar-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="user-check"></i></span>
          <span class="sidebar-label">{{ __('Étudiants' )}}</span>
        </a>

        {{-- Attendance Management --}}
        <a href="{{ route('admin.attendance.index') }}" class="sidebar-item {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="check-square"></i></span>
          <span class="sidebar-label">{{ __('Absences') }}</span>
        </a>

        {{-- Schedule Management --}}
        <a href="{{ route('admin.schedule.index') }}" class="sidebar-item {{ request()->routeIs('admin.schedule.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="calendar"></i></span>
          <span class="sidebar-label">{{ __('Emploi du Temps') }}</span>
        </a>

        {{-- Teacher Assignments --}}
        <a href="{{ route('admin.assignments.index') }}" class="sidebar-item {{ request()->routeIs('admin.assignments.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="users-2"></i></span>
          <span class="sidebar-label">{{ __('Affectations') }}</span>
        </a>

        {{-- Fees Management --}}
        <a href="{{ route('admin.fees.index') }}" class="sidebar-item {{ request()->routeIs('admin.fees.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="dollar-sign"></i></span>
          <span class="sidebar-label">{{ __('Frais') }}</span>
        </a>

        {{-- Payment Module (Mobile Money) --}}
        <a href="{{ route('schoolpay.admin.dashboard') }}" class="sidebar-item {{ request()->routeIs('schoolpay.admin.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="credit-card"></i></span>
          <span class="sidebar-label">{{ __('Paiements Mobile Money') }}</span>
        </a>

       

        {{-- Reports --}}
        <a href="{{ route('admin.reports.dashboard') }}" class="sidebar-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="file-text"></i></span>
          <span class="sidebar-label">{{ __('Rapports') }}</span>
        </a>
       

        {{-- Roles & Permissions --}}
        <a href="{{ route('admin.roles.index') }}" class="sidebar-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="lock"></i></span>
          <span class="sidebar-label">{{ __('Rôles') }}</span>
        </a>

        {{-- Settings --}}
        <a href="{{ route('admin.settings.edit') }}" class="sidebar-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="settings"></i></span>
          <span class="sidebar-label">{{ __('Paramètres')  }}</span>
        </a>
      </div>
    @endif

    {{-- ═══ PROFESSOR PRINCIPAL SECTION ═══ --}}
    @if(auth()->user()?->isProfPrincipal())
      <div class="sidebar-section">
        <span class="sidebar-section-label" style="color: #fbbf24; font-weight: 600;">
          <i class="fas fa-crown me-1"></i>{{ __('Professeur Principal')  }}
        </span>

       

        {{-- Note: Templates, Grades, Bulletins, Export, and Progress management
             are now handled through the Bulletin NG system below --}}

        {{-- Bulletin NG (Système nouvelle génération) --}}
        <a href="{{ route('teacher.bulletin_ng.index') }}" class="sidebar-item {{ request()->routeIs('teacher.bulletin_ng.*') ? 'active' : '' }}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 0.5rem; margin-top: 1rem;">
          <span class="sidebar-icon"><i data-lucide="file-text" style="color: #fbbf24;"></i></span>
          <span class="sidebar-label" style="font-weight: 600;">{{ __('Bulletins') }}</span>
        </a>
      </div>
    @endif

    {{-- ═══ TEACHER SECTION ═══ --}}
    @if(auth()->user()?->isTeacher())
      <div class="sidebar-section">
        <span class="sidebar-section-label">{{ __('Enseignement') }}</span>

        {{-- Dashboard --}}
        <a href="{{ route('teacher.dashboard') }}" class="sidebar-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="layout-dashboard"></i></span>
          <span class="sidebar-label">{{ __('Tableau de bord')  }}</span>
        </a>

        {{-- Course Materials --}}
        <a href="{{ route('teacher.materials.index') }}" class="sidebar-item {{ request()->routeIs('teacher.materials.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="file-text"></i></span>
          <span class="sidebar-label">{{ __('Ressources Pédagogiques') }}</span>
        </a>

        {{-- Parent Management --}}
        <a href="{{ route('teacher.parent-management.index', auth()->user()->teacher?->head_class_id ?? 0) }}" class="sidebar-item {{ request()->routeIs('teacher.parent-management.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="users-2"></i></span>
          <span class="sidebar-label">{{ __('Gestion Parents')}}</span>
        </a>

        {{-- Quiz --}}
        <a href="{{ route('teacher.quizzes.index') }}" class="sidebar-item {{ request()->routeIs('teacher.quizzes.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="help-circle"></i></span>
          <span class="sidebar-label">{{ __('Quiz') }}</span>
        </a>

        {{-- Schedule --}}
        <a href="{{ route('teacher.schedule') }}" class="sidebar-item {{ request()->routeIs('teacher.schedule') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="calendar"></i></span>
          <span class="sidebar-label">{{ __('Emploi du Temps') }}</span>
        </a>

        {{-- Student Absences --}}
        <a href="{{ route('teacher.student-absences.index') }}" class="sidebar-item {{ request()->routeIs('teacher.student-absences.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="calendar-times"></i></span>
          <span class="sidebar-label">{{ __('Absences Étudiants') }}</span>
        </a>

        {{-- Attendance --}}
        <a href="{{ route('teacher.attendance.index') }}" class="sidebar-item {{ request()->routeIs('teacher.attendance.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="check-square"></i></span>
          <span class="sidebar-label">{{ __('Absences')}}</span>
        </a>

        {{-- Courses --}}
        <a href="{{ route('teacher.courses') }}" class="sidebar-item {{ request()->routeIs('teacher.courses') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="book"></i></span>
          <span class="sidebar-label">{{ __( 'Cours') }}</span>
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
          <span class="sidebar-label">{{ __('Tableau Avancé') }}</span>
        </a>
        @endif
      </div>
    @endif

    {{-- ═══ PARENT SECTION ═══ --}}
    @if(auth()->user()?->isParent())
      <div class="sidebar-section">
        <span class="sidebar-section-label">{{ __('Parent')}}</span>

        <a href="{{ route('parent.dashboard') }}" class="sidebar-item {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="layout-dashboard"></i></span>
          <span class="sidebar-label">{{ __('Tableau de bord')}}</span>
        </a>

        {{-- Children & Monitoring --}}
        <div class="sidebar-item {{ request()->routeIs('parent.children*', 'parent.monitoring*') ? 'active open' : '' }}" data-toggle="sub-children">
          <span class="sidebar-icon"><i data-lucide="users"></i></span>
          <span class="sidebar-label">{{ __('Enfants')}}</span>
          <svg class="sidebar-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </div>
        <div class="sidebar-submenu {{ request()->routeIs('parent.children*', 'parent.monitoring*') ? 'open' : '' }}" id="sub-children">
          <a href="{{ route('parent.children') }}" class="sidebar-subitem {{ request()->routeIs('parent.children') ? 'active' : '' }}">
            {{ __('Mes enfants') }}
          </a>
          <a href="{{ route('parent.monitoring.index') }}" class="sidebar-subitem {{ request()->routeIs('parent.monitoring.*') ? 'active' : '' }}">
            {{ __('Suivi Académique') }}
          </a>
        </div>

        

        {{-- Payments Menu (All Payment Views) --}}
        <div class="sidebar-item {{ request()->routeIs('parent.payments.*', 'parent.mobile-money.*', 'schoolpay.parent.*') ? 'active open' : '' }}" data-toggle="sub-payment">
          <span class="sidebar-icon"><i data-lucide="credit-card"></i></span>
          <span class="sidebar-label">{{ __('Paiement') }}</span>
          <svg class="sidebar-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </div>
        <div class="sidebar-submenu {{ request()->routeIs('parent.payments.*', 'parent.mobile-money.*', 'schoolpay.parent.*') ? 'open' : '' }}" id="sub-payment">
          {{-- SchoolPay Interface --}}
          <a href="{{ route('schoolpay.parent.index') }}" class="sidebar-subitem {{ request()->routeIs('schoolpay.parent.*') ? 'active' : '' }}">
            <span class="text-xs"><i class="fas fa-credit-card me-1"></i>{{ __('Paiement SchoolPay') }}</span>
          </a>

          <div class="h-px bg-gray-200 my-2"></div>

          {{-- Payment Dashboard Links --}}
          <a href="{{ route('parent.payments.index') }}" class="sidebar-subitem {{ request()->routeIs('parent.payments.index') ? 'active' : '' }}">
            <span class="text-xs">{{ __('Tableau de Bord') }}</span>
          </a>
          
          <a href="{{ route('parent.payments.mobile-money') }}" class="sidebar-subitem {{ request()->routeIs('parent.payments.mobile-money') ? 'active' : '' }}">
            <span class="text-xs">{{ __('Mobile Money') }}</span>
          </a>

          <a href="{{ route('parent.payments.receipts') }}" class="sidebar-subitem {{ request()->routeIs('parent.payments.receipts') ? 'active' : '' }}">
            <span class="text-xs">🧾 {{ __('Reçus') }}</span>
          </a>

          <div class="h-px bg-gray-200 my-2"></div>

          {{-- Children Payments --}}
          @if(auth()->user()->children && auth()->user()->children->count() > 0)
            <span class="text-xs px-3 py-2 text-gray-600 font-semibold uppercase tracking-wide"> {{ __('Enfants') }}</span>
            @foreach(auth()->user()->children as $child)
              <a href="{{ route('parent.child.payments', $child) }}" class="sidebar-subitem pl-6 {{ request()->routeIs('parent.child.payments') && request()->route('student') && request()->route('student')->id === $child->id ? 'active' : '' }}">
                <span class="text-xs">💳 {{ $child->user->name ?? ($child->first_name . ' ' . $child->last_name) }}</span>
              </a>
            @endforeach  
          @endif

          <div class="h-px bg-gray-200 my-2"></div>

          {{-- Mobile Money Payment for Each Child --}}
          @if(auth()->user()->children && auth()->user()->children->count() > 0)
            <span class="text-xs px-3 py-2 text-orange-600 font-semibold uppercase tracking-wide">🏦 {{ __('Pay Now') ?? 'Payer Maintenant' }}</span>
            @foreach(auth()->user()->children as $child)
              <a href="{{ route('parent.mobile-money.show', $child) }}" class="sidebar-subitem pl-6 {{ request()->routeIs('parent.mobile-money.show') && request()->route('student') && request()->route('student')->id === $child->id ? 'active' : '' }}">
                <span class="text-xs">💵 {{ $child->user->name ?? ($child->first_name . ' ' . $child->last_name) }}</span>
              </a>
            @endforeach
          @endif
        </div>

        {{-- Appointments --}}
        <a href="{{ route('parent.appointments.index') }}" class="sidebar-item {{ request()->routeIs('parent.appointments.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="calendar"></i></span>
          <span class="sidebar-label">{{ __('nav.appointments') ?? 'Rendez-vous' }}</span>
        </a>
      </div>
    @endif

    {{-- ═══ STUDENT SECTION ═══ --}}
    @if(auth()->user()?->isStudent())
      <div class="sidebar-section">
        <span class="sidebar-section-label">{{ __('Étudiant' )}}</span>

        <a href="{{ route('student.dashboard') }}" class="sidebar-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="layout-dashboard"></i></span>
          <span class="sidebar-label">{{ __('Tableau de bord') }}</span>
        </a>

        {{-- Academic Info --}}
        <div class="sidebar-item {{ request()->routeIs('student.marks', 'student.grades*') ? 'active open' : '' }}" data-toggle="sub-academic-student">
          <span class="sidebar-icon"><i data-lucide="award"></i></span>
          <span class="sidebar-label">{{ __('Scolarité') }}</span>
          <svg class="sidebar-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </div>
        <div class="sidebar-submenu {{ request()->routeIs('student.marks', 'student.grades*') ? 'open' : '' }}" id="sub-academic-student">
          <a href="{{ route('student.marks') }}" class="sidebar-subitem {{ request()->routeIs('student.marks') ? 'active' : '' }}">
            {{ __('Notes') }}
          </a>
          <a href="{{ route('student.progress.index') }}" class="sidebar-subitem {{ request()->routeIs('student.progress.*') ? 'active' : '' }}">
            {{ __('Progression') }}
          </a>
        </div>

        <a href="{{ route('student.attendance') }}" class="sidebar-item {{ request()->routeIs('student.attendance') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="calendar-check"></i></span>
          <span class="sidebar-label">{{ __('Présences') }}</span>
        </a>

        <a href="{{ route('student.schedule') }}" class="sidebar-item {{ request()->routeIs('student.schedule') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="clock"></i></span>
          <span class="sidebar-label">{{ __('Emploi du temps') }}</span>
        </a>

        {{-- E-Learning --}}
        <a href="{{ route('student.e-learning.index') }}" class="sidebar-item {{ request()->routeIs('student.e-learning.*') ? 'active' : '' }}">
          <span class="sidebar-icon"><i data-lucide="book-open"></i></span>
          <span class="sidebar-label">{{ __('E-Learning') }}</span>
        </a>

        {{-- Quiz Management --}}
        <div class="sidebar-item {{ request()->routeIs('student.quiz.*') ? 'active open' : '' }}" data-toggle="sub-quiz">
          <span class="sidebar-icon"><i data-lucide="help-circle"></i></span>
          <span class="sidebar-label">{{ __('Quiz') }}</span>
          <svg class="sidebar-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </div>
        <div class="sidebar-submenu {{ request()->routeIs('student.quiz.*') ? 'active open' : '' }}" id="sub-quiz">
          <a href="{{ route('student.quiz-take.index') }}" class="sidebar-subitem {{ request()->routeIs('student.quiz-take.*') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list me-2"></i>{{ __('Passer Quiz') }}
          </a>
          <a href="{{ route('student.quiz-result.index') }}" class="sidebar-subitem {{ request()->routeIs('student.quiz-result.*') ? 'active' : '' }}">
            <i class="fas fa-chart-bar me-2"></i>{{ __('Résultats Quiz') }}
          </a>
        </div>

        {{-- Assignments & Courses --}}
        @if(Route::has('student.assignments') || Route::has('student.courses.index'))
          <div class="sidebar-item {{ request()->routeIs('student.assignments*', 'student.courses*') ? 'active open' : '' }}" data-toggle="sub-learning">
            <span class="sidebar-icon"><i data-lucide="book-open"></i></span>
            <span class="sidebar-label">{{ __('Apprentissage') }}</span>
            <svg class="sidebar-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 18 15 12 9 6"/>
            </svg>
          </div>
          <div class="sidebar-submenu {{ request()->routeIs('student.assignments*', 'student.courses*') ? 'open' : '' }}" id="sub-learning">
            @if(Route::has('student.assignments'))
              <a href="{{ route('student.assignments') }}" class="sidebar-subitem {{ request()->routeIs('student.assignments*') ? 'active' : '' }}">
                {{ __('Devoirs') }}
              </a>
            @endif
            @if(Route::has('student.courses.index'))
              <a href="{{ route('student.courses.index') }}" class="sidebar-subitem {{ request()->routeIs('student.courses*') ? 'active' : '' }}">
                {{ __('Cours') }}
              </a>
            @endif
          </div>
        @endif

        
      </div>
    @endif

    {{-- ═══ COMMON SECTION ═══ --}}
    <div class="sidebar-section">
      <span class="sidebar-section-label">{{ __('Communication') }}</span>

      <a href="{{ route('chat.index') }}" class="sidebar-item {{ request()->routeIs('chat.*') ? 'active' : '' }}">
        <span class="sidebar-icon"><i data-lucide="message-circle"></i></span>
        <span class="sidebar-label">{{ __('Chat') }}</span>
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
        <span class="sidebar-label">{{ __('Notifications')}}</span>
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
