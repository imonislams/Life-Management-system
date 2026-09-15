<x-app-layout>
    <x-slot name="title">Dashboard - Personal Finance Management System</x-slot>

    <div class="card">
        <h1 class="card-title">Personal Finance Management System</h1>
        <p class="card-subtitle">Welcome, {{ Auth::user()->name }}</p>
    </div>
</x-app-layout>
