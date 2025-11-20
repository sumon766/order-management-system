# E-Commerce Order Management System

## Project Overview
A scalable REST API for an e-commerce order management system with inventory tracking, built with Laravel 12. This system provides complete order processing, product management, and inventory tracking capabilities with role-based access control.

## 🛠 Built With

### Tech Stack
- **Backend**: Laravel 12, PHP 8.2+
- **Authentication**: Laravel Sanctum
- **Database**: MySQL 8.0+
- **Queue**: Database Queue
- **Testing**: PHPUnit
- **Documentation**: Swagger/OpenAPI
- **API**: RESTful Architecture

### Key Features
- **Product & Inventory Management** - CRUD operations with variants and real-time stock tracking
- **Order Processing Workflow** - Pending → Processing → Shipped → Delivered → Cancelled
- **Role-based Access Control** - Admin, Vendor, and Customer roles with granular permissions
- **Real-time Inventory Tracking** - Automatic stock deduction/restoration on order confirmation/cancellation
- **Low Stock Alerts** - Queue-based notifications for inventory management
- **Bulk Product Import** - CSV import functionality for vendors
- **Product Search** - Full-text search capabilities
- **PDF Invoice Generation** - Automated invoice generation for orders
- **Email Notifications** - Order status updates and system notifications
- **Comprehensive Testing Suite** - Feature and unit tests with high coverage

## 🚀 API Documentation

### Swagger UI
Access interactive API documentation at: `/api/documentation`

### API Testing with Postman

1. **Import the API**: Use the Swagger documentation to generate a Postman collection
    - Visit `/api/documentation`
    - Use the "Export" feature to download as Postman collection

2. **Environment Setup**:
    - Create a new environment in Postman
    - Add variables:
        - `base_url`: `http://localhost:8000`
        - `access_token`: Your JWT token after login

3. **Testing Flow**:
    - Start with `/api/v1/register` or `/api/v1/login` to get access token
    - Use the token in Authorization header: `Bearer {access_token}`
    - Test protected endpoints like products and orders
### Postman Collection
Import the Postman collection from `/docs/Order-Management-API.postman_collection.json`

## 💻 Getting Started

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL 8.0 or higher
- Laravel 12

### Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/sumon766/order-management-system.git
   cd order-management-system

2. **Install Dependencies**
   ```bash
    composer install

3. **Environment setup**
   ```bash
    cp .env.example .env
    php artisan key:generate

4. **Database configuration**
   ```bash
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=order-management-system
    DB_USERNAME=DB_username
    DB_PASSWORD=DB_password

5. **Run migrations and seeders**
   ```bash
    php artisan migrate
    php artisan db:seed
   
6. **Generate API documentation**
   ```bash
   php artisan l5-swagger:generate
   
7. **Start the development server**
   ```bash
   php artisan serve

### Usage
The API will be available at http://localhost:8000/api/v1

### Run tests
    php artisan test

    php artisan test tests/Feature/AuthTest.php
    php artisan test tests/Feature/ProductTest.php
    php artisan test tests/Feature/OrderTest.php

    php artisan test --coverage

### Deployment
    php artisan config:cache
    php artisan route:cache
    php artisan optimize

### Queue worker setup
    php artisan queue:work --daemon

## 👥 Author

- GitHub: [@githubhandle](https://github.com/sumon766)
- Twitter: [@twitterhandle](https://twitter.com/sumon766)
- LinkedIn: [LinkedIn](https://linkedin.com/in/sumon766)


