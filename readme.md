## Installation

Install PHP dependencies:

```sh
composer install
```

Install NPM dependencies:

```sh
npm ci
```

Build assets:

```sh
npm run dev
```

Setup configuration:

```sh
cp .env.example .env
```

Generate application key:

```sh
php artisan key:generate
```

Create the symbolic links configured for the application:

```sh
php artisan storage:link
```

Create an SQLite database. You can also use another database (MySQL, Postgres), simply update your configuration accordingly.

```sh
touch database/database.sqlite
```

Run database migrations:

```sh
php artisan migrate
```

Run database seeder:

```sh
php artisan db:seed
```

Run the dev server (the output will give the address):

```sh
php artisan serve
```

You're ready to go! Visit Brick CRM in your browser, and login with:

- **Username:** johndoe@example.com
- **Password:** secret

## Running tests

To run the Brick CRM tests, run:

```
phpunit
```


## Additional Info

PHP debug:
```
1)Open 20-xdebug.ini with nano text editor:
    sudo nano /etc/php/8.0/fpm/conf.d/20-xdebug.ini
2)Input these text lines:
    zend_extension=xdebug.so
    xdebug.mode = debug
    xdebug.discover_client_host = yes
    xdebug.start_with_request = yes
    xdebug.client_host = 10.0.2.2
    xdebug.max_nesting_level = 512
    xdebug.log_level=0
3)Make link:
    sudo ln -sf /etc/php/8.0/mods-available/xdebug.ini   /etc/php/8.0/cli/conf.d/20-xdebug.ini
4)Restart php service:
    sudo service php8.0-fpm restart
```

Forge ssh access:
```
 1) Generate ssh key in the .ssh folder: 
    ssh-keygen -t rsa
 2) Copy public key to the forge ssh keys
 3) Connect to the server: 
    ssh forge@ip-address -i keyname
```
