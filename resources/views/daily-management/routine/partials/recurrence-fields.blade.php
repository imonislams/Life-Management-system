@php
use App\Models\RoutineItem;

/**
* Recurrence field set shared by the routine create/edit forms.
*
* Expects: $routineItem (RoutineItem|null).
*/
$item = $routineItem ?? null;

// Pre-selected custom weekdays (0=Sun..6=Sat).
$selectedDays = old('days_of_week', $item ? $item->dayOfWeekList() : []);
$selectedDays = array_map('intval', (array) $selectedDays);
@endphp

<div class="form-group">
    <label for="recurrence_type" class="form-label">Recurrence <span style="color: var(--danger-color);">*</span></label>
    <select id="recurrence_type" name="recurrence_type" class="form-control" required>
        @foreach(RoutineItem::RECURRENCE_TYPES as $type)
        <option value="{{ $type }}" {{ old('recurrence_type', optional($item)->recurrence_type ?? 'daily') === $type ? 'selected' : '' }}>
            @switch($type)
            @case('one_time') One time (single date) @break
            @case('daily') Daily @break
            @case('weekly') Weekly @break
            @case('monthly') Monthly @break
            @case('custom_days') Custom days (pick weekdays) @break
            @case('interval') Custom interval (every N days) @break
            @endswitch
        </option>
        @endforeach
    </select>
    @error('recurrence_type')<div class="error-msg">{{ $message }}</div>@enderror
</div>

<!-- Custom interval -->
<div class="form-group" id="intervalGroup" style="display:none;">
    <label for="interval_days" class="form-label">Repeat Every (days)</label>
    <input id="interval_days" type="number" min="1" max="365" name="interval_days" value="{{ old('interval_days', optional($item)->interval_days ?? 2) }}" class="form-control">
    @error('interval_days')<div class="error-msg">{{ $message }}</div>@enderror
</div>

<!-- Custom days -->
<div class="form-group" id="daysGroup" style="display:none;">
    <label class="form-label">Repeat On</label>
    <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
        @foreach(RoutineItem::WEEKDAYS as $value => $label)
        <label style="display:inline-flex; align-items:center; gap:0.35rem; font-size:0.85rem; background:#f1f5f9; border:1px solid #e2e8f0; padding:0.35rem 0.6rem; border-radius:0.4rem; cursor:pointer;">
            <input type="checkbox" name="days_of_week[]" value="{{ $value }}" {{ in_array($value, $selectedDays, true) ? 'checked' : '' }}>
            {{ $label }}
        </label>
        @endforeach
    </div>
    @error('days_of_week')<div class="error-msg">{{ $message }}</div>@enderror
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
    <div class="form-group">
        <label for="start_date" class="form-label">Start Date</label>
        <input id="start_date" type="date" name="start_date" value="{{ old('start_date', optional(optional($item)->start_date)->format('Y-m-d') ?? now()->toDateString()) }}" class="form-control">
        @error('start_date')<div class="error-msg">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="end_date" class="form-label">End Date (Optional)</label>
        <input id="end_date" type="date" name="end_date" value="{{ old('end_date', optional(optional($item)->end_date)->format('Y-m-d')) }}" class="form-control">
        @error('end_date')<div class="error-msg">{{ $message }}</div>@enderror
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('recurrence_type');
        const intervalGroup = document.getElementById('intervalGroup');
        const daysGroup = document.getElementById('daysGroup');

        function sync() {
            intervalGroup.style.display = typeSelect.value === 'interval' ? '' : 'none';
            daysGroup.style.display = typeSelect.value === 'custom_days' ? '' : 'none';
        }

        if (typeSelect) {
            typeSelect.addEventListener('change', sync);
            sync();
        }
    });
</script>