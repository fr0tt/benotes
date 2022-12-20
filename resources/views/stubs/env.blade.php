APP_NAME=Benotes
APP_ENV=local
APP_DEBUG=false
APP_URL={{ $app_url }}

APP_KEY={{ $app_key }}
JWT_SECRET={{ $jwt_secret }}
USE_FILESYSTEM={{ $use_filesystem }}

DB_CONNECTION={{ $db_connection }}
DB_URL={{ $db_url }}
DB_HOST={{ $db_host }}
DB_PORT={{ $db_port }}
DB_DATABASE={{ $db_database }}
DB_USERNAME={{ $db_username }}
DB_PASSWORD={{ $db_password }}

CACHE_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_DRIVER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=