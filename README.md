## Chat server
install:
```
composer update
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan websocket
```