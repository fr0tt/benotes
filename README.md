<p align="center">
        <img width="110" src="https://raw.githubusercontent.com/fr0tt/benotes/master/public/logo_144x144.png" alt="Benotes Logo"/>
</p>

<h1 align="center">Benotes</h1>

An open source self hosted notes and bookmarks taking web app.

This project is currently in **Beta**. You may encounter bugs or errors.


## Installation

Prerequisite:
- git  (_https://git-scm.com_)
- composer  (_https://getcomposer.org_)
- PHP ≥ 7.2
- MySQL, Postgres, SQLite or SQL Server (currently only MySQL is tested)

Installation:
- git clone https://github.com/fr0tt/benotes  (_downloads files version-controlled_)
- composer install  (_installs dependencies accordingly to your php version_)
- cp .env.example .env  (_copies configuration file_)
- generate a random string for APP_KEY e.g. openssl rand -base64 32 (_for security reasons_)
- create a database
- edit DB_DATABASE, DB_USERNAME and DB_PASSWORD in .env file accordingly (_in order to be able to connect to your database_)
- php artisan install  (_amongst other: creates database tables and fills them_)
- ln -sfn ../storage/app/public/ public/storage (_create symlink for storage_)
- chown -R :www-data storage && chmod -R 774 storage (_make storage directory writable for webserver if your webserver runs as user www-data_)

## Upgrade

- git pull (*upgrade files*)
- composer install (*upgrade dependencies*)
- php artisan migrate (*upgrade database schemas*)

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

