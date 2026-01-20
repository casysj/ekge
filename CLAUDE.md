# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

EKGE Church website - a Korean church website in Essen, Germany. This is a full-stack web application with a PHP backend using Laminas Framework and a Vue.js frontend.

**Tech Stack:**
- Backend: Laminas Framework (PHP 8.2), Doctrine ORM
- Database: MariaDB 10.11
- Frontend: Vue.js 3, Vue Router, Tailwind CSS, Vite
- Infrastructure: Docker Compose with Nginx, PHP-FPM, MariaDB, Adminer

## Development Commands

### Docker Operations
```bash
# Start all containers
docker-compose up -d

# Stop all containers
docker-compose down

# View logs
docker-compose logs -f

# Access PHP container
docker-compose exec php bash

# Access MariaDB
docker-compose exec mariadb mysql -u root -p
```

### Backend (Laminas/PHP)
```bash
# Inside PHP container (docker-compose exec php bash):

# Install dependencies
composer install

# Run tests
./vendor/bin/phpunit

# Code style check
composer cs-check

# Fix code style
composer cs-fix

# Static analysis
composer static-analysis

# Clear config cache (required after config changes)
composer clear-config-cache

# Development mode
composer development-enable
composer development-disable
composer development-status
```

### Frontend (Vue.js)
```bash
# Inside frontend container or locally:

# Install dependencies
npm install

# Dev server (runs on port 5173)
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview
```

### Database Operations
```bash
# Backup database
docker-compose exec mariadb mysqldump -u root -p ekge_church > backup.sql

# Restore database
docker-compose exec -T mariadb mysql -u root -p ekge_church < backup.sql

# Access Adminer (web UI)
# Navigate to http://localhost:8081
# Server: mariadb, credentials in .env
```

## Architecture

### Backend Architecture (Laminas MVC)

**Module Structure:**
- Single module: `Application` (located in `src/module/Application/`)
- Uses Laminas MVC pattern with Controllers, Services, and Doctrine ORM

**Key Directories:**
- `src/module/Application/src/Controller/` - MVC controllers with JSON API endpoints
- `src/module/Application/src/Service/` - Business logic services
- `src/module/Application/src/Entity/` - Doctrine ORM entities (PHP 8 attributes)
- `src/module/Application/src/Repository/` - Doctrine repositories
- `src/module/Application/config/module.config.php` - Route and service configuration
- `src/config/autoload/` - Application configuration (database, doctrine, etc.)

**Configuration Pattern:**
- Routes defined in [module.config.php](src/module/Application/config/module.config.php)
- Controllers use Factory pattern for dependency injection
- Services registered in service_manager with factories
- Doctrine entities use PHP 8 attributes (not annotations)
- Database config via environment variables in [doctrine.global.php](src/config/autoload/doctrine.global.php)

**Key Controllers:**
- `BoardController` - Public board/post API endpoints
- `AdminController` - Admin authentication, post CRUD, file uploads
- `MenuController` - Menu/navigation API
- `FileController` - File serving (attachments, images)

**Key Services:**
- `BoardService` - Board operations and metadata
- `PostService` - Post CRUD operations
- `AuthenticationService` - Session-based admin authentication
- `FileUploadService` - File upload handling
- `AttachmentService` - Attachment management
- `MenuService` - Menu/navigation management

**Doctrine ORM:**
- Entities: Post, Board, User, Menu, MenuContent, Attachment, Banner, Setting
- Uses AttributeDriver for entity mapping (PHP 8 attributes)
- Repository pattern for database queries
- Relationships: Post belongs to Board and User; Board has many Posts; etc.

### Frontend Architecture (Vue.js 3)

**Structure:**
- `frontend/src/views/` - Page components (Home, BoardList, BoardDetail, Gallery, About, admin/*)
- `frontend/src/components/` - Reusable components
- `frontend/src/layouts/` - Layout components (AdminLayout)
- `frontend/src/router/` - Vue Router configuration
- `frontend/src/composables/` - Composition API reusable logic (useAuth)
- `frontend/src/services/` - API service modules (adminService.js, etc.)
- `frontend/src/assets/` - Static assets

**Routing:**
- Public routes: Home (/), BoardList (/board/:code), BoardDetail (/board/:code/:id), Gallery, About
- Admin routes: Login (/admin/login), Dashboard (/admin), Posts management (/admin/posts)
- Auth guard: Checks authentication for routes with `requiresAuth: true` meta
- Uses `useAuth` composable for authentication state

**API Communication:**
- Services use axios for API calls to backend at `/api/*` endpoints
- Admin endpoints require session authentication
- File serving via `/api/files/:id` or `/api/files/path/:path`

### API Endpoints

**Public APIs:**
- `GET /api/boards` - List boards
- `GET /api/boards/:code/posts` - List posts for a board
- `GET /api/posts/:id` - View single post
- `GET /api/menus` - List menus
- `GET /api/menus/:id` - Get menu by ID
- `GET /api/files/:id` - Serve file by attachment ID
- `GET /api/files/path/:path` - Serve file by path

**Admin APIs (authentication required):**
- `POST /api/admin/login` - Admin login
- `POST /api/admin/logout` - Admin logout
- `GET /api/admin/me` - Get current admin user
- `POST /api/admin/posts` - Create post
- `PUT /api/admin/posts/:id` - Update post
- `POST /api/admin/upload` - Upload file
- `DELETE /api/admin/attachments/:id` - Delete attachment
- `GET /api/admin/stats` - Get dashboard statistics

## Access URLs

- **Main Website:** http://localhost:8080
- **Frontend Dev Server:** http://localhost:5173
- **Adminer (Database UI):** http://localhost:8081

## Development Standards

- **PHP Coding Standard:** PSR-12
- **Git Strategy:** Git Flow
- **Commit Messages:** Conventional Commits format
- **PHP Version:** 8.2+
- **Database Charset:** UTF8MB4

## Environment Setup

1. Copy `.env.example` to `.env` and configure database credentials
2. Run `docker-compose up -d` to start all services
3. Database schema is initialized automatically via `docker/mariadb/init.sql`
4. Backend available at http://localhost:8080
5. Frontend dev server at http://localhost:5173

## Important Notes

- The backend serves as a JSON API; frontend is a separate SPA
- Session-based authentication (not JWT) for admin routes
- File uploads stored in `data/uploads/`
- Logs stored in `logs/` directory
- MariaDB data persisted in `data/mysql/`
- Config cache must be cleared after configuration changes: `composer clear-config-cache`
- Doctrine proxies generated in `data/DoctrineORMModule/Proxy/`
