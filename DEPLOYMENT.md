# Installation
1. install php and its needed extensions (**WARNING:** PHP version 7.4 is needed for this project to run.)
```bash
sudo apt-get install php7.4 php7.4-cli php7.4-common php7.4-json php7.4-opcache php7.4-mbstring php7.4-sqlite3 php7.4-xml
```
2. **install last composer version**

3. run composer install
```bash
composer install
```
4. At project root run
```bash
php src/DevTools/createDB.php
```
5. then
```bash
php src/DevTools/php src/DevTools/importDataFixtures.php
```

(note: you can run this script multiple time)
<br/>
You are now good to go, run PHP server with your favorite tool, you can now access the app.
<br/>
<br/>
<br/>

# Development Environment
For a fast testing in development environment just use PHP built-in server with:

```bash
php -S localhost:8080 -t public
```
in project root folder.

Launch Unit Tests suite with `pu` or `puv` (verbose) commands :

```bash
composer pu[v]
```
<br/>
<br/>
<br/>
<br/>
  
# Production Environment

- In `public/index.php` file, modify the corresponding lines and replace with: 

```php
error_reporting(0);
```

```php
ini_set('display_errors', '0');
```

```php
if (true) {
    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}
```
or, simply
```php
$containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
```

- In `src/middlewares.php` file, modify the corresponding lines to this: 
```php
$displayErrorDetails = false;
```

- change `src/services.php` file accordingly (i.e DB configuration).

<br/>
<br/>
<br/>
<br/>
  
# Zelty PHP App Deployment
requirements: **php7.4 php7.4-cli php7.4-common php7.4-json php7.4-opcache php7.4-mbstring php7.4-sqlite3 php7.4-xml**

composer installation is required (global recommended)
then 
```bash
composer.phar install --no-dev --optimize-autoloader
```

## With Nginx Deployment (Linux/debian)
```bash
useradd -s /usr/sbin/nologin -r -M -g 33 -d /etc/nginx nginx
```

modified user in `/etc/nginx/nginx.conf`
modified server configuration file `/etc/nginx/sites-enabled/default`


## PHP-fpm
Don't forget to set file system rights
```bash
useradd -s /usr/sbin/nologin -r -M -g 33 www-data
```
(**Note:** `-g 33` to add user to www-data group on debian)

```bash
scp -P 2222 ../livrable-v1.tar.gz user@remote.server.com:/opt/
```

```bash
tar -xzvf /opt/deploy-liverable-v0.1.tar.gz -C /var/www/
```

```bash
chown -R www-data:www-data /var/www/
chmod -R 640 /var/www/
find /var/www/public -type d -print0 | xargs -0 chmod 755 
find /var/www/public -type f -print0 | xargs -0 chmod 644
find /var/www/src -type d -print0 | xargs -0 chmod 755 
find /var/www/src -type f -print0 | xargs -0 chmod 644
find /var/www/vendor -type d -print0 | xargs -0 chmod 755 
find /var/www/vendor -type f -print0 | xargs -0 chmod 644
```
