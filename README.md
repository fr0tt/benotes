<p align="center">
    <img width="110" alt="Benotes Logo"
        src="https://raw.githubusercontent.com/fr0tt/benotes/master/public/logo_144x144.png"/>
</p>

<h1 align="center">Benotes</h1>

<img src="https://user-images.githubusercontent.com/33751346/115884212-ee754800-a44e-11eb-940d-b9e96eeeab36.png"
    alt="Benotes Thumbnail">

An open source self hosted web app for your notes and bookmarks side by side.

This project is currently in **Beta**. You may encounter bugs or errors.

### Features

- URLs are automatically saved with an image, title and description
- supports both markdown and a rich text editor
- can be installed as a PWA on your mobile devices (and desktop)
- share content via this app (if installed as an PWA and supported by your browser)
- collections can be shared via a public available URL
- links can be instantly pasted as new posts
- can be hosted almost anywhere thanks to its use of the lightweight Lumen framework and well supported PHP language
- works with and without a persistent storage layer (both filesystem and S3 are supported)
- can also be hosted via Docker or on Heroku

## Installation & Upgrade

Currently their are three options for you to choose from:
- [Normal classical way](installation.md#classic)
- [Docker](installation.md#docker)
- [Heroku](installation.md#heroku)

## Issues

Feel free to [contact me](https://twitter.com/_fr0tt) if you need any help or open an [issue](https://github.com/fr0tt/benotes/issues) or a [discussion](https://github.com/fr0tt/benotes/discussions).


Q: Having trouble with reordering posts ?

Try this command in order to fix it.
```
php artisan fix-position
```
or
```
/usr/bin/php7.4 artisan fix-position
```


## Rest API

Further information can be found here: [Rest API Documentation](api.md)