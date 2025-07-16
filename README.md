# 📦 Laravel Invoicing System

![WIP](https://img.shields.io/badge/status-WIP-orange?style=for-the-badge&logo=laravel&logoColor=white)
[![CI Status](https://github.com/programadoramauri/LaravelInvoices/actions/workflows/cy.yml/badge.svg)](https://github.com/programadoramauri/LaravelInvoices/actions/workflows/cy.yml)
[![Coverage Status](https://codecov.io/gh/programadoramauri/LaravelInvoices/branch/main/graph/badge.svg)](https://codecov.io/gh/programadoramauri/LaravelInvoices)
![PHPStan](https://img.shields.io/badge/phpstan-passing-brightgreen)
![Pest](https://img.shields.io/badge/pest-tests--passing-brightgreen)

A Laravel-based invoicing system currently under development. This project is focused on exploring modern backend structure, containerized development and progressive feature building.

> 🛠️ Project status: **Phase 1 – Initial Setup and Environment**

---

## 🚀 Getting Started

### Requirements

- Docker & Docker Compose
- Composer (inside container only)

---

### 🐳 Run with Docker

```bash
git clone https://github.com/programadoramauri/LaravelInvoices.git
cd invoicing-system

# Start containers
docker-compose up -d --build

# Install dependencies
docker exec app composer install

# Copy .env and generate key
docker exec app cp .env.example .env
docker exec app php artisan key:generate

# Run migrations
docker exec app php artisan migrate

Access the app at: http://localhost:8000
```

### 🧪 Testing

```bash
docker exec app php artisan test
```

### 🗂️ Folder Structure

```
invoicing-system/
├── app/              # Laravel application core
├── docker/           # Docker-related files
├── database/         # Migrations & seeders
├── public/           # Public web root
├── resources/        # Views, assets, etc.
├── routes/           # API & Web routes
├── .env.example
├── docker-compose.yml
└── README.md
```

### 📌 Notes

- Database: MySQL running in Docker (localhost:3306)
- Mail not configured yet (use log driver for now)

### 👤 Author

[Amauri Franco](https://github.com/programadoramauri)
