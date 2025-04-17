<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of the tasks.
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'nullable|exists:projects,id',
            'status' => 'nullable|in:todo,in_progress,completed',
            'priority' => 'nullable|in:low,medium,high',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $query = Task::query();

        // Filter by project if provided
        if ($request->has('project_id')) {
            $project = Project::find($request->project_id);
            
            // Check if user is owner or member of the project
            if ($project->owner_id !== $user->id && !$project->members->contains($user->id)) {
                return response()->json([
                    'message' => 'Bạn không có quyền truy cập dự án này'
                ], 403);
            }
            
            $query->where('project_id', $request->project_id);
        } else {
            // Get tasks from projects where user is owner or member
            $query->whereHas('project', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('members', function ($q2) use ($user) {
                      $q2->where('users.id', $user->id);
                  });
            });
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority if provided
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Get tasks with related data
        $tasks = $query->with([
            'project:id,name',
            'assignedUser:id,name,email,avatar',
            'creator:id,name,email,avatar'
        ])->orderBy('due_date', 'asc')->get();

        return response()->json([
            'tasks' => $tasks
        ]);
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,completed',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user is owner or member of the project
        $project = Project::find($request->project_id);
        $user = Auth::user();
        
        if ($project->owner_id !== $user->id && !$project->members->contains($user->id)) {
            return response()->json([
                'message' => 'Bạn không có quyền tạo công việc trong dự án này'
            ], 403);
        }

        // Create the task
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'project_id' => $request->project_id,
            'assigned_to' => $request->assigned_to,
            'created_by' => Auth::id(),
        ]);

        // Create notification if task is assigned to someone
        if ($request->assigned_to && $request->assigned_to != Auth::id()) {
            Notification::create([
                'user_id' => $request->assigned_to,
                'type' => 'task_assigned',
                'message' => 'Bạn đã được giao công việc mới: ' . $request->title,
                'notifiable_id' => $task->id,
                'notifiable_type' => Task::class,
            ]);
        }

        return response()->json([
            'message' => 'Công việc đã được tạo thành công',
            'task' => $task->load([
                'project:id,name',
                'assignedUser:id,name,email,avatar',
                'creator:id,name,email,avatar'
            ])
        ], 201);
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): JsonResponse
    {
        // Check if user is owner or member of the project
        $project = $task->project;
        $user = Auth::user();
        
        if ($project->owner_id !== $user->id && !$project->members->contains($user->id)) {
            return response()->json([
                'message' => 'Bạn không có quyền xem công việc này'
            ], 403);
        }

        return response()->json([
            'task' => $task->load([
                'project:id,name',
                'assignedUser:id,name,email,avatar',
                'creator:id,name,email,avatar'
            ])
        ]);
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,completed',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user is owner or member of the project
        $project = $task->project;
        $user = Auth::user();
        
        if ($project->owner_id !== $user->id && !$project->members->contains($user->id)) {
            return response()->json([
                'message' => 'Bạn không có quyền cập nhật công việc này'
            ], 403);
        }

        // Check if assignment has changed
        $oldAssignedTo = $task->assigned_to;
        $newAssignedTo = $request->assigned_to;

        // Update the task
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'assigned_to' => $request->assigned_to,
        ]);

        // Create notification if task is assigned to someone new
        if ($newAssignedTo && $newAssignedTo != $oldAssignedTo && $newAssignedTo != Auth::id()) {
            Notification::create([
                'user_id' => $newAssignedTo,
                'type' => 'task_assigned',
                'message' => 'Bạn đã được giao công việc: ' . $request->title,
                'notifiable_id' => $task->id,
                'notifiable_type' => Task::class,
            ]);
        }

        return response()->json([
            'message' => 'Công việc đã được cập nhật thành công',
            'task' => $task->load([
                'project:id,name',
                'assignedUser:id,name,email,avatar',
                'creator:id,name,email,avatar'
            ])
        ]);
    }

    /**
     * Update the status of a task.
     */
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:todo,in_progress,completed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user is owner or member of the project
        $project = $task->project;
        $user = Auth::user();
        
        if ($project->owner_id !== $user->id && !$project->members->contains($user->id)) {
            return response()->json([
                'message' => 'Bạn không có quyền cập nhật trạng thái công việc này'
            ], 403);
        }

        $task->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Trạng thái công việc đã được cập nhật thành công',
            'task' => $task->load([
                'project:id,name',
                'assignedUser:id,name,email,avatar',
                'creator:id,name,email,avatar'
            ])
        ]);
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        // Check if user is owner of the project or creator of the task
        $project = $task->project;
        $user = Auth::user();
        
        if ($project->owner_id !== $user->id && $task->created_by !== $user->id) {
            return response()->json([
                'message' => 'Bạn không có quyền xóa công việc này'
            ], 403);
        }

        $task->delete();

        return response()->json([
            'message' => 'Công việc đã được xóa thành công'
        ]);
    }
}
