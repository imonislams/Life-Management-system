<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        // Apply the authenticated user's saved appearance preferences.
        // Falls back to the stock design when no settings row exists yet.
        $layoutSettings = Auth::check() ? \App\Models\Setting::where('user_id', Auth::id())->first() : null;
        $primaryColor = $layoutSettings->primary_color ?? '#2563eb';
        $theme = $layoutSettings->theme ?? 'light';
        $compactMode = (bool) ($layoutSettings->compact_mode ?? false);
    @endphp
    <title>{{ $title ?? 'Personal Life Management System' }}</title>
    @php
        // The user's configured application name (sidebar brand + document title).
        $brandName = \App\Support\UserPreference::appName();
    @endphp
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if($primaryColor !== '#2563eb' || $compactMode)
        <style>
            :root {
                --primary-color: {{ $primaryColor }};
                @if($compactMode)
                --topbar-height: 56px;
                @endif
            }
            @if($compactMode)
            .card { padding: 1.125rem; margin-bottom: 1.125rem; }
            .summary-card { padding: 1rem; }
            .content-area { padding: 1.25rem; }
            @endif
        </style>
    @endif
    @if($theme === 'dark')
        <style>
            body { background-color: #0f172a; color: #e2e8f0; }
            .sidebar, .topbar, .card, .summary-card { background-color: #1e293b; border-color: #334155; }
            .card-title, .page-header-title, .summary-card-value, .empty-state-title, h1, h2, h3, h4 { color: #e2e8f0; }
            .sidebar-brand { color: {{ $primaryColor }}; }
            .data-table th { background-color: #0f172a; }
            .data-table td { color: #e2e8f0; border-color: #334155; }
            .nav-submenu { border-left-color: #334155; }
            .filter-bar { background-color: #0f172a; }
            .alert-success { background-color: #052e16; border-color: #166534; color: #bbf7d0; }
            .alert-danger { background-color: #450a0a; border-color: #991b1b; color: #fecaca; }
            .form-control { background-color: #0f172a; color: #e2e8f0; border-color: #334155; }
            .btn-secondary, .logout-btn { background-color: #334155; color: #e2e8f0; border-color: #475569; }
            .btn-secondary:hover, .logout-btn:hover { background-color: #475569; }
            .nav-link:hover, .submenu-link:hover { background-color: #334155; color: #e2e8f0; }
            .nav-link.active, .submenu-link.active { background-color: #1e3a8a; color: #bfdbfe; }
            .nav-accordion-toggle:hover { background-color: #334155; color: #e2e8f0; }
            .nav-accordion-toggle.active { color: {{ $primaryColor }}; background-color: #0f172a; }
            .nav-submenu { border-left-color: #334155; }
            .card-subtitle, .filter-label, .empty-state, .user-name { color: #94a3b8; }
            .sidebar-brand { color: {{ $primaryColor }}; }
            .user-avatar { background-color: #1e3a8a; color: #bfdbfe; border-color: #1e40af; }
            .filter-bar { background-color: #0f172a; border-color: #334155; }
            .data-table tbody tr:hover { background-color: #334155; }
            .badge-income { background-color: #052e16; color: #bbf7d0; border-color: #166534; }
            .badge-expense { background-color: #450a0a; color: #fecaca; border-color: #991b1b; }
            .badge-active { background-color: #172554; color: #bfdbfe; border-color: #1e40af; }
            .badge-inactive { background-color: #334155; color: #cbd5e1; border-color: #475569; }
            .alert-success { background-color: #052e16; border-color: #166534; color: #bbf7d0; }
            .alert-danger { background-color: #450a0a; border-color: #991b1b; color: #fecaca; }
            .summary-card-title { color: #94a3b8; }
            .chart-container canvas { filter: invert(0.92) hue-rotate(180deg); }
            /* Inline light-tinted panels used across pages */
            [style*="background: #f8fafc"], [style*="background-color: #f8fafc"],
            [style*="background: #eff6ff"], [style*="background-color: #eff6ff"] {
                background-color: #0f172a !important;
            }
            [style*="background-color: #e2e8f0"] { background-color: #334155 !important; }
            .pagination-wrapper a, .pagination-wrapper span { color: #e2e8f0; }
        </style>
    @elseif($theme === 'system')
        <style>
            @media (prefers-color-scheme: dark) {
                body { background-color: #0f172a; color: #e2e8f0; }
                .sidebar, .topbar, .card, .summary-card { background-color: #1e293b; border-color: #334155; }
                .card-title, .page-header-title, .summary-card-value, .empty-state-title, h1, h2, h3, h4 { color: #e2e8f0; }
                .data-table th { background-color: #0f172a; }
                .data-table td { color: #e2e8f0; border-color: #334155; }
                .data-table tbody tr:hover { background-color: #334155; }
                .form-control { background-color: #0f172a; color: #e2e8f0; border-color: #334155; }
                .btn-secondary, .logout-btn { background-color: #334155; color: #e2e8f0; border-color: #475569; }
                .badge-income { background-color: #052e16; color: #bbf7d0; border-color: #166534; }
                .badge-expense { background-color: #450a0a; color: #fecaca; border-color: #991b1b; }
                .badge-active { background-color: #172554; color: #bfdbfe; border-color: #1e40af; }
                .badge-inactive { background-color: #334155; color: #cbd5e1; border-color: #475569; }
                .alert-success { background-color: #052e16; border-color: #166534; color: #bbf7d0; }
                .alert-danger { background-color: #450a0a; border-color: #991b1b; color: #fecaca; }
                .filter-bar { background-color: #0f172a; border-color: #334155; }
                .nav-submenu { border-left-color: #334155; }
                [style*="background: #f8fafc"], [style*="background-color: #f8fafc"],
                [style*="background: #eff6ff"], [style*="background-color: #eff6ff"] {
                    background-color: #0f172a !important;
                }
            }
        </style>
    @endif
</head>
<body>
    <div class="app-container">
        <!-- Sidebar Backdrop for Mobile -->
        <div id="sidebarBackdrop" class="sidebar-backdrop"></div>

        <!-- Sidebar Navigation -->
        <aside id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('dashboard') }}" class="sidebar-brand">
                    @if($layoutSettings && $layoutSettings->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($layoutSettings->logo_path))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($layoutSettings->logo_path) }}" alt="{{ $brandName }}" style="height: 24px; width: auto;">
                    @else
                        <span class="brand-icon">❖</span>
                    @endif
                    {{ \Illuminate\Support\Str::limit($brandName, 22) }}
                </a>
            </div>

            <nav class="sidebar-nav" id="sidebarNav">
                <!-- Main Nav -->
                <div class="nav-section">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="nav-icon">📊</span> Dashboard
                    </a>
                </div>

                <!-- Money Management Collapsible Accordion Parent -->
                @php
                    $isMoneyManagementActive = request()->routeIs([
                        'money-management.*',
                        'income.*',
                        'salary.*',
                        'expenses.*',
                        'savings.*',
                        'recurring-transactions.*'
                    ]);
                @endphp

                <div class="nav-section accordion-section {{ $isMoneyManagementActive ? 'expanded' : '' }}" id="moneyManagementSection">
                    <button type="button" class="nav-accordion-toggle {{ $isMoneyManagementActive ? 'active' : '' }}" id="moneyManagementToggle" aria-expanded="{{ $isMoneyManagementActive ? 'true' : 'false' }}">
                        <span class="nav-accordion-label">
                            <span class="nav-icon">💼</span> Money Management
                        </span>
                        <span class="accordion-chevron">▼</span>
                    </button>

                    <div class="nav-submenu" id="moneyManagementSubmenu">
                        <a href="{{ route('money-management.index') }}" class="submenu-link {{ request()->routeIs('money-management.*') ? 'active' : '' }}">
                            <span class="nav-icon">📊</span> Analytics
                        </a>
                        <a href="{{ route('income.index') }}" class="submenu-link {{ request()->routeIs('income.*') ? 'active' : '' }}">
                            <span class="nav-icon">📈</span> Income
                        </a>
                        <a href="{{ route('salary.index') }}" class="submenu-link {{ request()->routeIs('salary.*') ? 'active' : '' }}">
                            <span class="nav-icon">💵</span> Salary
                        </a>
                        <a href="{{ route('expenses.index') }}" class="submenu-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                            <span class="nav-icon">📉</span> Expenses
                        </a>
                        <a href="{{ route('savings.index') }}" class="submenu-link {{ request()->routeIs('savings.*') ? 'active' : '' }}">
                            <span class="nav-icon">🏦</span> Savings
                        </a>
                        <a href="{{ route('recurring-transactions.index') }}" class="submenu-link {{ request()->routeIs('recurring-transactions.*') ? 'active' : '' }}">
                            <span class="nav-icon">🔄</span> Recurring Finance
                        </a>
                    </div>
                </div>

                <!-- Daily Activities + related personal modules -->
                <div class="nav-section">
                    <a href="{{ route('daily-activities.index') }}" class="nav-link {{ request()->routeIs('daily-activities.*') ? 'active' : '' }}">
                        <span class="nav-icon">📓</span> Daily Activities
                    </a>
                    <a href="{{ route('routine.index') }}" class="nav-link {{ request()->routeIs('routine.*', 'routine-occurrences.*') ? 'active' : '' }}">
                        <span class="nav-icon">⏰</span> Daily Routine
                    </a>
                    <a href="{{ route('habits.index') }}" class="nav-link {{ request()->routeIs('habits.*', 'habit-activities.*') ? 'active' : '' }}">
                        <span class="nav-icon">⚡</span> Habits
                    </a>
                </div>

                <!-- Personal Growth Collapsible Accordion Parent -->
                @php
                    $isPersonalGrowthActive = request()->routeIs([
                        'goals.*',
                        'progress.*'
                    ]);
                @endphp
                <div class="nav-section accordion-section {{ $isPersonalGrowthActive ? 'expanded' : '' }}" id="personalGrowthSection">
                    <button type="button" class="nav-accordion-toggle {{ $isPersonalGrowthActive ? 'active' : '' }}" id="personalGrowthToggle" aria-expanded="{{ $isPersonalGrowthActive ? 'true' : 'false' }}">
                        <span class="nav-accordion-label">
                            <span class="nav-icon">🌱</span> Personal Growth
                        </span>
                        <span class="accordion-chevron">▼</span>
                    </button>

                    <div class="nav-submenu" id="personalGrowthSubmenu">
                        <a href="{{ route('goals.index') }}" class="submenu-link {{ request()->routeIs('goals.*') ? 'active' : '' }}">
                            <span class="nav-icon">🎯</span> Goals
                        </a>
                        <a href="{{ route('progress.index') }}" class="submenu-link {{ request()->routeIs('progress.*') ? 'active' : '' }}">
                            <span class="nav-icon">📈</span> Progress
                        </a>
                    </div>
                </div>

                <!-- Important Dates Collapsible Accordion Parent -->
                @php
                    $isImportantDatesActive = request()->routeIs([
                        'calendar.*',
                        'events.*'
                    ]);
                @endphp
                <div class="nav-section accordion-section {{ $isImportantDatesActive ? 'expanded' : '' }}" id="importantDatesSection">
                    <button type="button" class="nav-accordion-toggle {{ $isImportantDatesActive ? 'active' : '' }}" id="importantDatesToggle" aria-expanded="{{ $isImportantDatesActive ? 'true' : 'false' }}">
                        <span class="nav-accordion-label">
                            <span class="nav-icon">📌</span> Important Dates
                        </span>
                        <span class="accordion-chevron">▼</span>
                    </button>

                    <div class="nav-submenu" id="importantDatesSubmenu">
                        <a href="{{ route('calendar.index') }}" class="submenu-link {{ request()->routeIs('calendar.*') ? 'active' : '' }}">
                            <span class="nav-icon">🗓️</span> Calendar
                        </a>
                        <a href="{{ route('events.index') }}" class="submenu-link {{ request()->routeIs('events.*') ? 'active' : '' }}">
                            <span class="nav-icon">🎉</span> Events
                        </a>
                    </div>
                </div>

                <!-- AI Assistant (100% local, no paid API) -->
                <div class="nav-section">
                    <a href="{{ route('ai.assistant') }}" class="nav-link {{ request()->routeIs('ai.*') ? 'active' : '' }}">
                        <span class="nav-icon">🤖</span> AI Assistant
                    </a>
                </div>

            </nav>

            <!-- Fixed sidebar footer: Settings stays pinned to the bottom while
                 the navigation area above scrolls independently. -->
            <div class="sidebar-footer">
                <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <span class="nav-icon">⚙️</span> Settings
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="main-wrapper">
            <!-- Topbar Header -->
            <header class="topbar">
                <div class="topbar-left">
                    <button id="sidebarToggle" class="mobile-toggle-btn" aria-label="Toggle Navigation">
                        ☰
                    </button>
                    <h2 class="page-header-title">{{ $pageTitle ?? 'Dashboard' }}</h2>
                </div>

                @php
                    $topbarUser = Auth::user();
                    $hasAvatar = $topbarUser && $topbarUser->profile_photo_path
                        && \Illuminate\Support\Facades\Storage::disk('public')->exists($topbarUser->profile_photo_path);
                @endphp
                <div class="user-profile">
                    @if($hasAvatar)
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($topbarUser->profile_photo_path) }}"
                            alt="{{ $topbarUser->name }}"
                            class="user-avatar"
                            style="object-fit: cover;"
                        >
                    @else
                        <span class="user-avatar">{{ strtoupper(substr($topbarUser->name ?? 'U', 0, 1)) }}</span>
                    @endif
                    <a href="{{ route('settings.profile') }}" class="user-name" style="text-decoration: none; color: inherit;">
                        {{ $topbarUser->name ?? 'User' }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-btn">Logout</button>
                    </form>
                </div>
            </header>

            <!-- Main Page View Content -->
            <main class="content-area">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Global alert system disabled during restoration (see docs/CHANGELOG.md).
         The components are kept on disk but are no longer mounted globally so the
         application behaves exactly as it did before the alert integration. -->

    <!-- JavaScript for Mobile Sidebar Toggle & Accordion Navigation -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mobile Sidebar Toggle
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const backdrop = document.getElementById('sidebarBackdrop');

            if (toggleBtn && sidebar && backdrop) {
                toggleBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('open');
                    backdrop.classList.toggle('open');
                });

                backdrop.addEventListener('click', function () {
                    sidebar.classList.remove('open');
                    backdrop.classList.remove('open');
                });
            }

            // Accordion Toggle Logic for all accordion buttons
            const accordionToggles = document.querySelectorAll('.nav-accordion-toggle');
            accordionToggles.forEach(function (toggle) {
                toggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    const section = toggle.closest('.accordion-section');
                    if (section) {
                        const isExpanded = section.classList.toggle('expanded');
                        toggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
                    }
                });
            });
        });
    </script>
</body>
</html>
