# Installation & Upgrade

- [Classic](#classic)
- [Docker](#docker)
- [Heroku](#heroku)
- [Optional: S3 as Filesystem](#optional-s3-as-filesystem)

## Classic

### Prerequisite (Classic)

- git  (_https://git-scm.com_)
- composer  (_https://getcomposer.org_)
- PHP ≥ 7.2.5
- MySQL, Postgres, SQLite or SQL Server (currently only MySQL and PostgreSQL were tested)

### Installation (Classic)

- ```git clone https://github.com/fr0tt/benotes```
(_download files version-controlled_)
- ```composer install```
(_install dependencies accordingly to your php version.
<br> Please note that **php8-cli will fail**, use instead something similar to the likes of: ```/usr/bin/php7.4 /usr/local/bin/composer install``` or any other php-cli version between 7.2.5 and 7.4_)
- ```cp .env.example .env```
(_copy configuration file_)
- generate a random string for ```APP_KEY``` in your ```.env``` file e.g. ```openssl rand -base64 32```
(_for security reasons_)
- create a database
- also edit ```DB_DATABASE```, ```DB_USERNAME``` and ```DB_PASSWORD``` in ```.env``` file accordingly.
If you use something else than MySQL you most likely need to adjust ```DB_CONNECTION``` and ```DB_PORT``` as well.
For PostgreSQL have a look at https://github.com/fr0tt/benotes/issues/14
(_in order to be able to connect to your database_)
- ```php artisan install``` or something like ```/usr/bin/php7.4 artisan install```
(_amongst other: create database tables and fill them. Type yes if asked_)
- ```ln -sfn ../storage/app/public/ public/storage```
(_create symlink for storage_)
- ```chown -R :www-data storage && chmod -R 774 storage```
(_make storage directory writable for webserver if your webserver runs as user www-data_)
- if you wish to use it on a production server change in your ```.env``` file ```APP_ENV``` from ```local``` to ```production```
- configure your webserver or use for testing purposes ```php -S localhost:8000 -t public```

### Upgrade (Classic)

- ```git pull```
(*upgrade files*)
- ```composer install```
(*upgrade dependencies. See composer part of Installation for information about php8 above*)
- ```php artisan migrate```
(*upgrade database schemas*)

---

## Docker

### Installation (Docker)

- ```git clone https://github.com/fr0tt/benotes```
- ```cp .env.docker.example .env```
- edit ```.env``` and set a random secret for ```APP_KEY``` (generated with e.g. ```openssl rand -base64 32```)
and change ```APP_PORT``` if necessary
- ```docker-compose build```
(_build docker container_)
- ```docker-compose up -d```
(_start docker container_)
- ```docker-compose exec --user application app sh```
(_access the app service_)
- ```sh docker/install.sh```
(_do some necessary work like database migration and admin account creation_)
- if you wish to use it on a production server change in your ```.env``` file ```APP_ENV``` from ```local``` to ```production```
- (based on your setup you might want to configure a reverse proxy as well)
- for persisting data see [below](optional-s3-as-filesystem)


### Upgrade (Docker)

- ```git pull```
- ```docker-compose exec app sh```
- ```sh docker/update.sh```
(_Type yes if asked_)

---

## Heroku

### Installation (Heroku)

- [![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy?template=https://github.com/fr0tt/benotes)
- run command: ```php artisan install --only-user``` (_by clicking the 'more' button and 'run console'_)
- for persisting data see [below](optional-s3-as-filesystem)

Please note that this button **only** allows you to easily install the application. For updating it see section *Upgrade on Heroku* in **Upgrade** down below.

### Upgrade (Heroku)

- Follow the steps described at https://f-a.nz/dev/update-deploy-to-heroku-app/ (*replace https://github.com/user/my-project with https://github.com/fr0tt/benotes*)


<br>
<br>

---
---

## Optional: S3 as Filesystem

<br>

If you use an ephermal, non-persistent filesystem which is the case e.g. with Heroku or every Docker hosting that does not implement volumes you can use the S3 driver in order to store your data permanently.

What benefit would you gain from doing so ?

Currently the only difference would be that every thumbnail from every link would use the original location of every thumbnail, meaning that your browser would need to communicate with each of them - instead of accessing them all shrinked and from on single source, your local filesystem or a S3 bucket.

Every Object Storage that is S3 compliant can be used such as e.g. AWS S3, Digital Ocean Spaces or Minio.
- Create a bucket and make it public
- Add to your ```.env``` file the following with correct corresponding values:
```
FILESYSTEM_DRIVER     = s3
AWS_ACCESS_KEY_ID     = yourKeyId
AWS_SECRET_ACCESS_KEY = yourAccessKey
AWS_DEFAULT_REGION    = us-east-1
AWS_BUCKET            = yourCreativeBucketName
AWS_ENDPOINT          = endpointUrl
```