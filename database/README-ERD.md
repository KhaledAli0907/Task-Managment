# Task Management System - Entity Relationship Diagrams

This directory contains Mermaid ERD diagrams for the Task Management System database schema.

## Files

### 1. `erd.mmd` - Complete ERD

-   **Purpose**: Full database schema with all tables and relationships
-   **Use Case**: Complete system documentation, database design review
-   **Includes**: All tables including Laravel system tables (sessions, password_reset_tokens)

### 2. `erd-detailed.mmd` - Detailed ERD with Descriptions

-   **Purpose**: Comprehensive ERD with field descriptions and constraints
-   **Use Case**: Developer documentation, database implementation guide
-   **Includes**: Field descriptions, data types, constraints, and relationship explanations

### 3. `erd-simplified.mmd` - Simplified Business ERD

-   **Purpose**: Core business entities and relationships only
-   **Use Case**: Business analysis, stakeholder presentations, system overview
-   **Includes**: Main entities (users, tasks, roles, permissions) with primary relationships

## How to View the Diagrams

### Option 1: GitHub/GitLab (Recommended)

-   GitHub and GitLab automatically render Mermaid diagrams
-   Simply view the `.mmd` files in your repository
-   No additional setup required

### Option 2: Mermaid Live Editor

1. Go to [Mermaid Live Editor](https://mermaid.live/)
2. Copy the content from any `.mmd` file
3. Paste into the editor
4. View the rendered diagram

### Option 3: VS Code Extension

1. Install the "Mermaid Preview" extension
2. Open any `.mmd` file
3. Use `Ctrl+Shift+P` → "Mermaid Preview"

### Option 4: Command Line (with Mermaid CLI)

```bash
# Install Mermaid CLI
npm install -g @mermaid-js/mermaid-cli

# Generate PNG
mmdc -i erd.mmd -o erd.png

# Generate SVG
mmdc -i erd.mmd -o erd.svg
```

## Database Schema Overview

### Core Tables

#### Users Table

-   **Primary Key**: `id` (bigint, auto-increment)
-   **Unique Fields**: `email`
-   **Relationships**:
    -   One-to-Many with Tasks (assignee)
    -   Many-to-Many with Roles
    -   Many-to-Many with Permissions

#### Tasks Table

-   **Primary Key**: `id` (uuid)
-   **Foreign Keys**:
    -   `assignee_id` → users.id
    -   `parent_task_id` → tasks.id (self-referencing)
-   **Status Enum**: pending, in_progress, completed, archived
-   **Relationships**:
    -   Many-to-One with Users (assignee)
    -   One-to-Many with Tasks (parent-child hierarchy)

#### Roles Table

-   **Primary Key**: `id` (bigint, auto-increment)
-   **Unique Fields**: `name` (manager, user, super_admin)
-   **Relationships**: Many-to-Many with Users and Permissions

#### Permissions Table

-   **Primary Key**: `id` (bigint, auto-increment)
-   **Unique Fields**: `name` (TASK_CREATE, TASK_READ, etc.)
-   **Relationships**: Many-to-Many with Users and Roles

### Pivot Tables (Spatie Permission Package)

#### model_has_roles

-   Links users to their assigned roles
-   Composite primary key: (role_id, model_type, model_id)

#### model_has_permissions

-   Links users to their direct permissions
-   Composite primary key: (permission_id, model_type, model_id)

#### role_has_permissions

-   Links roles to their permissions
-   Composite primary key: (permission_id, role_id)

### System Tables

#### password_reset_tokens

-   Laravel's password reset functionality
-   Primary key: `email`

#### sessions

-   Laravel's session management
-   Primary key: `id` (session ID)

## Key Relationships

1. **User-Task Assignment**: Users can be assigned to multiple tasks
2. **Task Hierarchy**: Tasks can have parent-child relationships (self-referencing)
3. **Role-Based Access Control**: Users have roles, roles have permissions
4. **Direct Permissions**: Users can also have direct permissions (bypassing roles)

## Usage Examples

### For Developers

-   Use `erd-detailed.mmd` for implementation reference
-   Reference field types and constraints during development
-   Understand relationship cardinalities for query optimization

### For Business Analysts

-   Use `erd-simplified.mmd` for business process understanding
-   Focus on core entities and their relationships
-   Understand data flow and business rules

### For System Architects

-   Use `erd.mmd` for complete system overview
-   Understand all system components and their interactions
-   Plan for scalability and performance optimization

## Maintenance

When making database changes:

1. Update the relevant `.mmd` files
2. Test the diagrams in Mermaid Live Editor
3. Update this README if new tables or relationships are added
4. Consider versioning for major schema changes
