<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $user->tasks();

        // Status filter
        if ($request->filled('status') && in_array($request->status, ['pending', 'in_progress', 'completed'])) {
            $query->where('status', $request->status);
        }

        // Priority filter
        if ($request->filled('priority') && in_array($request->priority, ['low', 'medium', 'high'])) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->orderByRaw("CASE WHEN status = 'completed' THEN 1 ELSE 0 END")
                       ->orderBy('due_date', 'asc')
                       ->orderBy('id', 'desc')
                       ->paginate(10)
                       ->withQueryString();

        $totalTasks = $user->tasks()->count();
        $pendingTasks = $user->tasks()->where('status', 'pending')->count();
        $inProgressTasks = $user->tasks()->where('status', 'in_progress')->count();
        $completedTasks = $user->tasks()->where('status', 'completed')->count();

        return view('daily-management.tasks.index', compact(
            'tasks',
            'totalTasks',
            'pendingTasks',
            'inProgressTasks',
            'completedTasks'
        ));
    }

    /**
     * Show form for creating a new task.
     */
    public function create()
    {
        return view('daily-management.tasks.create');
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', 'in:low,medium,high'],
            'status' => ['required', 'in:pending,in_progress,completed'],
        ]);

        $request->user()->tasks()->create($validated);

        return redirect()->route('tasks.index')->with('status', 'Task created successfully.');
    }

    /**
     * Show form for editing the specified task.
     */
    public function edit(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(403);
        }

        return view('daily-management.tasks.edit', compact('task'));
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', 'in:low,medium,high'],
            'status' => ['required', 'in:pending,in_progress,completed'],
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')->with('status', 'Task updated successfully.');
    }

    /**
     * Toggle status between completed and pending.
     */
    public function toggleStatus(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(403);
        }

        $newStatus = $task->status === 'completed' ? 'pending' : 'completed';
        $task->update(['status' => $newStatus]);

        $statusMsg = $newStatus === 'completed' ? 'Task marked as completed.' : 'Task marked as pending.';

        return redirect()->back()->with('status', $statusMsg);
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            abort(403);
        }

        $task->delete();

        return redirect()->route('tasks.index')->with('status', 'Task deleted successfully.');
    }
}
