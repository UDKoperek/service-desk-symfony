# 🎫 Service Desk Pro

A modern, high-performance ticket management system built with **Symfony 7.4** and **PHP 8.2+**. This application leverages **Symfony UX (Turbo/Stimulus)** for a seamless user experience and **SendGrid** for reliable automated communication.

---

## 🚀 Key Features

### ⚡ Modern Frontend (Symfony UX)
* **Single-Page Experience**: Powered by **Symfony Turbo**, providing fast navigation without full page reloads.
* **Reactive UI**: Interactive components built with **Stimulus**.
* **Asset Management**: Uses **AssetMapper**, eliminating the need for Node.js/Webpack on the production server.

### 🔐 Security & Access Control
* **Email Verification**: Secure registration flow with link verification via `VerifyEmailBundle`.
* **Role Hierarchy**: 
    * `ROLE_ADMIN`: Full system management (Categories/Users).
    * `ROLE_AGENT`: Global ticket handling and responses.
    * `ROLE_USER`: Personal ticket management.
* **Voters**: Strict authorization logic to protect tickets and comments.

### 📧 Communication & Backend
* **Mailing System**: Integrated with **SendGrid** for professional email delivery.
* **Background Processing**: **Symfony Messenger** support for asynchronous task handling (e.g., sending emails without blocking the UI).
* **Advanced Search**: Filtering and pagination (KnpPaginator) for efficient ticket management.

---

## 💻 Tech Stack

| Component | Technology |
| :--- | :--- |
| **Backend** | Symfony 7.4 (PHP 8.2+) |
| **Database** | MySQL / MariaDB (Doctrine ORM) |
| **Mailing** | Symfony Mailer + SendGrid |
| **Frontend** | Twig + Symfony UX (Turbo & Stimulus) |
| **Task Queue** | Symfony Messenger |
| **Testing** | PHPUnit + Symfony BrowserKit |

---

## 🔧 Installation & Setup

### 1. Prerequisites
* **PHP 8.2** or higher
* **Composer**
* **MySQL / MariaDB**

### 2. Clone and Install
```bash
git clone [https://github.com/your-username/service-desk.git](https://github.com/your-username/service-desk.git)
cd service-desk
composer install

3. Environment Configuration ⚙️
Create a local environment file:

Bash

cp .env .env.local
Update your .env.local with your credentials:

Code snippet

# Database
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/service_desk?serverVersion=10.4.32-MariaDB&charset=utf8mb4"

# Mailer (SendGrid Example)
MAILER_DSN=sendgrid://KEY@default
4. Database Initialization
Bash

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction

# Load test data (Admin, Agents, Tickets)
php bin/console doctrine:fixtures:load --no-interaction
5. Running the App
Bash

symfony serve
🧪 Testing
The project uses PHPUnit for functional and unit testing.

Bash

# Prepare test database
php bin/console --env=test doctrine:database:create
php bin/console --env=test doctrine:migrations:migrate --no-interaction

# Run tests
php bin/phpunit
