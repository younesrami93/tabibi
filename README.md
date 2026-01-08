# Tabibi - Medical Clinic Management System

Tabibi is a comprehensive Laravel-based SaaS platform for managing medical clinics in Morocco. It provides a multi-tenant system where clinics can subscribe and manage their operations including patient records, appointments, prescriptions, and billing.

## Features

- **Multi-Tenant Architecture**: Isolated clinic data with subscription management
- **Role-Based Access**: Super Admin, Doctors, and Secretaries with appropriate permissions
- **Patient Management**: Complete patient records with demographics and medical history
- **Appointment Scheduling**: Calendar-based booking with status tracking
- **Medical Services**: Configurable service catalog with pricing
- **Prescription Templates**: Reusable prescription formats
- **Document Generation**: Built-in document editor with printing capabilities
- **Media Library**: Image management for clinics
- **Global Search**: Quick search across patients and appointments

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Livewire, Tailwind CSS, Vite
- **Database**: MySQL with Eloquent ORM
- **Authentication**: Laravel Sanctum with role-based middleware

## Installation

1. Clone the repository
2. Run `composer install`
3. Copy `.env.example` to `.env` and configure your database
4. Run `php artisan key:generate`
5. Run `php artisan migrate`
6. Run `npm install && npm run build`
7. Start the development server: `php artisan serve`

## Usage

- Access the application at `http://localhost:8000`
- Default login credentials will be available after seeding
- Super Admin can create clinics and manage platform settings
- Clinic staff can manage patients, appointments, and documents

## Development

- Use `npm run dev` for frontend development
- Run tests with `php artisan test`
- Code style: Follow PSR-12 standards
- Use `php artisan make:*` commands for scaffolding

## Contributing

This is a solo project. For improvements or bug reports, please create an issue.

## License

This project is proprietary software.**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
