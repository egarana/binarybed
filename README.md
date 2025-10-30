# Binarybed

A contemporary Laravel starter kit that pairs Inertia.js with Vue 3 for building polished single-page applications with minimal setup.

## Highlights
- **Modern full-stack**: Laravel 12 backend with Inertia.js and Vue 3 on the client.
- **Product-ready UI**: Tailwind CSS v4, prebuilt layouts, and utility components to ship interfaces faster.
- **Authentication included**: Laravel Fortify and Spatie Permission provide secure auth and role management.
- **Multi-tenant capable**: `stancl/tenancy` enables scalable multi-tenant architectures out of the box.
- **Developer tooling**: Vite, TypeScript, ESLint, and Prettier keep the workflow fast and consistent.

## Tech Stack
- PHP 8.2+
- Laravel 12
- Inertia.js & Vue 3
- Tailwind CSS v4
- TypeScript

## Getting Started
1. Clone the repository and install dependencies.
   ```bash
   git clone <repository-url>
   cd binarybed
   composer install
   npm install
   ```
2. Create your environment file and generate the application key.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Run database migrations (and optionally seed demo data).
   ```bash
   php artisan migrate
   php artisan db:seed # optional
   ```
4. Launch the development servers.
   ```bash
   npm run dev
   php artisan serve
   ```
   Visit the app at `http://localhost:8000`.

## Useful Scripts
| Command | Description |
| ------- | ----------- |
| `php artisan test` | Run the automated test suite. |
| `npm run lint` | Lint the frontend codebase with ESLint. |
| `npm run format` | Format frontend files with Prettier. |

## Project Structure
| Path | Purpose |
| ---- | ------- |
| `app/` | Core Laravel application code (models, controllers, actions). |
| `resources/` | Vue, Inertia, Blade views, and frontend assets. |
| `routes/` | Web, API, and other route definitions. |
| `database/` | Migrations, factories, and seeders. |
| `tests/` | Feature and unit tests powered by Pest. |

## License
Distributed under the [MIT license](LICENSE). Feel free to adapt it to your own projects.
