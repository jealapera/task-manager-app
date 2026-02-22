# Task Manager App

A simple task manager built for Matt, a GoTeam recruiter who wants to keep his tasks organized and on track each day.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12, PHP 8.2+, Sanctum, SQLite |
| Frontend | Nuxt 4, Vue 3, TypeScript, Pinia, Tailwind CSS v4 |
| Testing | Pest PHP (36 tests, 94 assertions) |
| Package Manager | pnpm |

## Features

- Add tasks with a single line — no descriptions needed
- Mark tasks complete/incomplete
- Inline edit — click the pencil or the task text; confirm with Enter or ✓, cancel with Escape or ✗
- Delete tasks with a confirmation dialog
- Search across all dates (debounced, server-side)
- Browse by date from the sidebar; collapses to a drawer on mobile
- Sort by custom order, priority, or date added
- Drag and drop to reorder (saved to the backend)
- Priority badge — click to cycle None / Low / Medium / High
- Mobile responsive

## How It's Built

### Backend

- **Repository Pattern** — All database queries go through `TaskRepository`. Controllers only handle HTTP concerns.
- **Sanctum** — Token-based auth. Login returns a Bearer token; all task routes require it via `auth:sanctum` middleware.
- **Form Requests** — Every endpoint that writes data has its own Form Request class: `LoginRequest`, `StoreTaskRequest`, `UpdateTaskRequest`, `ReorderTasksRequest`.
- **SQLite** — Zero-config setup, no separate database server needed.
- **Pest PHP** — 36 tests, 94 assertions covering auth, CRUD, reordering, search, and authorization.

### Frontend

- **Nuxt 4** — App code lives under `app/` following the Nuxt 4 convention.
- **Pinia stores** — `useAuthStore` and `useTaskStore` are both composition-style stores. Components read state directly from the store — no prop drilling.
- **TypeScript** — All types defined in `types/index.ts` (`Task`, `User`, `Priority`, etc.).
- **`$fetch` plugin** — Centralized API client that injects the Bearer token on every request and redirects to login on 401.
- **Component structure** — `TaskItem.vue`, `TaskList.vue`, `TaskInput.vue`, `SearchBar.vue` keep each concern in its own file.

## Project Structure

```
task-manager-app/
├── backend/                   # Laravel 12 API
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   ├── Http/Requests/
│   │   ├── Http/Resources/
│   │   ├── Models/
│   │   ├── Policies/
│   │   └── Repositories/
│   ├── database/seeders/
│   ├── routes/api.php
│   └── tests/Feature/
└── frontend/                  # Nuxt 4 SPA
    └── app/
        ├── components/
        │   ├── task/          # TaskItem, TaskList, TaskInput
        │   └── ui/            # SearchBar
        ├── middleware/
        ├── pages/
        ├── plugins/
        ├── stores/
        └── types/
```

## Setup

### Requirements

- PHP 8.2+, Composer, Node 22+, pnpm 10+
- Docker Desktop (optional, for Laravel Sail)

### Backend

**Without Docker:**
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
# API at http://127.0.0.1:8000
```

**With Laravel Sail (Docker):**
```bash
cd backend
composer install
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
# API at http://localhost
```

### Frontend

```bash
cd frontend
pnpm install
cp .env.example .env    # set NUXT_PUBLIC_API_BASE=http://127.0.0.1:8000/api
pnpm dev --port 3000
# App at http://localhost:3000
```

> If using Sail, set `NUXT_PUBLIC_API_BASE=http://localhost/api` instead.

### Login credentials

- Email: `matt@goteam.ph`
- Password: `Pa$$w0rd!`

### Running tests

```bash
# Local
cd backend && php artisan test

# With Sail
cd backend && ./vendor/bin/sail artisan test
```

Expected: **36 tests, 94 assertions, 0 failures**

Covers: auth (login/logout), task CRUD, reordering, search (cross-date), input validation, and authorization (users cannot access each other's tasks).
