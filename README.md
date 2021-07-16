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
- collections can be shared via a public available URL
- links can be pasted (by pressing only one button)
- can be hosted almost anywhere thanks to its use of the lightweight Lumen framework and well supported PHP language

## Installation & Upgrade

Currently their are three options for you two choose from:
- [Normal classical way](installation.md#classic)
- [Docker](installation.md#docker)
- [Heroku](installation.md#heroku)

---

Feel free to [contact me](https://twitter.com/_fr0tt) if you need any help or open an issue. 

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

