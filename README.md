
# Benotes


## Installation

Prerequisite:
- git  (_https://git-scm.com_)
- composer  (_https://getcomposer.org_)
- PHP ≥ 7.1.3
- MySQL

Installation:
- git clone https://github.com/fr0tt/benotes  (_downloads files version-controlled_)
- composer install  (_installs dependencies accordingly to your php version_)
- mv .env.example .env  (_copies configuration file_)
- create database  (_if you need help: https://dev.mysql.com/doc/refman/5.7/en/creating-database.html_)
- edit .env file  (_in order to be able to connect to your database_)
- php artisan install  (_amongst other: creates database tables and fills them_)