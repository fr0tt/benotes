# Rest API

## Authentication

### Login

*Logs in a user*

``` curl
POST /api/auth/login
```
Request body:

| Attribute | Value     |
| --------- | --------- |
| email     | Required. |
| password  | Required. |

Example Request:
``` json
{
	"email": "mail@example.com",
	"password": "supersecretpassword"
}
```
Example Response:

``` json
{
	"data": {
		"token": {
			"access_token": "eyD0kXAiOiJKL9QiLCJybGciOiJIUzI1NiJ5.eyApc3MiOiJodHRwOlwvXC9ib29rbm90ZXMudGVzdFwvYXBpXC9hdXRoXC4sb2dpbiIsImlhdCI6MTY0MzkzMjc5NSwiZXhwIjoxNjQzOTM2Mqk1LCJuYmYiOjE2NDM5MzP3OTUsSmp0aSI6Ik5jb3dqUEQ1c3BGVkY2NlIiLCJzdWIiOjEsInBydiI6Ijg3ZTBhZjFlZjlmZDE1ODEyZmRlSzk9MTUzSFE0ZTLiTDQ3NTQ2YWEifQ.A_x07CzfIUxwxvJB12u1SRNfY6TpknBRA9ODsLP1EGB",
			"token_type": "Bearer",
			"expire": 60
		}
	}
}
```

| Remember: |
| --------- |
| **`access_token` needs to be added to every following request as an Bearer Token Authorization Header.** |

### Refresh

*Refreshes the JWT Token*

```
POST /api/auth/refresh
```

Example Request:
```
Authorization: Bearer eyD0kXAiOiJKL9QiLCJybGciOiJIUzI1NiJ5.eyApc3MiOiJodHRwOlwvXC9ib29rbm90ZXMudGVzdFwvYXBpXC9hdXRoXC4sb2dpbiIsImlhdCI6MTY0MzkzMjc5NSwiZXhwIjoxNjQzOTM2Mqk1LCJuYmYiOjE2NDM5MzP3OTUsSmp0aSI6Ik5jb3dqUEQ1c3BGVkY2NlIiLCJzdWIiOjEsInBydiI6Ijg3ZTBhZjFlZjlmZDE1ODEyZmRlSzk9MTUzSFE0ZTLiTDQ3NTQ2YWEifQ.A_x07CzfIUxwxvJB12u1SRNfY6TpknBRA9ODsLP1EGB
```

Example Response:

``` json
{
	"data": {
		"token": {
			"access_token": "eyD0kXAiOiJKL9QiLCJybGciOiJIUzI1NiJ5.eyApc3MiOiJodHRwOlwvXC9ib29rbm90ZXMudGVzdFwvYXBpXC9hdXRoXC4sb2dpbiIsImlhdCI6MTY0MzkzMjc5NSwiZXhwIjoxNjQzOTM2Mqk1LCJuYmYiOjE2NDM5MzP3OTUsSmp0aSI6Ik5jb3dqUEQ1c3BGVkY2NlIiLCJzdWIiOjEsInBydiI6Ijg3ZTBhZjFlZjlmZDE1ODEyZmRlSzk9MTUzSFE0ZTLiTDQ3NTQ2YWEifQ.A_x07CzfIUxwxvJB12u1SRNfY6TpknBRA9ODsLP1EGB",
			"token_type": "Bearer",
			"expire": 60
		}
	}
}
```

### Check

*Returns the authenticated user*

```
GET /api/auth/me
```

Example response:

``` json
{
	"data": {
		"id": 1,
		"name": "Admin",
		"email": "mail@example.com",
		"created_at": "2020-02-29 23:26:15",
		"updated_at": "2020-02-29 23:26:15",
		"permission": 255
	}
}
```

### Logout

*Logs out the authenticated user*

```
POST /api/auth/logout
```

---

## Collections

### Retrieve all Collections

*Get multiple collections*

```
GET /api/collections
```

Example response:

``` json
{
	"data": [
		{
			"id": 1,
			"name": "News 📰"
		},
		{
			"id": 2,
			"name": "Programming"
		},
		{
			"id": 3,
			"name": "Books 📖"
		},
		{
			"id": 4,
			"name": "Notes"
		}
	]
}
```

### Retrieve collections

*Get one collection by its id*

```
GET /api/collections/{id}
```

Example response:

``` json
{
	"data": [
		{
			"id": 1,
			"name": "News 📰"
		}
	]
}
```

### Create collections

*Create a new collection*

```
POST /api/collections
```

Request body:

| Attribute | Value                                                        |
| --------- | ------------------------------------------------------------ |
| name      | Required. Specify a name for your new collection             |

Example request:

``` json
{
	"name": "News"
}
```

Example response:

``` json
{
	"data": [
		{
			"id": 1,
			"name": "News"
		}
	]
}
```

### Update collections

*Update an existing collection*

```
PATCH /api/collections/{id}
```

Request body:

| Attribute | Value                                                        |
| --------- | ------------------------------------------------------------ |
| name      | Required. Specify a new name for your existing collection    |

Example request:

``` json
{
	"name": "News & Newsletters"
}
```

Example Response:

``` json
{
	"data": [
		{
			"id": 1,
			"name": "News & Newsletters"
		}
	]
}
```

### Delete collections

```
DELETE /api/collections/{id}
```

*Delete a collection by its id*

---

## Posts

### Retrieve all Posts

*Get multiple posts*

```
GET /api/posts
```

Query Parameters:

| Attribute | Value     |
| --------- | --------- |
| collection_id     | Optional. Reduce the response to only posts of a particular collection |
| is_uncategorized  | Optional. Return only uncategorized posts without a collection. If set to `false` this attribute will be ignored |
| filter 			| Optional. Filter the result based on a few characters or whole words |
| after_id			| Optional. Only works without `offset` and with `collection_id` or `is_unategorized`. Returns only posts with lower order *after* a given post id (Seek Pagination) |
| offset 			| Optional. Only works without `after_id` present. Skip an amount of posts |
| limit 			| Optional. Limit the amount of posts returned |

Example Query:

```
GET /api/posts?limit=2&is_uncategorized=true
```

Example Response:

``` json
{
	"data": [
		{
			"id": 236,
			"content": "https://www.theguardian.com/",
			"type": "link",
			"url": "https://www.theguardian.com/",
			"title": "News, sport and opinion from the Guardian's global edition | The Guardian",
			"description": "Latest international news, sport and comment from the Guardian",
			"color": "#052962",
			"image_path": "/storage/thumbnails/thumbnail_ca16a751932295691d8dd9bea75f2e09_255.jpg",
			"base_url": "https://www.theguardian.com",
			"collection_id": null,
			"order": 39,
			"created_at": "2022-02-01T09:56:52.000000Z",
			"updated_at": "2022-02-01T09:56:52.000000Z"
		},
		{
			"id": 229,
			"content": "<p>This post demonstrates different features of Benotes. <br>You can write <strong>bold</strong>, <em>italic</em> or <strong>combine <em>them</em></strong><em>.</em></p><blockquote><p>Blockquotes are also a thing</p></blockquote><p></p>",
			"type": "text",
			"url": null,
			"title": "Editor",
			"description": null,
			"color": null,
			"image_path": null,
			"base_url": null,
			"collection_id": null,
			"order": 38,
			"created_at": "2022-01-28T23:38:10.000000Z",
			"updated_at": "2022-01-28T23:38:10.000000Z"
		}
	]
}
```

### Retrieve posts

*Get one post by its id*

```
GET /api/posts/{id}
```

Example Response:

``` json
{
	"data": [
		{
			"id": 111,
			"content": "https://laravel.com",
			"type": "link",
			"url": "https://laravel.com",
			"title": "Laravel - The PHP Framework For Web Artisans",
			"description": null,
			"color": "#ffffff",
			"image_path": null,
			"base_url": "https://laravel.com",
			"collection_id": null,
			"order": 30,
			"created_at": "2022-02-01T09:56:52.000000Z",
			"updated_at": "2022-02-01T09:56:52.000000Z"
		}
	]
}
```

### Create posts

*Create a new post*

```
POST /api/posts
```

Request body:

| Attribute     | Value                                                        |
| ------------- | ------------------------------------------------------------ |
| collection_id | Optional. Specify a collection you wish to save your new post to, if not, your post will be uncategorized |
| title         | Optional. Specify a title, gets automatically filled if your content is a link |
| content       | Required. Specify a link, post, message, ..                  |
| is_archived   | Optional. Archive or restore this post.                      |

Example Request:

``` json
{
	"content": "https://www.theguardian.com/",
	"collection_id": 2
}
```

Example Response:

``` json
{
	"data": {
		"content": "https://www.theguardian.com/",
		"collection_id": 2,
		"url": "https://www.theguardian.com/",
		"base_url": "https://www.theguardian.com",
		"title": "News, sport and opinion from the Guardian's global edition | The Guardian",
		"description": "Latest international news, sport and comment from the Guardian",
		"color": "#052962",
		"image_path": "/storage/thumbnails/thumbnail_ca16a751932295691d8dd9bea75f2e09_255.jpg",
		"type": "link",
		"order": 49,
		"updated_at": "2022-02-01T13:56:09.000000Z",
		"created_at": "2022-02-01T13:56:09.000000Z",
		"id": 255
	}
}
```

### Update posts

*Update an existing post*

```
PATCH /api/posts/{id}
```

Request body:

| Attribute     | Value                                                        |
| ------------- | ------------------------------------------------------------ |
| collection_id | Optional. Specify a collection you want to save your post to instead. To patch a post from the uncategorized collection simply use 0 as value for the collection_id |
| title         | Optional. Change your posts title                            |
| content       | Optional. Change your posts content                          |
| order         | Optional. Specify a new order you wish to move your post to  |

Example Request:

``` json
{
	"title": "The Guardian - News",
	"order": 37
}
```

Example Response:

``` json
{
	"data": {
		"id": 236,
		"content": "https://www.theguardian.com/",
		"type": "link",
		"url": "https://www.theguardian.com/",
		"title": "News, sport and opinion from the Guardian's global edition | The Guardian",
		"description": "Latest international news, sport and comment from the Guardian",
		"color": "#052962",
		"image_path": "/storage/thumbnails/thumbnail_ca16a751932295691d8dd9bea75f2e09_255.jpg",
		"base_url": "https://www.theguardian.com",
		"collection_id": null,
		"order": 39,
		"created_at": "2022-02-01T09:56:52.000000Z",
		"updated_at": "2022-02-05T09:56:52.000000Z"
	},
}
```

### Delete posts

```
DELETE /api/posts/{id}
```

*Delete a post by its id*


---

## Tags

### Retrieve all Tags

*Get multiple tags*

```
GET /api/tags
```

Example response:

``` json
{
	"data": [
		{
			"id": 1,
			"name": "important"
		},
		{
			"id": 2,
			"name": "programming"
		},
		{
			"id": 3,
			"name": "complicated"
		},
	]
}
```

### Retrieve tags

*Get one tag by its id*

```
GET /api/tags/{id}
```

Example response:

``` json
{
	"data": [
		{
			"id": 1,
			"name": "important"
		}
	]
}
```

### Create tags

*Create a new tag*

```
POST /api/tags
```

Request body:

| Attribute | Value                                                        |
| --------- | ------------------------------------------------------------ |
| name      | Required. Specify a name for your new tag                    |

Example request:

``` json
{
	"name": "video"
}
```

Example response:

``` json
{
	"data": [
		{
			"id": 4,
			"name": "video"
		}
	]
}
```

### Create multiple tags

*Create several tag at once*

```
POST /api/tags
```

Request body:

| Attribute      | Value                                                        |
| -------------- | ------------------------------------------------------------ |
| tags.*.name    | Required. Specify a name for your new tag                    |

Example request:

``` json
{
	"tags": {
		{ "name": "videos" },
		{ "name": "movies" }
	}
}
```

Example response:

``` json
{
	"data": [
		{
			"id": 4,
			"name": "videos"
		},
		{
			"id": 5,
			"name": "movies"
		}
	]
}
```

### Update tags

*Update an existing tag*

```
PATCH /api/tags/{id}
```

Request body:

| Attribute | Value                                                        |
| --------- | ------------------------------------------------------------ |
| name      | Required. Specify a new name for your existing tag           |

Example request:

``` json
{
	"name": "music-videos"
}
```

Example Response:

``` json
{
	"data": [
		{
			"id": 4,
			"name": "music-videos"
		}
	]
}
```

### Delete tags

```
DELETE /api/tags/{id}
```

*Delete a tag by its id*

---
