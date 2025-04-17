<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users
        $admin = User::where('email', 'admin@example.com')->first();
        $manager = User::where('email', 'manager@example.com')->first();
        $user = User::where('email', 'user@example.com')->first();

        if (!$admin || !$manager || !$user) {
            $this->command->error('Users not found. Please run RolePermissionSeeder first.');
            return;
        }

        // Create projects
        $project1 = Project::create([
            'name' => 'Website Redesign',
            'description' => 'Redesign the company website with modern UI/UX principles',
            'owner_id' => $admin->id,
        ]);

        $project2 = Project::create([
            'name' => 'Mobile App Development',
            'description' => 'Develop a mobile app for both iOS and Android platforms',
            'owner_id' => $manager->id,
        ]);

        $project3 = Project::create([
            'name' => 'Marketing Campaign',
            'description' => 'Plan and execute a marketing campaign for the new product launch',
            'owner_id' => $admin->id,
        ]);

        // Add members to projects
        $project1->members()->attach([$admin->id, $manager->id, $user->id]);
        $project2->members()->attach([$manager->id, $user->id]);
        $project3->members()->attach([$admin->id, $manager->id]);

        // Create tasks for Project 1
        $tasks1 = [
            [
                'title' => 'Wireframe Design',
                'description' => 'Create wireframes for all pages of the website',
                'status' => 'completed',
                'priority' => 'high',
                'due_date' => now()->addDays(5),
                'project_id' => $project1->id,
                'assigned_to' => $user->id,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Frontend Development',
                'description' => 'Implement the frontend using HTML, CSS, and JavaScript',
                'status' => 'in_progress',
                'priority' => 'high',
                'due_date' => now()->addDays(10),
                'project_id' => $project1->id,
                'assigned_to' => $manager->id,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Backend Integration',
                'description' => 'Connect the frontend with the backend API',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => now()->addDays(15),
                'project_id' => $project1->id,
                'assigned_to' => $admin->id,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Testing',
                'description' => 'Perform comprehensive testing of the website',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => now()->addDays(20),
                'project_id' => $project1->id,
                'assigned_to' => $user->id,
                'created_by' => $admin->id,
            ],
        ];

        // Create tasks for Project 2
        $tasks2 = [
            [
                'title' => 'App Design',
                'description' => 'Design the UI/UX for the mobile app',
                'status' => 'in_progress',
                'priority' => 'high',
                'due_date' => now()->addDays(7),
                'project_id' => $project2->id,
                'assigned_to' => $user->id,
                'created_by' => $manager->id,
            ],
            [
                'title' => 'iOS Development',
                'description' => 'Develop the app for iOS platform',
                'status' => 'todo',
                'priority' => 'high',
                'due_date' => now()->addDays(14),
                'project_id' => $project2->id,
                'assigned_to' => $manager->id,
                'created_by' => $manager->id,
            ],
            [
                'title' => 'Android Development',
                'description' => 'Develop the app for Android platform',
                'status' => 'todo',
                'priority' => 'high',
                'due_date' => now()->addDays(14),
                'project_id' => $project2->id,
                'assigned_to' => $manager->id,
                'created_by' => $manager->id,
            ],
            [
                'title' => 'API Integration',
                'description' => 'Integrate the app with backend APIs',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => now()->addDays(21),
                'project_id' => $project2->id,
                'assigned_to' => $user->id,
                'created_by' => $manager->id,
            ],
        ];

        // Create tasks for Project 3
        $tasks3 = [
            [
                'title' => 'Market Research',
                'description' => 'Conduct market research to identify target audience',
                'status' => 'completed',
                'priority' => 'high',
                'due_date' => now()->addDays(3),
                'project_id' => $project3->id,
                'assigned_to' => $manager->id,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Content Creation',
                'description' => 'Create content for the marketing campaign',
                'status' => 'in_progress',
                'priority' => 'medium',
                'due_date' => now()->addDays(8),
                'project_id' => $project3->id,
                'assigned_to' => $user->id,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Social Media Strategy',
                'description' => 'Develop a social media strategy for the campaign',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => now()->addDays(12),
                'project_id' => $project3->id,
                'assigned_to' => $manager->id,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Campaign Launch',
                'description' => 'Launch the marketing campaign',
                'status' => 'todo',
                'priority' => 'high',
                'due_date' => now()->addDays(18),
                'project_id' => $project3->id,
                'assigned_to' => $admin->id,
                'created_by' => $admin->id,
            ],
        ];

        // Insert all tasks
        foreach (array_merge($tasks1, $tasks2, $tasks3) as $taskData) {
            Task::create($taskData);
        }
    }
}
