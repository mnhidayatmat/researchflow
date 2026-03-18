<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Student $student)
    {
        $this->authorize('view', $student);

        $tasks = $student->tasks()
            ->whereNull('parent_id')
            ->with('subtasks')
            ->orderBy('sort_order')
            ->get();

        return view('tasks.index', compact('student', 'tasks'));
    }

    public function kanban(Student $student)
    {
        $this->authorize('view', $student);

        $tasks = $student->tasks()
            ->whereNull('parent_id')
            ->with('subtasks')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('status');

        return view('tasks.kanban', compact('student', 'tasks'));
    }

    public function gantt(Student $student)
    {
        $this->authorize('view', $student);

        $tasks = $student->tasks()
            ->whereNotNull('start_date')
            ->whereNotNull('due_date')
            ->orderBy('start_date')
            ->get();

        return view('tasks.gantt', compact('student', 'tasks'));
    }

    public function timeline(Student $student)
    {
        $this->authorize('view', $student);

        $tasks = $student->tasks()
            ->whereNotNull('start_date')
            ->whereNotNull('due_date')
            ->with('dependencies')
            ->orderBy('start_date')
            ->get();

        return view('tasks.timeline', compact('student', 'tasks'));
    }

    public function create(Student $student)
    {
        $this->authorize('view', $student);
        $milestones = $student->researchJourneys()->with('stages.milestones')->get()
            ->pluck('stages')->flatten()->pluck('milestones')->flatten();

        return view('tasks.create', compact('student', 'milestones'));
    }

    public function store(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'milestone_id' => 'nullable|exists:milestones,id',
            'parent_id' => 'nullable|exists:tasks,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer',
        ]);

        $student->tasks()->create([
            ...$validated,
            'assigned_by' => Auth::id(),
            'status' => 'backlog',
        ]);

        return redirect()->route('tasks.index', $student)->with('success', 'Task created.');
    }

    public function show(Student $student, Task $task)
    {
        $this->authorize('view', $task);
        $task->load(['subtasks', 'milestone', 'assignedBy', 'dependencies', 'revisions.comments.user']);
        return view('tasks.show', compact('student', 'task'));
    }

    public function edit(Student $student, Task $task)
    {
        $this->authorize('update', $task);
        $milestones = $student->researchJourneys()->with('stages.milestones')->get()
            ->pluck('stages')->flatten()->pluck('milestones')->flatten();
        return view('tasks.edit', compact('student', 'task', 'milestones'));
    }

    public function update(Request $request, Student $student, Task $task)
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'milestone_id' => 'nullable|exists:milestones,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:' . implode(',', Task::STATUSES),
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'progress' => 'integer|min:0|max:100',
            'estimated_hours' => 'nullable|integer',
        ]);

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
            $validated['progress'] = 100;
        }

        $task->update($validated);

        return redirect()->route('tasks.show', [$student, $task])->with('success', 'Task updated.');
    }

    public function destroy(Student $student, Task $task)
    {
        $this->authorize('update', $task);
        $task->delete();
        return redirect()->route('tasks.index', $student)->with('success', 'Task deleted.');
    }
}
