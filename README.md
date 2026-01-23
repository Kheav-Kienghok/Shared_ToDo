# Shared To-Do Backend – Beginner-Friendly Docs

**Stack:** Laravel (API-only) · PostgreSQL · Redis  
**Base URL:** `http://localhost:8000/api`  
**API Docs (Local):** [http://localhost:8000/docs/api](http://localhost:8000/docs/api)  
**API Docs (Production):** [https://shared-todo-v1-0.onrender.com/docs/api](https://shared-todo-v1-0.onrender.com/docs/api)

> This is a simple guide to quickly understand the backend and how to interact with it.

---

  **Note:** The production server may take a few seconds to spin up. To test endpoints in the API docs, switch the server from **Live (http)** to **Prod (https)** for proper HTTPS requests.

## Database Diagram

> Visual representation of tables and their relationships.

![Database Diagram](public/Diagram.png)

---

## 1. Users & Authentication

**What it does:**  
Handles signing up, logging in, logging out, and getting info about the logged-in user.

### Database Table: `users`

| Column     | What it stores                  |
|----------- |-------------------------------- |
| id         | Unique ID for the user          |
| name       | User’s name                     |
| email      | User’s email (must be unique)   |
| password   | Encrypted password              |
| created_at | When the user was created       |
| updated_at | When info was last updated      |

### API Endpoints

| Method | Endpoint       | What it does                              |
|--------|--------------- |------------------------------------------ |
| POST   | /auth/register | Sign up a new user                        |
| POST   | /auth/login    | Login and get a token                     |
| POST   | /auth/logout   | Log out the user                          |
| GET    | /auth/profile  | Get info about the logged-in user         |

---

## 2. To-Do Lists

**What it does:**  
Create, view, update, and archive your lists.

### Database Table: `lists`

| Column      | What it stores                       |
|------------ |------------------------------------- |
| id          | Unique ID for the list               |
| name        | Name of the list                     |
| description | Optional description of the list     |
| is_archived | Whether the list is archived or not  |
| created_at  | When the list was created            |
| updated_at  | Last time the list was updated       |

### Database Table: `list_user`

**Handles which users can access a list and their role**

| Column    | What it stores                         |
|---------- |--------------------------------------  |
| list_id   | ID of the list                         |
| user_id   | ID of the user                         |
| role      | Role in the list: owner, editor, viewer|
| created_at| When the user was added                |
| updated_at| Last time role changed                 |

### API Endpoints

| Method | Endpoint           | What it does                           |
|--------|------------------  |--------------------------------------- |
| POST   | /lists             | Create a new list                      |
| GET    | /lists             | See all your lists                     |
| PATCH  | /lists/{list_id}   | Update list name or description        |
| DELETE | /lists/{list_id}   | Archive or delete a list               |

---

## 3. Sharing Lists & Permissions

**What it does:**  
Let other users access your lists and control what they can do.

### API Endpoints

| Method | Endpoint                          | What it does                                         |
|--------|-----------------------------------|---------------------------------------               |
| POST   | /lists/{list_id}/share            | Share a list with another user                       |
| PATCH  | /lists/{list_id}/users/{user_id}  | Change that user’s role (owner/collaborator viewer)  |
| DELETE | /lists/{list_id}/users/{user_id}  | Remove that user from the list                       |

---

## 4. Tasks

**What it does:**  
Manage tasks under each list — create, view, update, complete, or assign them.

### Database Table: `tasks`

| Column       | What it stores                            |
|------------- |-----------------------------------------  |
| id           | Unique ID for the task                    |
| list_id      | ID of the list this task belongs to       |
| title        | Title of the task                         |
| description  | Optional description                      |
| assigned_to  | User ID of the person assigned            |
| status       | Status: todo, in_progress, done           |
| priority     | Priority number (e.g., 1 = highest)       |
| due_date     | When the task is due                      |
| completed_at | Timestamp when task was completed         |
| reminder_at  | Optional reminder timestamp               |
| created_by   | User ID who created the task              |
| created_at   | When the task was created                 |
| updated_at   | Last update timestamp                     |

### API Endpoints

| Method | Endpoint                       | What it does                                                           |
|--------|--------------------------------|------------------------------------------------                        |
| POST   | /lists/{list_id}/tasks         | Create a new task under a list                                         |
| GET    | /lists/{list_id}/tasks         | Get all tasks for a list (with optional filters: status, due, overdue) |
| PATCH  | /tasks/{task_id}               | Update a task’s details                                                |
| DELETE | /tasks/{task_id}               | Delete a task                                                          |
| POST   | /tasks/{task_id}/complete      | Mark a task as completed                                               |
| POST   | /tasks/{task_id}/assign        | Assign a task to a user                                                |

### Example Query Filters

- `GET /tasks?status=todo` → Get all tasks with status “todo”  
- `GET /tasks?due=today` → Get tasks due today  
- `GET /tasks?overdue=true` → Get overdue tasks (status not done)

---

## Quick Summary for Beginners

1. **Users table** → manages user accounts  
2. **Lists table** → stores your to-do lists  
3. **list_user table** → controls which users see which lists and their permissions  
4. **Endpoints** → how the frontend talks to the backend  

> ✅ All features are implemented, tested, and ready.  
> 📌 Check the API in your browser: [http://localhost:8000/docs/api](http://localhost:8000/docs/api)
