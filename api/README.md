### Install dependencies

Run the commands:

```sh
composer install
yarn install
```

### Configuration file

```sh
php artisan keys:generate
```

### Create the symbolic link to public disk

```sh
php artisan storage:link
```

### Create tables and fake data

```sh
php artisan migrate --seed
```