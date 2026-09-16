<x-app-layout>
    <x-slot name="title">Add Task - Personal Life Management System</x-slot>
    <x-slot name="pageTitle">Tasks</x-slot>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h1 class="card-title">Create New Task</h1>
        <p class="card-subtitle" style="margin-bottom: 1.5rem;">Enter details for your new daily task.</p>

        <form method="POST" action="{{ route('tasks.store') }}">
            @csrf

            <div class="form-group">
                <label for="title" class="form-label">Task Title <span style="color: var(--danger-color);">*</span></label>
                <input
                    id="title"
                    type="text"
                    name="title"
                    value="{{ old('title') }}"
                    required
                    autofocus
                    class="form-control"
                    placeholder="e.g. Complete project proposal review"
                >
                @error('title')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description (Optional)</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    class="form-control"
                    placeholder="Enter additional task details or notes..."
                >{{ old('description') }}</textarea>
                @error('description')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="due_date" class="form-label">Due Date (Optional)</label>
                    <input
                        id="due_date"
                        type="date"
                        name="due_date"
                        value="{{ old('due_date') }}"
                        class="form-control"
                    >
                    @error('due_date')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="priority" class="form-label">Priority <span style="color: var(--danger-color);">*</span></label>
                    <select id="priority" name="priority" class="form-control" required>
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                    </select>
                    @error('priority')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status <span style="color: var(--danger-color);">*</span></label>
                <select id="status" name="status" class="form-control" required>
                    <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
                @error('status')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Save Task</button>
                <a href="{{ route('tasks.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
