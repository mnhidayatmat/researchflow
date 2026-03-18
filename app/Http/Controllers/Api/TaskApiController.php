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
            ->whereNotNull('start_date')
            ->whereNotNull('due_date')
            ->with('dependencies')
            ->orderBy('start_date')
            ->get()
            ->map(fn(Task $task) => [
                'id' => (string) $task->id,
                'name' => $task->title,
                'start' => $task->start_date->format('Y-m-d'),
                'end' => $task->due_date->format('Y-m-d'),
                'progress' => $task->progress,
                'dependencies' => $task->dependencies->pluck('id')->map(fn($id) => (string) $id)->implode(','),
                'custom_class' => 'gantt-' . $task->status,
            ]);

        return response()->json($tasks);
    }

    public function updateDates(Request $request, Task $task)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:start_date',
        ]);

        $task->update($validated);

        return response()->json(['success' => true, 'task' => $task->fresh()]);
    }
}
