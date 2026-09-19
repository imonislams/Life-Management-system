@php
/**
* Settings section navigation. Rendered identically on every settings page
* so the section tabs stay consistent with the rest of the app design.
*/
$sections = [
['route' => 'settings.index', 'pattern' => 'settings.index', 'label' => 'Overview', 'icon' => '⚙️'],
['route' => 'settings.general', 'pattern' => 'settings.general', 'label' => 'General', 'icon' => '🏷️'],
['route' => 'settings.currency', 'pattern' => 'settings.currency', 'label' => 'Currency', 'icon' => '💱'],
['route' => 'settings.salary', 'pattern' => 'settings.salary', 'label' => 'Salary', 'icon' => '💵'],
['route' => 'settings.savings', 'pattern' => 'settings.savings', 'label' => 'Savings', 'icon' => '🏦'],
['route' => 'settings.income', 'pattern' => 'settings.income', 'label' => 'Income', 'icon' => '📈'],
['route' => 'settings.expense', 'pattern' => 'settings.expense', 'label' => 'Expense', 'icon' => '📉'],
['route' => 'settings.recurring', 'pattern' => 'settings.recurring', 'label' => 'Recurring', 'icon' => '🔄'],
['route' => 'settings.notifications', 'pattern' => 'settings.notifications', 'label' => 'Notifications', 'icon' => '🔔'],
['route' => 'settings.appearance', 'pattern' => 'settings.appearance', 'label' => 'Appearance', 'icon' => '🎨'],
['route' => 'settings.profile', 'pattern' => 'settings.profile', 'label' => 'Profile', 'icon' => '👤'],
['route' => 'settings.security', 'pattern' => 'settings.security', 'label' => 'Security', 'icon' => '🔒'],
['route' => 'settings.ai', 'pattern' => 'settings.ai', 'label' => 'AI Status', 'icon' => '🤖'],
];
@endphp

<div class="card" style="padding: 0.75rem;">
    <div style="display: flex; flex-wrap: wrap; gap: 0.375rem;">
        @foreach($sections as $section)
        <a
            href="{{ route($section['route']) }}"
            class="btn-secondary btn-sm"
            style="padding: 0.5rem 0.75rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; {{ request()->routeIs($section['pattern']) ? 'background-color: #eff6ff; color: var(--primary-color); border-color: #bfdbfe; font-weight: 600;' : '' }}">
            <span>{{ $section['icon'] }}</span> {{ $section['label'] }}
        </a>
        @endforeach
    </div>
</div>