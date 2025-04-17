<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo các quyền
        $permissions = [
            // Quyền quản lý người dùng
            ['name' => 'Manage Users', 'slug' => 'manage-users', 'description' => 'Quản lý người dùng'],
            ['name' => 'View Users', 'slug' => 'view-users', 'description' => 'Xem danh sách người dùng'],

            // Quyền quản lý dự án
            ['name' => 'Create Project', 'slug' => 'create-project', 'description' => 'Tạo dự án mới'],
            ['name' => 'Edit Project', 'slug' => 'edit-project', 'description' => 'Chỉnh sửa dự án'],
            ['name' => 'Delete Project', 'slug' => 'delete-project', 'description' => 'Xóa dự án'],
            ['name' => 'View Project', 'slug' => 'view-project', 'description' => 'Xem dự án'],

            // Quyền quản lý công việc
            ['name' => 'Create Task', 'slug' => 'create-task', 'description' => 'Tạo công việc mới'],
            ['name' => 'Edit Task', 'slug' => 'edit-task', 'description' => 'Chỉnh sửa công việc'],
            ['name' => 'Delete Task', 'slug' => 'delete-task', 'description' => 'Xóa công việc'],
            ['name' => 'View Task', 'slug' => 'view-task', 'description' => 'Xem công việc'],
            ['name' => 'Assign Task', 'slug' => 'assign-task', 'description' => 'Phân công công việc'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Tạo các vai trò
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Quản trị viên có toàn quyền'
            ]
        );

        $managerRole = Role::firstOrCreate(
            ['slug' => 'project-manager'],
            [
                'name' => 'Project Manager',
                'description' => 'Quản lý dự án'
            ]
        );

        $userRole = Role::firstOrCreate(
            ['slug' => 'user'],
            [
                'name' => 'User',
                'description' => 'Người dùng bình thường'
            ]
        );

        // Gán tất cả quyền cho admin
        $adminRole->permissions()->attach(Permission::all());

        // Gán quyền cho project manager
        $managerRole->permissions()->attach(
            Permission::whereIn('slug', [
                'view-users',
                'create-project',
                'edit-project',
                'delete-project',
                'view-project',
                'create-task',
                'edit-task',
                'delete-task',
                'view-task',
                'assign-task'
            ])->get()
        );

        // Gán quyền cho user thông thường
        $userRole->permissions()->attach(
            Permission::whereIn('slug', [
                'view-project',
                'view-task',
                'create-task',
                'edit-task'
            ])->get()
        );

        // Tạo tài khoản admin mặc định
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );
        $admin->roles()->sync([$adminRole->id]);

        // Tạo tài khoản manager mặc định
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Project Manager',
                'password' => Hash::make('password'),
            ]
        );
        $manager->roles()->sync([$managerRole->id]);

        // Tạo tài khoản user mặc định
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'User',
                'password' => Hash::make('password'),
            ]
        );
        $user->roles()->sync([$userRole->id]);
    }
}
