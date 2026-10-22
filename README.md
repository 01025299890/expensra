# Expensra

**An AI-powered personal finance and smart budgeting backend, built with Laravel.**

Most expense apps make you fill in a form for every purchase. With Expensra you just say *"I paid 150 EGP for lunch"* or snap a photo of the receipt, and the backend parses it, categorizes it, and records it for you.

This repository contains the RESTful API that powers Expensra: authentication, AI expense logging, budgeting, analytics, scheduled automation, and an admin dashboard.

---

## Features

### AI expense logging
- Log expenses by **voice** or by **receipt photo**.
- An AI engine extracts the amount, category, and date, then the expense is saved automatically.
- Parsed results are validated before being stored.

### Authentication and access control
- Token-based authentication with **Laravel Sanctum**.
- **OTP verification** flow for account security.
- Custom **middleware** for:
  - Role-based access control (RBAC)
  - Free vs Premium feature gating
  - Real-time budget validation before a transaction is executed

### Budgeting and analytics
- 6-month income vs. expense trends
- Category breakdowns
- Monthly surplus reallocation
- Smart savings goal deposits

### Scheduled automation
All jobs run on the Laravel Scheduler and are timezone-aware (`Africa/Cairo`).

| Task | Schedule |
|------|----------|
| Monthly financial reset | 1st of every month |
| Savings goal reminders | Weekly |
| Database backup and cleanup (Spatie Backup) | Daily |

### Admin dashboard
- Audit user activity
- Manage subscription upgrades
- Track system growth metrics

---

## Tech stack

- PHP / Laravel
- Laravel Sanctum
- MySQL
- Laravel Scheduler
- Spatie Laravel Backup
- AI integration for voice and image parsing

---

## Getting started

### Requirements
- PHP 8.1 or higher
- Composer
- MySQL

### Installation

```bash
git clone https://github.com/01025299890/expensra.git
cd expensra
composer install
cp .env.example .env
php artisan key:generate
```

Configure your database and AI credentials in `.env`, then run:

```bash
php artisan migrate
php artisan serve
```

### Running the scheduler

The scheduled tasks need the Laravel scheduler running. In production, add this cron entry:

```bash
* * * * * cd /path/to/expensra && php artisan schedule:run >> /dev/null 2>&1
```

During local development you can use:

```bash
php artisan schedule:work
```

---

## API overview

All endpoints are prefixed with `/api` and, except for registration and login, require a Sanctum bearer token:

```
Authorization: Bearer <token>
```

| Area | Description |
|------|-------------|
| Auth | Register, OTP verification, login, logout |
| Expenses | Create expenses manually, by voice, or from a receipt photo |
| Budgets | Set and track budgets per category |
| Goals | Create savings goals and make deposits |
| Analytics | Income vs. expense trends and category breakdowns |
| Admin | User auditing, subscription management, growth metrics |

---

## Screenshots

Add screenshots or a short demo GIF of the app here.

---

## Contributing

Feedback and contributions are welcome. Feel free to open an issue or submit a pull request.

## License

Add your license here (for example, MIT).
