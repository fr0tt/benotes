<p align="center">
    <img width="110" alt="Benotes Logo"
        src="https://raw.githubusercontent.com/fr0tt/benotes/master/public/logo_144x144.png"/>
</p>

<h1 align="center">Benotes</h1>

<img src="https://user-images.githubusercontent.com/33751346/115884212-ee754800-a44e-11eb-940d-b9e96eeeab36.png"
    alt="Benotes Thumbnail">

<a href="https://www.producthunt.com/posts/benotes?utm_source=badge-featured&utm_medium=badge&utm_souce=badge-benotes" target="_blank"><img src="https://api.producthunt.com/widgets/embed-image/v1/featured.svg?post_id=294704&theme=light" alt="Benotes - Open source web app for your notes and bookmarks | Product Hunt" style="width: 250px; height: 54px;" width="250" height="54" /></a>

An open source self hosted web app for your notes and bookmarks side by side.

This project is currently in **Beta**. You may encounter bugs or errors.

### Features

- URLs are automatically saved with an image, title and description
- supports both markdown and a rich text editor
- can be installed as a PWA on your mobile devices (and desktop)
- collections can be shared via a public available URL
- links can be pasted (by pressing only one button)
- can be hosted almost anywhere thanks to its use of the lightweight Lumen framework and well supported PHP language

## Installation

**Prerequisite**:
- git  (_https://git-scm.com_)
- composer  (_https://getcomposer.org_)
- PHP ≥ 7.2.5
- MySQL, Postgres, SQLite or SQL Server (currently only MySQL and PostgreSQL were tested)

**Installation**:

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
- also edit ```DB_DATABASE```, ```DB_USERNAME``` and ```DB_PASSWORD``` in ```.env``` file accordingly. If you use something else than MySQL use most likely need to adjust ```DB_CONNECTION``` and ```DB_PORT``` as well  
(_in order to be able to connect to your database_)
- ```php artisan install```  
(_amongst other: create database tables and fill them. Type yes if asked_)
- ```ln -sfn ../storage/app/public/ public/storage```  
(_create symlink for storage_)
- ```chown -R :www-data storage && chmod -R 774 storage```  
(_make storage directory writable for webserver if your webserver runs as user www-data_)
- if you wish to use it on a production server change in your ```.env``` file ```APP_ENV``` from ```local``` to ```production```
- configure your webserver or use for testing purposes ```php -S localhost:8000 -t public```

**Installation on Heroku**:

- [![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)
(_if the referrer link won't work properly on your device, try to use for deploying the original application: https://heroku.com/deploy?template=https://github.com/fr0tt/benotes_)
- run command: ```php artisan install --only-user``` (_by clicking the 'more' button and 'run console'_)

Please note that this button **only** allows you to easily install the application. For updating it see section *Upgrade on Heroku* in **Upgrade** down below.

## Upgrade

- ```git pull```  (*upgrade files*)
- ```composer install```  (*upgrade dependencies. See composer part of Installation for information about php8 above*)
- ```php artisan migrate```  (*upgrade database schemas*)

**Upgrade on Heroku**:

- Follow the steps described at https://f-a.nz/dev/update-deploy-to-heroku-app/ (*replace https://github.com/user/my-project with https://github.com/fr0tt/benotes*)

---

Feel free to [contact me](https://twitter.com/_fr0tt) if you need any help or write an issue. 

## Rest API

### Authentication

**POST /api/auth/login**

*Logs in a user*

| Attribute | Value     |
| --------- | --------- |
| email     | Required. |
| password  | Required. |

**POST /api/auth/refresh**

*Refreshes the JWT Token*

**GET /api/auth/me** 

*Returns the authenticated user*

**POST /api/auth/logout**

*Logs out the authenticated user*

---

### Posts

**GET /api/posts**

*Get multiple posts*

| Attribute     | Value                                                        |
| ------------- | ------------------------------------------------------------ |
| collection_id | Optional. Specify a collection of which you want to request posts. If collection_id is not specified you will get all posts from all collections from your user |
| is_uncategorized | Optional. Specify if you wish to get all posts without a collection. |
| limit         | Optional. Limit the amount of requested posts by number      |

**GET /api/posts/{id}**

*Get one post by its id*

**POST /api/posts**

*Create a new post*

| Attribute     | Value                                                        |
| ------------- | ------------------------------------------------------------ |
| collection_id | Optional. Specify a collection you wish to save your new post to, if not, your post will not be part of a collection. |
| title         | Optional. Specify a title, gets automatically filled if your content is a link |
| content       | Required. Specify a link, post, message, ..                  |

**PATCH /api/posts/{id}**

*Update an existing post*

| Attribute     | Value                                                        |
| ------------- | ------------------------------------------------------------ |
| collection_id | Optional. Specify a collection you want to save your post to instead. To patch a post from the uncategorized collection simply use 0 as value for the collection_id |
| title         | Optional. Change your posts title                            |
| content       | Optional. Change your posts content                          |
| order         | Optional. Specify a new order you wish to move your post to  |

**DELETE /api/posts/{id}**

*Delete a post by its id*

