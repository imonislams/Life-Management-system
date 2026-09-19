@php
/**
* Optional activity list input for the habit forms.
*
* Each row posts `activities[index][name]`, which the HabitController merges
* into the habit's activity list (existing activities, with their completion
* history, are preserved by name).
*/
$existingActivities = isset($habit) && $habit ? $habit->activities : collect();
@endphp

<div class="form-group">
    <label class="form-label">Activities (Optional)</label>
    <p style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.5rem;">
        Add multiple independently-trackable activities, e.g. Fajr, Dhuhr, Asr, Maghrib, Isha.
    </p>

    @if($existingActivities->count() > 0)
    <div style="display:flex; flex-wrap:wrap; gap:0.4rem; margin-bottom:0.75rem;">
        @foreach($existingActivities as $activity)
        <span class="badge badge-active">{{ $activity->name }}</span>
        @endforeach
    </div>
    @endif

    <div id="activityRows">
        <div class="activity-row" style="display:flex; gap:0.5rem; margin-bottom:0.5rem;">
            <input type="text" name="activities[0][name]" class="form-control" placeholder="Activity name">
        </div>
    </div>
    <button type="button" id="addActivityRow" class="btn-secondary btn-sm" style="padding:0.4rem 0.75rem;">+ Add another activity</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('activityRows');
        const addBtn = document.getElementById('addActivityRow');
        if (!container || !addBtn) return;

        addBtn.addEventListener('click', function() {
            const index = container.querySelectorAll('.activity-row').length;
            const row = document.createElement('div');
            row.className = 'activity-row';
            row.style.cssText = 'display:flex; gap:0.5rem; margin-bottom:0.5rem;';
            row.innerHTML = '<input type="text" name="activities[' + index + '][name]" class="form-control" placeholder="Activity name">';
            container.appendChild(row);
        });
    });
</script>