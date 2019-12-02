# socket-server

Socket Server using Laravel

## install

- install via composer

```sh
composer require nemrut/socket-server
```

- After installing, add provider on config/app.php on your project.

```php
// app.php

    'providers' => [
        ...

        Nemrut\Providers\NemrutServiceProvider::class,
    ],
```

## run server

```sh
php artisan socket-server
```

