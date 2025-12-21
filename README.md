# 🎫 Service Desk Pro

A modern, high-performance ticket management system built with **Symfony 7.4** and **PHP 8.2+**
[![Symfony Tests](https://github.com/UDKoperek/service-desk-symfony/actions/workflows/tests.yaml/badge.svg)](https://github.com/UDKoperek/service-desk-symfony/actions/workflows/tests.yaml)

---

## 🚀 Key Features

### 🔐 Security & Access Control
* **Email Verification**: Secure registration flow with link verification via `VerifyEmailBundle`.
* **Role Hierarchy**: 
    * `ROLE_ADMIN`: Full system management (Categories/Users).
    * `ROLE_AGENT`: Global ticket handling and responses.
    * `ROLE_USER`: Personal ticket management.
* **Voters**: Strict authorization logic to protect tickets and comments.

### 📧 Communication & Backend
* **Advanced Search**: Filtering and pagination (KnpPaginator) for efficient ticket management.

---

## 💻 Tech Stack

| Component | Technology |
| :--- | :--- |
| **Backend** | Symfony 7.4 (PHP 8.2+) |
| **Database** | MySQL (Doctrine ORM) |
| **Mailing** | Symfony Mailer + SendGrid |
| **Testing** | PHPUnit + Symfony BrowserKit |

---

## 🔧 Installation & Setup

### 1. Prerequisites
* **PHP 8.2** or higher
* **Composer**
* **MySQL**
* **Docker**

### 2. Clone and Install
bash
```git clone [https://github.com/your-username/service-desk.git](https://github.com/your-username/service-desk.git)```
```cd "C:\your\path\service-desk-symphony-main"```
```composer install```

3. Environment Configuration ⚙️
Create a local environment file:
bash
```cp .env .env.local```

Update your .env.local with your credentials:

Code snippet:
```# Database ```
```DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/service-desk-symphony-main?serverVersion=10.4.32-MariaDB&charset=utf8mb4"```

```# Mailer (SendGrid Example)```
```MAILER_DSN=sendgrid://KEY@default```




4. Database Initialization
bash
```php bin/console doctrine:database:create```
```php bin/console doctrine:migrations:migrate --no-interaction```

# Load test data (Admin, Agents, Tickets)
```php bin/console doctrine:fixtures:load --no-interaction```


5. Running the App
bash
```php -S 127.0.0.1:8000 -t public```

🧪 Testing
The project uses PHPUnit for functional and unit testing.

# Prepare test database
bash
```php bin/console --env=test doctrine:database:create```
```php bin/console --env=test doctrine:migrations:migrate --no-interaction```

# Run tests
bash
```php bin/phpunit```


# Service Desk Project 🎫

A professional ticket management system built with Symfony 7, PHP 8.2, and a Dockerized architecture.

## 🚀 Quick Start

To run this project locally, ensure you have **Docker Desktop** installed and running.

# 1. Build and start the containers:

bash
```docker compose up -d --build```

Install PHP dependencies:

bash
```docker compose exec php composer install```

Run database migrations:

Bash

```docker compose exec php bin/console doctrine:migrations:migrate```

## 🛠 Architecture
The project runs in an isolated Linux environment using Docker:

PHP: 8.2-fpm (running as www-data user)

Database: MySQL 8.0

Web Server: Nginx

API Layer: API Platform (JSON format) !!!COMMING SOON!!!

## 🌐 Local Endpoints
Web Application: http://localhost

API Documentation (Swagger): http://localhost/api !!!COMMING SOON!!!

Database Manager: http://localhost:8080 (phpMyAdmin)

##⚠️ Troubleshooting
Line Endings (Git LF vs CRLF)
This project forces LF line endings for compatibility with the Linux-based Docker environment. Git is configured to handle this automatically via .gitattributes.

Permission Errors (Dubious Ownership)
If you encounter a "dubious ownership" error when running Git commands inside the container, run:
bash
```docker compose exec php git config --global --add safe.directory /var/www/html```
