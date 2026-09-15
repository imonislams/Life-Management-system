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

                <!-- Money Management -->
                <div class="nav-section">
                    <div class="nav-section-title">Money Management</div>
                    <a href="{{ route('income.index') }}" class="nav-link {{ request()->routeIs('income.*') ? 'active' : '' }}">
                        <span class="nav-icon">📈</span> Income
                    </a>
                    <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                        <span class="nav-icon">📉</span> Expenses
                    </a>
                    <a href="{{ route('salary.index') }}" class="nav-link {{ request()->routeIs('salary.*') ? 'active' : '' }}">
                        <span class="nav-icon">💵</span> Salary
                    </a>
                    <div class="nav-link disabled">
                        <span class="nav-icon">🏦</span> Savings <span class="nav-badge">Soon</span>
                    </div>
                </div>

                <!-- Daily Life -->
                <div class="nav-section">
                    <div class="nav-section-title">Daily Life</div>
                    <div class="nav-link disabled">
                        <span class="nav-icon">📅</span> Routine <span class="nav-badge">Soon</span>
                    </div>
                    <div class="nav-link disabled">
                        <span class="nav-icon">✅</span> Tasks <span class="nav-badge">Soon</span>
                    </div>
                    <div class="nav-link disabled">
                        <span class="nav-icon">🎯</span> Goals <span class="nav-badge">Soon</span>
                    </div>
                </div>

                <!-- Habits -->
                <div class="nav-section">
                    <div class="nav-section-title">Habits</div>
                    <div class="nav-link disabled">
                        <span class="nav-icon">🔥</span> Habits <span class="nav-badge">Soon</span>
                    </div>
                </div>

                <!-- Prayer -->
                <div class="nav-section">
                    <div class="nav-section-title">Prayer</div>
                    <div class="nav-link disabled">
                        <span class="nav-icon">🕌</span> Prayer Tracking <span class="nav-badge">Soon</span>
                    </div>
                </div>

                <!-- Reminders -->
                <div class="nav-section">
                    <div class="nav-section-title">Reminders</div>
                    <div class="nav-link disabled">
                        <span class="nav-icon">🔔</span> Reminders <span class="nav-badge">Soon</span>
                    </div>
                </div>

                <!-- Special Days -->
                <div class="nav-section">
                    <div class="nav-section-title">Special Days</div>
                    <div class="nav-link disabled">
                        <span class="nav-icon">🎉</span> Special Days <span class="nav-badge">Soon</span>
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

    <!-- Vanilla JavaScript for Mobile Sidebar Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
        });
    </script>
</body>
</html>
