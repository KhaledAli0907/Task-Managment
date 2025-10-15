# Visual Example of the ERD

Here's what the simplified ERD looks like when rendered:

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email UK
        string password
        string device_token
        timestamp created_at
        timestamp updated_at
    }

    tasks {
        uuid id PK
        string title
        text description
        enum status
        boolean completed
        date due_date
        bigint assignee_id FK
        uuid parent_task_id FK
        timestamp created_at
        timestamp updated_at
    }

    roles {
        bigint id PK
        string name UK
        string guard_name
        timestamp created_at
        timestamp updated_at
    }

    permissions {
        bigint id PK
        string name UK
        string guard_name
        timestamp created_at
        timestamp updated_at
    }

    %% Main Business Relationships
    users ||--o{ tasks : "assigned_to"
    tasks ||--o{ tasks : "parent_child"
    users ||--o{ roles : "has_roles"
    roles ||--o{ permissions : "has_permissions"
```

## Key Features of This ERD:

### 🔑 Primary Relationships

-   **Users → Tasks**: One user can be assigned to many tasks
-   **Tasks → Tasks**: Self-referencing relationship for parent-child task hierarchy
-   **Users → Roles**: Many-to-many relationship for role-based access control
-   **Roles → Permissions**: Many-to-many relationship for permission management

### 📊 Data Types

-   **UUID**: Used for task IDs for better security and distributed systems
-   **BigInt**: Used for user and role IDs (standard Laravel auto-increment)
-   **Enum**: Task status with predefined values
-   **Boolean**: Task completion flag
-   **Timestamps**: Created/updated tracking for all entities

### 🔒 Security Features

-   **Role-Based Access Control**: Users have roles, roles have permissions
-   **Self-Referencing Tasks**: Parent-child task relationships
-   **Foreign Key Constraints**: Data integrity enforcement
-   **Unique Constraints**: Email uniqueness, role name uniqueness

### 🎯 Business Logic

-   **Task Assignment**: Users can be assigned to tasks
-   **Task Hierarchy**: Tasks can have subtasks (parent-child)
-   **Status Management**: Tasks have lifecycle statuses
-   **Due Date Tracking**: Tasks have deadline management
