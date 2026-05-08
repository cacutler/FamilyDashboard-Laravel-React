# Family Dashboard

Family Dashboard is a Laravel + React application designed to help families organize events, chores, reminders, and family member profiles in a centralized workspace.

## Table of Contents

- [Project Overview](#project-overview)
- [Tech Stack](#tech-stack)
- [Key Features](#key-features)
- [Getting Started](#getting-started)
- [Local Development](#local-development)
- [Testing](#testing)
- [Project Structure](#project-structure)
- [REST Endpoints](#rest-endpoints)
- [Database Design](#database-design)
- [Future Roadmap](#future-roadmap)

## Project Overview

This application enables parents and children to collaborate around family activities and responsibilities.

- Parents can assign and manage chores, reminders, and events.
- Family members can create and edit events they own.
- Profile management supports basic user roles and permissions.
- The user experience is built with React, while backend logic uses Laravel.

## Tech Stack

- Backend: Laravel
- Frontend: React / TypeScript
- Database: MySQL / MariaDB (configured via Laravel)
- Package management: Composer and pnpm
- Build tooling: Vite

## Key Features

- Role-based permissions for parents and children
- Event creation, editing, and management
- To‑do and reminder assignment and tracking
- User profile management
- Support for family-wide sharing of tasks and schedules

## Getting Started

### Prerequisites

- PHP 8.1+ (or the version supported by the Laravel setup)
- Composer
- Node.js 18+ / pnpm
- A database server (MySQL, MariaDB, SQLite, etc.)

### Setup

1. Clone the repository:

   ```bash
   git clone https://github.com/<your-org>/FamilyDashboard-Laravel-React.git
   cd FamilyDashboard-Laravel-React
   ```

2. Install PHP dependencies:

   ```bash
   composer install
   ```

3. Install frontend dependencies:

   ```bash
   pnpm install
   ```

4. Copy the environment file and configure your database:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Run database migrations:

   ```bash
   php artisan migrate
   ```

## Local Development

### Running the application

To start the Laravel backend and Vite frontend tooling:

```bash
php artisan serve
pnpm dev
```

Then open the development URL shown by your local server.

### Frontend build

To build the frontend assets for production:

```bash
pnpm build
```

## Testing

Run the Laravel test suite with PHPUnit:

```bash
php artisan test
```

## Project Structure

- `app/` – Laravel application source code
- `config/` – Laravel configuration files
- `database/` – migrations, seeders, and factories
- `public/` – public entry point and compiled assets
- `resources/js/` – React frontend source
- `routes/` – Laravel route definitions
- `tests/` – application tests

## REST Endpoints

The application routes are defined under `routes/web.php` and `routes/settings.php`.

Currently, endpoint documentation is captured within the application route definitions. Common entry points include:

- `GET /` – landing page
- `POST /login` – authenticate a user
- `POST /register` – register a new user
- `GET /dashboard` – family dashboard view
- `GET /events` – list events
- `POST /events` – create event
- `PUT /events/{id}` – update event
- `DELETE /events/{id}` – delete event
- `GET /todos` – list to-dos
- `POST /todos` – create to-do
- `PUT /todos/{id}` – update to-do
- `DELETE /todos/{id}` – delete to-do

## Database Design

### Events

- `id`
- `user_id` – owner or creator
- `name`
- `location`
- `start_date`
- `start_time`
- `end_date`
- `end_time`
- `description`

### To Dos

- `id`
- `user_id` – creator
- `title`
- `description`
- `assigned_user_id` – user responsible for the task
- `status`

### Users

- `id`
- `name`
- `username`
- `email`
- `password`
- `status` – `parent` or `child`
- `birthdate`

## Future Roadmap

Potential future enhancements include:

- Family budgeting and expense tracking
- Meal planning and shopping lists
- Contact directory for extended family
- Announcements and messaging integration
- Chat support for family communication
- Mobile-friendly experience with location-aware features
- Weather integration and family trip planning