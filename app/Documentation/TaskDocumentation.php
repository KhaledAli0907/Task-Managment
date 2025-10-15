<?php

namespace App\Documentation;

/**
 * @OA\Info(
 *   title="Task Management System API",
 *   version="0.0.1",
 *   description="API documentation",
 * )
 * @OA\Server(
 *   url="http://localhost:8000",
 *   description="Local Server"
 * )
 */
class TaskDocumentation
{
    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     summary="Get all tasks",
     *     tags={"Tasks"},
     *     description="Retrieves a list of tasks. Results are filtered based on user role and permissions. Requires TASK_READ permission.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter tasks by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "in_progress", "completed", "archived"})
     *     ),
     *     @OA\Parameter(
     *         name="assignee_id",
     *         in="query",
     *         description="Filter tasks by assignee ID",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="parent_task_id",
     *         in="query",
     *         description="Filter tasks by parent task ID",
     *         required=false,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tasks retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tasks fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                     @OA\Property(property="title", type="string", example="Implement user authentication"),
     *                     @OA\Property(property="description", type="string", nullable=true, example="Implement JWT-based authentication system"),
     *                     @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "archived"}, example="pending"),
     *                     @OA\Property(property="completed", type="boolean", example=false),
     *                     @OA\Property(property="due_date", type="string", format="date-time", nullable=true, example="2024-12-31T23:59:59Z"),
     *                     @OA\Property(property="assignee_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440001"),
     *                     @OA\Property(property="parent_task_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440002"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T14:45:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You do not have permission to read tasks")
     *         )
     *     )
     * )
     */
    private function index()
    {
    }

    /**
     * @OA\Get(
     *     path="/api/tasks/{id}",
     *     summary="Get a specific task",
     *     tags={"Tasks"},
     *     description="Retrieves a single task by ID. Results are filtered based on user role and permissions. Requires TASK_READ permission.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="title", type="string", example="Implement user authentication"),
     *                 @OA\Property(property="description", type="string", nullable=true, example="Implement JWT-based authentication system"),
     *                 @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "archived"}, example="pending"),
     *                 @OA\Property(property="completed", type="boolean", example=false),
     *                 @OA\Property(property="due_date", type="string", format="date-time", nullable=true, example="2024-12-31T23:59:59Z"),
     *                 @OA\Property(property="assignee_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440001"),
     *                 @OA\Property(property="parent_task_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440002"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T14:45:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions or task not accessible",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You do not have permission to view this task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task not found")
     *         )
     *     )
     * )
     */
    private function show()
    {
    }

    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="Create a new task",
     *     tags={"Tasks"},
     *     description="Creates a new task with optional child tasks. Requires TASK_CREATE permission.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title", "status"},
     *             @OA\Property(property="title", type="string", maxLength=255, example="Implement user authentication"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Implement JWT-based authentication system"),
     *             @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "archived"}, example="pending"),
     *             @OA\Property(property="completed", type="boolean", example=false),
     *             @OA\Property(property="due_date", type="string", format="date-time", nullable=true, example="2024-12-31T23:59:59Z"),
     *             @OA\Property(property="assignee_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440001"),
     *             @OA\Property(
     *                 property="children",
     *                 type="array",
     *                 nullable=true,
     *                 @OA\Items(
     *                     type="object",
     *                     required={"title", "status"},
     *                     @OA\Property(property="title", type="string", maxLength=255, example="Setup database"),
     *                     @OA\Property(property="description", type="string", nullable=true, example="Create database schema"),
     *                     @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "archived"}, example="pending"),
     *                     @OA\Property(property="due_date", type="string", format="date-time", nullable=true, example="2024-12-31T23:59:59Z"),
     *                     @OA\Property(property="assignee_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440001")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="title", type="string", example="Implement user authentication"),
     *                 @OA\Property(property="description", type="string", nullable=true, example="Implement JWT-based authentication system"),
     *                 @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "archived"}, example="pending"),
     *                 @OA\Property(property="completed", type="boolean", example=false),
     *                 @OA\Property(property="due_date", type="string", format="date-time", nullable=true, example="2024-12-31T23:59:59Z"),
     *                 @OA\Property(property="assignee_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440001"),
     *                 @OA\Property(property="parent_task_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440002"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T14:45:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", example={"title": {"The title field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You do not have permission to create tasks")
     *         )
     *     )
     * )
     */
    private function store()
    {
    }

    /**
     * @OA\Put(
     *     path="/api/tasks/{id}",
     *     summary="Update a task",
     *     tags={"Tasks"},
     *     description="Updates an existing task. Users with TASK_UPDATE permission can update all fields. Users with TASK_STATUS_UPDATE permission can only update status of tasks assigned to them.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", maxLength=255, example="Implement user authentication"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Implement JWT-based authentication system"),
     *             @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "cancelled"}, example="in_progress"),
     *             @OA\Property(property="completed", type="boolean", example=false),
     *             @OA\Property(property="due_date", type="string", format="date-time", nullable=true, example="2024-12-31T23:59:59Z"),
     *             @OA\Property(property="assignee_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440001")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="title", type="string", example="Implement user authentication"),
     *                 @OA\Property(property="description", type="string", nullable=true, example="Implement JWT-based authentication system"),
     *                 @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "archived"}, example="in_progress"),
     *                 @OA\Property(property="completed", type="boolean", example=false),
     *                 @OA\Property(property="due_date", type="string", format="date-time", nullable=true, example="2024-12-31T23:59:59Z"),
     *                 @OA\Property(property="assignee_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440001"),
     *                 @OA\Property(property="parent_task_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440002"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T14:45:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", example={"title": {"The title field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions or task not accessible",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You are not authorized to update this task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task not found")
     *         )
     *     )
     * )
     */
    private function update()
    {
    }

    /**
     * @OA\Delete(
     *     path="/api/tasks/{id}",
     *     summary="Delete a task",
     *     tags={"Tasks"},
     *     description="Deletes a task by ID. Requires TASK_DELETE permission.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You do not have permission to delete tasks")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task not found")
     *         )
     *     )
     * )
     */
    private function destroy()
    {
    }

    /**
     * @OA\Post(
     *     path="/api/tasks/{id}/assign",
     *     summary="Assign a task to a user",
     *     tags={"Tasks"},
     *     description="Assigns a task to a specific user. Requires TASK_ASSIGN permission.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"assignee_id"},
     *             @OA\Property(property="assignee_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task assigned successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task assigned successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", example={"assignee_id": {"The assignee id field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You do not have permission to assign tasks")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while assigning the task")
     *         )
     *     )
     * )
     */
    private function assign()
    {
    }

    /**
     * @OA\Patch(
     *     path="/api/tasks/{id}/status",
     *     summary="Change task status",
     *     tags={"Tasks"},
     *     description="Changes the status of a task. Users with TASK_UPDATE permission can change status of any task. Users with TASK_STATUS_UPDATE permission can only change status of tasks assigned to them.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Task ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "cancelled"}, example="completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task status changed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Task status changed successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", example={"status": {"The status field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions or task not accessible",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You are not authorized to change the status of this task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Task not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while changing task status")
     *         )
     *     )
     * )
     */
    private function changeTaskStatus()
    {
    }

    /**
     * @OA\Post(
     *     path="/api/tasks/{id}/children",
     *     summary="Add a child task",
     *     tags={"Tasks"},
     *     description="Adds a child task to an existing parent task. Requires TASK_MANAGE_CHILDREN permission.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Parent task ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title", "status"},
     *             @OA\Property(property="title", type="string", maxLength=255, example="Setup database"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Create database schema"),
     *             @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "archived"}, example="pending"),
     *             @OA\Property(property="completed", type="boolean", example=false),
     *             @OA\Property(property="due_date", type="string", format="date-time", nullable=true, example="2024-12-31T23:59:59Z"),
     *             @OA\Property(property="assignee_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440001")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Child task added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Child task added successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001"),
     *                 @OA\Property(property="title", type="string", example="Setup database"),
     *                 @OA\Property(property="description", type="string", nullable=true, example="Create database schema"),
     *                 @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "archived"}, example="pending"),
     *                 @OA\Property(property="completed", type="boolean", example=false),
     *                 @OA\Property(property="due_date", type="string", format="date-time", nullable=true, example="2024-12-31T23:59:59Z"),
     *                 @OA\Property(property="assignee_id", type="string", format="uuid", nullable=true, example="550e8400-e29b-41d4-a716-446655440001"),
     *                 @OA\Property(property="parent_task_id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-15T14:45:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", example={"title": {"The title field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You do not have permission to manage child tasks")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Parent task not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Parent task not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while adding the child task")
     *         )
     *     )
     * )
     */
    private function addChild()
    {
    }

    /**
     * @OA\Delete(
     *     path="/api/tasks/children/{childId}",
     *     summary="Remove a child task",
     *     tags={"Tasks"},
     *     description="Removes a child task from its parent. Requires TASK_MANAGE_CHILDREN permission.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="childId",
     *         in="path",
     *         description="Child task ID to remove",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440001")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Child task removed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Child task removed successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You do not have permission to manage child tasks")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Child task not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Child task not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while removing the child task")
     *         )
     *     )
     * )
     */
    private function removeChild()
    {
    }
}
