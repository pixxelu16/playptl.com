# playptl

Role-based platform for admins, organisers, and players.

## Setup

```bash
composer install
php artisan migrate
php artisan db:seed
php artisan serve
```

## Test Users

- Admin: `admin@example.com` / `password`
- Organiser: `organiser@example.com` / `password`
- Player: `player@example.com` / `password`

## Dashboards

- Admin: `/admin/dashboard`
- Organiser: `/organiser/dashboard`
- Player: `/player/dashboard`
