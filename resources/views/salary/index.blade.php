<x-app-layout>
    <x-slot name="title">Salary - Personal Finance Management System</x-slot>

    @if (session('status'))
        <div class="alert-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="card">
        <h2 class="card-title">Current Monthly Salary</h2>
        @if ($salary !== null)
            <div class="salary-display-value">
                ${{ number_format($salary, 2) }}
            </div>
        @else
            <div class="salary-empty-state">
                No monthly salary set.
            </div>
        @endif
    </div>

    <div class="card">
        <h2 class="card-title">{{ $salary !== null ? 'Update Salary' : 'Set Salary' }}</h2>
        <form method="POST" action="{{ route('salary.update') }}" style="max-width: 400px; margin-top: 1rem;">
            @csrf

            <div class="form-group">
                <label for="salary" class="form-label">Monthly Salary</label>
                <input
                    id="salary"
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="salary"
                    value="{{ old('salary', $salary) }}"
                    required
                    class="form-control"
                    placeholder="e.g. 5000.00"
                >
                @error('salary')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-primary">
                {{ $salary !== null ? 'Update Salary' : 'Save Salary' }}
            </button>
        </form>
    </div>
</x-app-layout>
