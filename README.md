
# Benotes


## Installation

Prerequisite:
- git  (_https://git-scm.com_)
- composer  (_https://getcomposer.org_)
- PHP ≥ 7.1.3
- MySQL, Postgres, SQLite or SQL Server (currently only MySQL is tested)

Installation:
- git clone https://github.com/fr0tt/benotes  (_downloads files version-controlled_)
- composer install  (_installs dependencies accordingly to your php version_)
- create a database
- cp .env.example .env  (_copies configuration file_)
- generate a random string for APP_KEY e.g. openssl rand -base64 32 (_for security reasons_)
- edit .env file  (_in order to be able to connect to your database_)
- php artisan install  (_amongst other: creates database tables and fills them_)
- ln -sfn ../storage/app/public/ public/storage (_create symlink for storage_)
- chmod -R 774 storage (_make storage directory writable for webserver_)