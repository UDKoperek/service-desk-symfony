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
* **XAMPP (Recommended)**

### 2. Clone and Install
```bash```
```git clone [https://github.com/your-username/service-desk.git](https://github.com/your-username/service-desk.git)```
```cd "C:\your\path\service-desk-symphony-main"```
```composer install```

3. Environment Configuration ⚙️
Create a local environment file:

Bash
```cp .env .env.local```

Update your .env.local with your credentials:

Code snippet:
```# Database ```
```DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/service-desk-symphony-main?serverVersion=10.4.32-MariaDB&charset=utf8mb4"```

```# Mailer (SendGrid Example)```
```MAILER_DSN=sendgrid://KEY@default```




4. Database Initialization

Bash
```php bin/console doctrine:database:create```
```php bin/console doctrine:migrations:migrate --no-interaction```

# Load test data (Admin, Agents, Tickets)
```php bin/console doctrine:fixtures:load --no-interaction```


5. Running the App
Bash
```php -S 127.0.0.1:8000 -t public```

symfony serve
🧪 Testing
The project uses PHPUnit for functional and unit testing.

Bash
# Prepare test database
```php bin/console --env=test doctrine:database:create```
```php bin/console --env=test doctrine:migrations:migrate --no-interaction```

# Run tests
```php bin/phpunit```


PROBLEMS? Database? Try this ->
Fixing the Development Environment (DEV)

This will create one new migration file containing the entire structure of your database.

Warning! These commands will permanently delete the data in your local database. If you have any important data there, make a backup (though this is rarely necessary during development).

Bash

# 1. Drop the database completely (to clear out any "ghost" migrations)
```php bin/console doctrine:database:drop --forcet```

# 2. Create a clean database from scratch
```php bin/console doctrine:database:createt```

# 3. Create a new migration.
php bin/console make:migration

# 4. Apply this new migration
```php bin/console doctrine:migrations:migratet```

After this, a single file (e.g., Version2025...php) will appear in your migrations folder, and the migrate command will work without errors.

2. Fixing the Test Environment (TEST)

As mentioned earlier, migrations are not necessary for tests. Instead, we can use a different approach: direct schema creation. This is faster and avoids versioning errors.

Run these commands for the test environment:

Bash

# 1. Drop the old test database
```php bin/console --env=test doctrine:database:drop --forcet```

# 2. Create a new one
```php bin/console --env=test doctrine:database:createt```

# 3. IMPORTANT: Create tables DIRECTLY (bypassing migration files)
```php bin/console --env=test doctrine:schema:create
