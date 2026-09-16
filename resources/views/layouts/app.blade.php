<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Personal Life Management System' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-container">
        <!-- Sidebar Backdrop for Mobile -->
        <div id="sidebarBackdrop" class="sidebar-backdrop"></div>

        <!-- Sidebar Navigation -->
        <aside id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('dashboard') }}" class="sidebar-brand">
                    <span class="brand-icon">❖</span> Life System
                </a>
            </div>

            <nav class="sidebar-nav">
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
                            <span class="nav-icon">📊</span> Analytics Dashboard
                        </a>
                        <a href="{{ route('income.index') }}" class="submenu-link {{ request()->routeIs('income.*') ? 'active' : '' }}">
                            <span class="nav-icon">📈</span> Income
                        </a>
                        <a href="{{ route('salary.index') }}" class="submenu-link {{ request()->routeIs('salary.*') ? 'active' : '' }}">
                            <span class="nav-icon">💵</span> Salary
                        </a>
                        <a href="{{ route('expenses.index') }}" class="submenu-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                            <span class="nav-icon">📉</span> Expense
                        </a>
                        <a href="{{ route('savings.index') }}" class="submenu-link {{ request()->routeIs('savings.*') ? 'active' : '' }}">
                            <span class="nav-icon">🏦</span> Savings
                        </a>
                        <a href="{{ route('recurring-transactions.index') }}" class="submenu-link {{ request()->routeIs('recurring-transactions.*') ? 'active' : '' }}">
                            <span class="nav-icon">🔄</span> Recurring Finance
                        </a>
                    </div>
                </div>

                <!-- Daily Management Collapsible Accordion Parent -->
                @php
                    $isDailyManagementActive = request()->routeIs([
                        'tasks.*',
                        'habits.*',
                        'routine.*'
                    ]);
                @endphp

                <div class="nav-section accordion-section {{ $isDailyManagementActive ? 'expanded' : '' }}" id="dailyManagementSection">
                    <button type="button" class="nav-accordion-toggle {{ $isDailyManagementActive ? 'active' : '' }}" id="dailyManagementToggle" aria-expanded="{{ $isDailyManagementActive ? 'true' : 'false' }}">
                        <span class="nav-accordion-label">
                            <span class="nav-icon">📅</span> Daily Management
                        </span>
                        <span class="accordion-chevron">▼</span>
                    </button>

                    <div class="nav-submenu" id="dailyManagementSubmenu">
                        <a href="{{ route('tasks.index') }}" class="submenu-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                            <span class="nav-icon">✅</span> Tasks
                        </a>
                        <a href="{{ route('habits.index') }}" class="submenu-link {{ request()->routeIs('habits.*') ? 'active' : '' }}">
                            <span class="nav-icon">⚡</span> Habits
                        </a>
                        <a href="{{ route('routine.index') }}" class="submenu-link {{ request()->routeIs('routine.*') ? 'active' : '' }}">
                            <span class="nav-icon">⏰</span> Daily Routine
                        </a>
                    </div>
                </div>
            </nav>
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

                <div class="user-profile">
                    <span class="user-avatar">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</span>
                    <span class="user-name">{{ Auth::user()->name ?? 'User' }}</span>
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
