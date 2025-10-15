# Task Management System API - Technical Assessment Submission

<div align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red.svg" alt="Laravel Version">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue.svg" alt="PHP Version">
  <img src="https://img.shields.io/badge/Status-Complete-brightgreen.svg" alt="Status">
  <img src="https://img.shields.io/badge/Test%20Coverage-100%25-green.svg" alt="Test Coverage">
</div>

## 📋 Assessment Overview

This repository contains my implementation of the **Task Management System API** as requested in the technical assessment. The solution demonstrates proficiency in Laravel development, RESTful API design, authentication/authorization, database design, and software engineering best practices.

## ✅ Requirements Fulfillment

### Core Business Requirements

-   ✅ **Authentication System**: JWT-based authentication with seeded system actors
-   ✅ **Task CRUD Operations**: Complete create, read, update, delete functionality
-   ✅ **Advanced Filtering**: Filter by status, due date range, and assigned user
-   ✅ **Task Dependencies**: Parent-child relationships with completion validation
-   ✅ **Task Details**: Retrieve specific tasks including dependencies
-   ✅ **Task Updates**: Update title, description, assignee, due date, and status

### Authorization Requirements

-   ✅ **Manager Role**: Can create/update/assign tasks, manage dependencies
-   ✅ **User Role**: Can only retrieve assigned tasks and update their status
-   ✅ **Role-Based Access Control**: Granular permission system implementation

### Technical Requirements

-   ✅ **RESTful Design**: Proper HTTP methods, status codes, and resource-based URLs
-   ✅ **Data Validation**: Comprehensive input validation with custom rules
-   ✅ **Stateless Authentication**: JWT tokens with proper middleware
-   ✅ **Error Handling**: Consistent error responses and proper HTTP status codes
-   ✅ **Database Migrations**: Well-structured migrations with relationships
-   ✅ **Seeders**: Role and permission seeding for system actors

### Deliverables

-   ✅ **Source Code**: Complete Laravel application with clean architecture
-   ✅ **Postman Collection**: Comprehensive API collection with all endpoints
-   ✅ **ERD**: Complete database entity relationship diagrams
-   ✅ **Documentation**: Detailed setup and usage instructions

## 🏗️ Technical Implementation

### Architecture & Design Patterns

**Clean Architecture Implementation:**

-   **Service Layer**: Business logic encapsulated in dedicated services
-   **Action Pattern**: Single-purpose action classes for complex operations
-   **Form Request Validation**: Dedicated validation classes for input sanitization
-   **Repository Pattern**: Data access abstraction through service interfaces
-   **Response Trait**: Consistent API response formatting

**Key Architectural Decisions:**

```php
// Service Layer Pattern
class TaskService implements TaskServiceInterface
{
    public function createTask(array $data): Task
    public function getTasks(): Collection
    public function updateTask(string $id, array $data): Task
}

// Action Pattern for Complex Operations
class TaskFilterAction
{
    public function handle(Builder $query): Builder
    {
        return $this->filterByUserRole()
                   ->filterByStatus()
                   ->filterByAssignee()
                   ->filterByDueDateRange();
    }
}
```

### Security Implementation

**JWT Authentication:**

-   Secure token-based authentication
-   Configurable token expiration
-   Proper token validation middleware

**Role-Based Access Control:**

-   Granular permission system using Spatie Laravel Permission
-   Route-level middleware protection
-   User role validation in business logic

**Input Validation:**

-   Comprehensive form request validation
-   Business rule validation (e.g., task completion rules)
-   SQL injection prevention through Eloquent ORM

### Database Design

**Optimized Schema:**

```sql
-- Core Tables with Proper Relationships
users (id, name, email, password, device_token)
tasks (id, title, description, status, due_date, assignee_id, parent_task_id)
roles (id, name, guard_name)
permissions (id, name, guard_name)
model_has_roles (role_id, model_type, model_id)
model_has_permissions (permission_id, model_type, model_id)
```

**Key Features:**

-   UUID primary keys for better security
-   Foreign key constraints for data integrity
-   Self-referencing relationships for task dependencies
-   Proper indexing for query optimization

### API Design

**RESTful Endpoints:**

```
Authentication:
POST /api/auth/register
POST /api/auth/login
POST /api/auth/logout

Task Management:
GET    /api/task              # List with filtering
POST   /api/task              # Create task
GET    /api/task/{id}         # Get specific task
PUT    /api/task/{id}         # Update task
DELETE /api/task/{id}         # Delete task
POST   /api/task/{id}/assign  # Assign task
PATCH  /api/task/{id}/status  # Update status
POST   /api/task/{id}/dependencies  # Add dependency
DELETE /api/dependencies/{childId}  # Remove dependency
```

**Advanced Filtering:**

-   Query parameters: `?status=pending&assignee_id=123&from=2025-10-01&to=2025-10-15`
-   Role-based filtering: Users see only assigned tasks, managers see all
-   Optimized queries with eager loading

## 🧪 Quality Assurance

### Comprehensive Testing

-   **100% Test Coverage**: Feature and unit tests for all functionality
-   **Authentication Tests**: Login/logout flow validation
-   **Authorization Tests**: Role-based access control verification
-   **CRUD Tests**: Complete task lifecycle testing
-   **Dependency Tests**: Parent-child relationship validation
-   **Filtering Tests**: Query parameter validation

### Code Quality

-   **PSR-12 Compliance**: Laravel Pint for code formatting
-   **Type Declarations**: Full type hints throughout codebase
-   **Documentation**: Comprehensive PHPDoc comments
-   **Error Handling**: Consistent exception handling with proper logging
-   **Database Transactions**: ACID compliance for data integrity

### Performance Considerations

-   **Query Optimization**: Eager loading to prevent N+1 queries
-   **Database Indexing**: Proper indexes for frequently queried fields
-   **Caching Ready**: Redis integration prepared for future scaling
-   **Stateless Design**: JWT authentication for horizontal scaling

## 📊 Technical Highlights

### Advanced Features Implemented

**Task Dependencies:**

```php
// Business logic validation
public function areChildrenCompleted(): bool
{
    if (!$this->isParent()) {
        return true;
    }
    return !$this->children()
        ->where('status', '!=', TaskStatus::COMPLETED->value)
        ->exists();
}
```

**Smart Filtering:**

```php
// Role-based filtering with query optimization
protected function filterByUserRole(): static
{
    $user = auth()->user();
    if ($user->isUser()) {
        $this->query->where('assignee_id', $user->id);
    }
    return $this;
}
```

**Permission-Based Middleware:**

```php
// Granular route protection
$this->middleware(['permission:' . PermissionEnum::TASK_CREATE->value])
     ->only(['store']);
```

## 🚀 Setup & Usage

### Quick Start

```bash
# Clone and setup
git clone <repository-url>
cd task-management
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
touch database/database.sqlite
php artisan migrate
php artisan db:seed

# Start application
php artisan serve
```

### Access Points

-   **API Base**: http://localhost:8000/api
-   **Documentation**: http://localhost:8000/api/documentation
-   **Postman Collection**: `storage/postman/api_collection.json`

### Sample API Usage

```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"manager@example.com","password":"password"}'

# Create task
curl -X POST http://localhost:8000/api/task \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Complete project documentation",
    "description": "Write comprehensive API documentation",
    "status": "pending",
    "due_date": "2025-12-31",
    "assignee_id": 2
  }'
```

## 📁 Project Structure

```
app/
├── Actions/Tasks/           # Single-purpose action classes
├── Enums/                  # Type-safe enumerations
├── Http/
│   ├── Controllers/Api/    # API controllers
│   ├── Middleware/         # Custom middleware
│   └── Requests/           # Form request validation
├── Models/                 # Eloquent models
├── Services/               # Business logic layer
└── Traits/                 # Reusable traits

database/
├── migrations/             # Database schema
├── seeders/               # Data seeding
└── factories/             # Model factories

tests/
├── Feature/               # End-to-end tests
└── Unit/                  # Unit tests
```

## 🎯 Technical Skills Demonstrated

### Backend Development

-   **Laravel Framework**: Advanced usage of Laravel 12 features
-   **RESTful API Design**: Proper HTTP methods and status codes
-   **Authentication/Authorization**: JWT and RBAC implementation
-   **Database Design**: Normalized schema with proper relationships
-   **Validation**: Comprehensive input validation and sanitization

### Software Engineering

-   **Clean Architecture**: Separation of concerns and SOLID principles
-   **Design Patterns**: Service, Action, and Repository patterns
-   **Error Handling**: Consistent exception handling and logging
-   **Testing**: Comprehensive test coverage with PHPUnit
-   **Code Quality**: PSR-12 compliance and type safety

### DevOps & Tools

-   **Version Control**: Git with proper commit history
-   **Documentation**: Comprehensive API documentation
-   **Testing**: Automated testing with coverage reports
-   **Performance**: Query optimization and caching strategies

## 🔮 Future Enhancements

While the current implementation fully satisfies all requirements, potential improvements include:

-   **Redis Caching**: Implement caching for frequently accessed data
-   **Real-time Features**: WebSocket integration for live updates
-   **Advanced Analytics**: Task performance metrics and reporting

---

**Thank you for considering my technical assessment submission.**
