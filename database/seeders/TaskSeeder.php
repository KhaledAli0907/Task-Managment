<?php

namespace Database\Seeders;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing tasks
        DB::table('tasks')->truncate();

        // Get users for assignment
        $manager = User::where('email', 'manager@test.com')->first();
        $user1 = User::where('email', 'user1@test.com')->first();
        $user2 = User::where('email', 'user2@test.com')->first();

        if (!$manager || !$user1 || !$user2) {
            $this->command->error('Users not found. Please run UserSeeder first.');
            return;
        }

        $this->command->info('Creating tasks...');

        // Create main project tasks
        $projectTasks = $this->createProjectTasks($manager, $user1, $user2);

        // Create child tasks for each main task
        $this->createChildTasks($projectTasks, $user1, $user2);

        $this->command->info('Tasks created successfully!');
        $this->command->info('Total tasks created: ' . Task::count());
    }

    /**
     * Create main project tasks
     */
    private function createProjectTasks($manager, $user1, $user2): array
    {
        $tasks = [];

        // Task 1: Website Development Project
        $tasks[] = Task::create([
            'title' => 'Website Development Project',
            'description' => 'Complete development of the company website with modern design and responsive layout.',
            'status' => TaskStatus::IN_PROGRESS->value,
            'completed' => false,
            'due_date' => now()->addDays(30),
            'assignee_id' => $manager->id,
        ]);

        // Task 2: Database Migration
        $tasks[] = Task::create([
            'title' => 'Database Migration',
            'description' => 'Migrate existing data from legacy system to new database structure.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(15),
            'assignee_id' => $user1->id,
        ]);

        // Task 3: API Documentation
        $tasks[] = Task::create([
            'title' => 'API Documentation',
            'description' => 'Create comprehensive API documentation for all endpoints.',
            'status' => TaskStatus::COMPLETED->value,
            'completed' => true,
            'due_date' => now()->subDays(5),
            'assignee_id' => $user2->id,
        ]);

        // Task 4: User Authentication System
        $tasks[] = Task::create([
            'title' => 'User Authentication System',
            'description' => 'Implement JWT-based authentication system with role-based access control.',
            'status' => TaskStatus::IN_PROGRESS->value,
            'completed' => false,
            'due_date' => now()->addDays(20),
            'assignee_id' => $user1->id,
        ]);

        // Task 5: Performance Optimization
        $tasks[] = Task::create([
            'title' => 'Performance Optimization',
            'description' => 'Optimize application performance and reduce loading times.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(45),
            'assignee_id' => $user2->id,
        ]);

        // Task 6: Security Audit
        $tasks[] = Task::create([
            'title' => 'Security Audit',
            'description' => 'Conduct comprehensive security audit and implement security best practices.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(25),
            'assignee_id' => $manager->id,
        ]);

        // Task 7: Mobile App Development
        $tasks[] = Task::create([
            'title' => 'Mobile App Development',
            'description' => 'Develop mobile application for iOS and Android platforms.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(60),
            'assignee_id' => $user1->id,
        ]);

        // Task 8: Testing and QA
        $tasks[] = Task::create([
            'title' => 'Testing and QA',
            'description' => 'Comprehensive testing of all features and quality assurance.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(40),
            'assignee_id' => $user2->id,
        ]);

        // Task 9: Deployment Setup
        $tasks[] = Task::create([
            'title' => 'Deployment Setup',
            'description' => 'Set up production environment and deployment pipeline.',
            'status' => TaskStatus::IN_PROGRESS->value,
            'completed' => false,
            'due_date' => now()->addDays(10),
            'assignee_id' => $manager->id,
        ]);

        // Task 10: User Training
        $tasks[] = Task::create([
            'title' => 'User Training',
            'description' => 'Conduct training sessions for end users on new system features.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(35),
            'assignee_id' => $manager->id,
        ]);

        return $tasks;
    }

    /**
     * Create child tasks for main tasks
     */
    private function createChildTasks(array $parentTasks, $user1, $user2): void
    {
        // Child tasks for Website Development Project
        $websiteProject = $parentTasks[0];
        Task::create([
            'title' => 'Frontend Development',
            'description' => 'Develop responsive frontend using React.js with modern UI components.',
            'status' => TaskStatus::IN_PROGRESS->value,
            'completed' => false,
            'due_date' => now()->addDays(20),
            'assignee_id' => $user1->id,
            'parent_task_id' => $websiteProject->id,
        ]);

        Task::create([
            'title' => 'Backend API Development',
            'description' => 'Create RESTful API endpoints for frontend integration.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(25),
            'assignee_id' => $user2->id,
            'parent_task_id' => $websiteProject->id,
        ]);

        Task::create([
            'title' => 'UI/UX Design',
            'description' => 'Create wireframes and design mockups for the website.',
            'status' => TaskStatus::COMPLETED->value,
            'completed' => true,
            'due_date' => now()->subDays(10),
            'assignee_id' => $user1->id,
            'parent_task_id' => $websiteProject->id,
        ]);

        // Child tasks for Database Migration
        $dbMigration = $parentTasks[1];
        Task::create([
            'title' => 'Data Analysis',
            'description' => 'Analyze existing data structure and plan migration strategy.',
            'status' => TaskStatus::COMPLETED->value,
            'completed' => true,
            'due_date' => now()->subDays(5),
            'assignee_id' => $user1->id,
            'parent_task_id' => $dbMigration->id,
        ]);

        Task::create([
            'title' => 'Migration Scripts',
            'description' => 'Write and test data migration scripts.',
            'status' => TaskStatus::IN_PROGRESS->value,
            'completed' => false,
            'due_date' => now()->addDays(10),
            'assignee_id' => $user2->id,
            'parent_task_id' => $dbMigration->id,
        ]);

        // Child tasks for User Authentication System
        $authSystem = $parentTasks[3];
        Task::create([
            'title' => 'JWT Implementation',
            'description' => 'Implement JWT token generation and validation.',
            'status' => TaskStatus::IN_PROGRESS->value,
            'completed' => false,
            'due_date' => now()->addDays(12),
            'assignee_id' => $user1->id,
            'parent_task_id' => $authSystem->id,
        ]);

        Task::create([
            'title' => 'Role-Based Access Control',
            'description' => 'Implement role-based permissions and middleware.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(15),
            'assignee_id' => $user2->id,
            'parent_task_id' => $authSystem->id,
        ]);

        Task::create([
            'title' => 'Password Reset Functionality',
            'description' => 'Implement secure password reset with email verification.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(18),
            'assignee_id' => $user1->id,
            'parent_task_id' => $authSystem->id,
        ]);

        // Child tasks for Performance Optimization
        $performance = $parentTasks[4];
        Task::create([
            'title' => 'Database Query Optimization',
            'description' => 'Optimize database queries and add proper indexing.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(30),
            'assignee_id' => $user2->id,
            'parent_task_id' => $performance->id,
        ]);

        Task::create([
            'title' => 'Frontend Performance',
            'description' => 'Optimize frontend loading times and implement lazy loading.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(35),
            'assignee_id' => $user1->id,
            'parent_task_id' => $performance->id,
        ]);

        // Child tasks for Security Audit
        $security = $parentTasks[5];
        Task::create([
            'title' => 'Vulnerability Assessment',
            'description' => 'Conduct automated and manual security vulnerability assessment.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(20),
            'assignee_id' => $user2->id,
            'parent_task_id' => $security->id,
        ]);

        Task::create([
            'title' => 'Security Policy Implementation',
            'description' => 'Implement security policies and best practices.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(25),
            'assignee_id' => $user1->id,
            'parent_task_id' => $security->id,
        ]);

        // Child tasks for Mobile App Development
        $mobileApp = $parentTasks[6];
        Task::create([
            'title' => 'iOS App Development',
            'description' => 'Develop native iOS application using Swift.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(45),
            'assignee_id' => $user1->id,
            'parent_task_id' => $mobileApp->id,
        ]);

        Task::create([
            'title' => 'Android App Development',
            'description' => 'Develop native Android application using Kotlin.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(50),
            'assignee_id' => $user2->id,
            'parent_task_id' => $mobileApp->id,
        ]);

        // Child tasks for Testing and QA
        $testing = $parentTasks[7];
        Task::create([
            'title' => 'Unit Testing',
            'description' => 'Write comprehensive unit tests for all components.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(30),
            'assignee_id' => $user1->id,
            'parent_task_id' => $testing->id,
        ]);

        Task::create([
            'title' => 'Integration Testing',
            'description' => 'Perform integration testing between different system components.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(35),
            'assignee_id' => $user2->id,
            'parent_task_id' => $testing->id,
        ]);

        Task::create([
            'title' => 'User Acceptance Testing',
            'description' => 'Conduct user acceptance testing with stakeholders.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(40),
            'assignee_id' => $user1->id,
            'parent_task_id' => $testing->id,
        ]);

        // Child tasks for Deployment Setup
        $deployment = $parentTasks[8];
        Task::create([
            'title' => 'Production Environment Setup',
            'description' => 'Set up production servers and infrastructure.',
            'status' => TaskStatus::IN_PROGRESS->value,
            'completed' => false,
            'due_date' => now()->addDays(5),
            'assignee_id' => $user2->id,
            'parent_task_id' => $deployment->id,
        ]);

        Task::create([
            'title' => 'CI/CD Pipeline',
            'description' => 'Configure continuous integration and deployment pipeline.',
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => now()->addDays(8),
            'assignee_id' => $user1->id,
            'parent_task_id' => $deployment->id,
        ]);
    }
}
