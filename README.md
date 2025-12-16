# 🎫 Service Desk Application

A professional ticket management system built with **Symfony 7** and **PHP 8.2+**. This application provides a complete solution for issue tracking, featuring automated communication and a robust security model.

---

## 🚀 Key Features

### 🛠 Ticket Management & UX
* **Dynamic Dashboard**: Full CRUD for tickets with status and priority tracking.
* **Filtering & Search**: Advanced search (Title/Content) and filtering by Status/Priority using **DTOs** and `#[MapQueryString]`.
* **Pagination**: High-performance data browsing powered by `KnpPaginatorBundle`.
* **Anonymous Access**: Unauthenticated users can report issues via secure `Session Token` tracking.

### 🔐 Security & Access Control
* **Role Hierarchy**: 
    * `ROLE_ADMIN` (Full access + Category management) 
    * `ROLE_AGENT` (Manage all tickets)
    * `ROLE_USER` (View/Manage personal tickets only).
* **Granular Permissions**: Custom **Voters** manage access to specific actions (e.g., preventing comments on closed tickets).

### 📧 Communication & Integrity
* **Automated Mailing**: Integrated **Symfony Mailer** for account verification and system notifications.
* **Data Integrity**: Enum-backed entities (`TicketStatus`, `TicketPriority`) and cascading deletes for comments.

---

## 💻 Tech Stack

| Component | Technology |
| :--- | :--- |
| **Backend** | Symfony 7.x (PHP 8.2+) |
| **Database** | MySQL / MariaDB |
| **ORM** | Doctrine |
| **Mailing** | Symfony Mailer |
| **Frontend** | Twig + Bootstrap |
| **Testing** | PHPUnit |

---

## 🔧 Installation & Setup

### 1. Prerequisites
* **PHP 8.2** or higher
* **Composer**
* **MySQL / MariaDB**
* **Symfony CLI** (optional but recommended)

### 2. Clone and Install
```bash
# Clone the repository
git clone [https://github.com/your-username/service-desk.git](https://github.com/your-username/service-desk.git)
cd service-desk

# Install PHP dependencies
composer install
