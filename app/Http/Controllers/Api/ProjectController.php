<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        // Get projects where user is owner or member
        $projects = Project::where('owner_id', $user->id)
            ->orWhereHas('members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->with(['owner:id,name,email,avatar', 'members:id,name,email,avatar'])
            ->get();
        
        return response()->json([
            'projects' => $projects
        ]);
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => Auth::id(),
        ]);

        // Add owner as a member of the project
        $project->members()->attach(Auth::id());

        return response()->json([
            'message' => 'Dự án đã được tạo thành công',
            'project' => $project->load(['owner:id,name,email,avatar', 'members:id,name,email,avatar'])
        ], 201);
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project): JsonResponse
    {
        // Check if user is owner or member of the project
        $user = Auth::user();
        if ($project->owner_id !== $user->id && !$project->members->contains($user->id)) {
            return response()->json([
                'message' => 'Bạn không có quyền truy cập dự án này'
            ], 403);
        }

        return response()->json([
            'project' => $project->load([
                'owner:id,name,email,avatar', 
                'members:id,name,email,avatar',
                'tasks' => function ($query) {
                    $query->orderBy('due_date', 'asc');
                }
            ])
        ]);
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        // Check if user is the owner of the project
        if ($project->owner_id !== Auth::id()) {
            return response()->json([
                'message' => 'Bạn không có quyền chỉnh sửa dự án này'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $project->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Dự án đã được cập nhật thành công',
            'project' => $project->load(['owner:id,name,email,avatar', 'members:id,name,email,avatar'])
        ]);
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project): JsonResponse
    {
        // Check if user is the owner of the project
        if ($project->owner_id !== Auth::id()) {
            return response()->json([
                'message' => 'Bạn không có quyền xóa dự án này'
            ], 403);
        }

        $project->delete();

        return response()->json([
            'message' => 'Dự án đã được xóa thành công'
        ]);
    }

    /**
     * Add a member to the project.
     */
    public function addMember(Request $request, Project $project): JsonResponse
    {
        // Check if user is the owner of the project
        if ($project->owner_id !== Auth::id()) {
            return response()->json([
                'message' => 'Bạn không có quyền thêm thành viên vào dự án này'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user is already a member
        if ($project->members->contains($request->user_id)) {
            return response()->json([
                'message' => 'Người dùng đã là thành viên của dự án'
            ], 422);
        }

        $project->members()->attach($request->user_id);
        $user = User::find($request->user_id);

        return response()->json([
            'message' => 'Đã thêm thành viên vào dự án',
            'member' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ]
        ]);
    }

    /**
     * Remove a member from the project.
     */
    public function removeMember(Request $request, Project $project): JsonResponse
    {
        // Check if user is the owner of the project
        if ($project->owner_id !== Auth::id()) {
            return response()->json([
                'message' => 'Bạn không có quyền xóa thành viên khỏi dự án này'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cannot remove the owner
        if ($request->user_id == $project->owner_id) {
            return response()->json([
                'message' => 'Không thể xóa chủ sở hữu dự án'
            ], 422);
        }

        // Check if user is a member
        if (!$project->members->contains($request->user_id)) {
            return response()->json([
                'message' => 'Người dùng không phải là thành viên của dự án'
            ], 422);
        }

        $project->members()->detach($request->user_id);

        return response()->json([
            'message' => 'Đã xóa thành viên khỏi dự án'
        ]);
    }
}
