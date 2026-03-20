<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskApiController extends Controller
{
    public function index(Student $student)
    {
        $tasks = $student->tasks()
            ->whereNull('parent_id')
            ->with('subtasks')
            ->orderBy('sort_order')
            ->get();

        return response()->json($tasks);
    }

    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', Task::STATUSES),
        ]);

        $task->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === 'completed' ? now() : null,
            'progress' => $validated['status'] === 'completed' ? 100 : $task->progress,
        ]);

        return response()->json(['success' => true, 'task' => $task->fresh()]);
    }

    public function updateProgress(Request $request, Task $task)
    {
        $validated = $request->validate([
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $task->update(['progress' => $validated['progress']]);

        return response()->json(['success' => true, 'task' => $task->fresh()]);
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.sort_order' => 'required|integer',
            'tasks.*.status' => 'nullable|string',
        ]);

        foreach ($validated['tasks'] as $taskData) {
            $update = ['sort_order' => $taskData['sort_order']];
            if (isset($taskData['status'])) {
                $update['status'] = $taskData['status'];
            }
            Task::where('id', $taskData['id'])->update($update);
        }

        return response()->json(['success' => true]);
    }

    public function ganttData(Student $student)
    {
        $tasks = $student->tasks()
            ->where(function ($query) {
                $query->whereNotNull('start_date')
                    ->orWhereNotNull('due_date');
            })
            ->with('dependencies')
            ->orderBy('start_date')
            ->get()
            ->map(fn(Task $task) => $task->toGanttData());

        return response()->json($tasks);
    }

    public function updateDates(Request $request, Task $task)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Auto-calculate duration if both dates are provided
        if (!empty($validated['start_date']) && !empty($validated['due_date'])) {
            $start = \Carbon\Carbon::parse($validated['start_date']);
            $end = \Carbon\Carbon::parse($validated['due_date']);
            $validated['duration_days'] = $start->diffInDays($end);
        }

        $task->update($validated);

        return response()->json(['success' => true, 'task' => $task->fresh()]);
    }

    /**
     * Create a new activity from the timeline overview
     */
    public function storeActivity(Request $request, Student $student)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'duration_days' => 'required|integer|min:1|max:365',
            'progress' => 'nullable|integer|min:0|max:100',
            'is_milestone' => 'nullable|boolean',
            'parent_task_id' => 'nullable|exists:tasks,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $validated['student_id'] = $student->id;
        $validated['assigned_by'] = auth()->id();
        $validated['is_milestone'] = $validated['is_milestone'] ?? false;
        $validated['status'] = $validated['is_milestone'] ? 'planned' : 'backlog';
        $validated['progress'] = $validated['progress'] ?? 0;

        // Calculate due date from start_date and duration
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $validated['due_date'] = $startDate->copy()->addDays($validated['duration_days']);

        $task = Task::create($validated);

        return response()->json([
            'success' => true,
            'task' => $task->fresh()->load('dependencies')->toGanttData(),
        ], 201);
    }

    /**
     * Get milestones for dropdown
     */
    public function milestones(Student $student)
    {
        // Get tasks that serve as milestones (could be filtered by status or type)
        $milestones = $student->tasks()
            ->where('status', '!=', 'backlog')
            ->orderBy('due_date')
            ->get()
            ->map(fn($task) => [
                'id' => $task->id,
                'name' => $task->title,
                'status' => $task->status,
                'deadline' => $task->due_date?->format('Y-m-d'),
            ]);

        return response()->json($milestones);
    }
}
