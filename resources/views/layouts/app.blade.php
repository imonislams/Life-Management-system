<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Personal Finance Management System' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('dashboard') }}" class="sidebar-brand">Finance App</a>
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('income.index') }}" class="nav-link {{ request()->routeIs('income.*') ? 'active' : '' }}">
                    Income
                </a>
                <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                    Expenses
                </a>
                <a href="{{ route('salary.index') }}" class="nav-link {{ request()->routeIs('salary.*') ? 'active' : '' }}">
                    Salary
                </a>
            </nav>
        </aside>

        <!-- Main Wrapper -->
        <div class="main-wrapper">
            <!-- Topbar -->
            <header class="topbar">
                <div class="user-profile">
                    <span class="user-name">{{ Auth::user()->name ?? 'User' }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-btn">Logout</button>
                    </form>
                </div>
            </header>

            <!-- Main Content -->
            <main class="content-area">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
