# Rapidev Resource Management System (RRMS)

This is a mini-ERP system built with Laravel and React. It provides basic functionalities for managing organizations, units, stores, parties, items, services, invoices, and inwards.

## Features

* Organization management
* Unit management with GST number tracking
* Store management with item and party linking
* Party management with address and party type linking
* Item and service management with categories and units of measurement
* Invoice management with detailed breakdowns
* Inward management with purchase order linking
* Item issue management for tracking consumption
* User authentication with email or phone number
* Role-based access control with permissions
* Audit logging for tracking changes

## Technologies Used

* Laravel 7
* React
* MySQL (or other database of your choice)
* Sanctum (for API authentication)
* Spatie Laravel-permission (for roles and permissions)
* OwenIt Laravel-auditing (for audit logging)

## Installation

1. Clone the repository: `git clone <repository_url>`
2. Install dependencies:
    * Backend: `composer install`
    * Frontend: `npm install`
3. Configure the environment:
    * Copy `.env.example` to `.env` and update the database credentials and other settings.
4. Run migrations: `php artisan migrate`
5. Seed the database (optional): `php artisan db:seed`
6. Generate an application key: `php artisan key:generate`
7. Start the development servers:
    * Backend: `php artisan serve`
    * Frontend: `npm start`

## API Documentation

The API documentation is available at `<api_documentation_url>`. (You can generate API documentation using tools like Swagger or Postman.)

## Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues.

## License

This project is licensed under the MIT License.