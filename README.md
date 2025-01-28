## Installation and deployment
For installation information please refer to [DEPLOYMENT.md](DEPLOYMENT.md) document.

## Authentication process flow
With your favorite API client (personally I recommend cURL).

1. Connect to any endpoints of the API.
2. use `WWW-Authenticate` API response's headers combined with PHP tool `php src/DevTools/authenticationHelper.php` fom the app.
3. generate digest with `php src/DevTools/authenticationHelper.php` using your credentials (login, password) and at least the nonce from the API response's header.
4. example:
```bash
$ php src/DevTools/authenticationHelper.php auth:client:digest thierry thierry1234 4364cc022f1e5dadf21e53d68b8b0b78b6632f62ea49cc1c
Authorization:
Digest username="thierry", realm="zelty.fr", uri="/login", qop="auth", nonce="4364cc022f1e5dadf21e53d68b8b0b78b6632f62ea49cc1c", nc=00000001, cnonce="8dfbfd899361c6b93a58e85b086b29b978862eacd6934047", response="bb8077aa0d32e6ef4767a06e2d3eded5655a1d8eb2761762f38eeb2b558c311b"
```
5. Copy Digest entirely (in yellow) with the quotes and paste it to **Authorization** header in your next API request
6. If all goes well you should be answered back by the API with a token, something like
```javascript
{
    "token": "ZWQyZDNmYzcxNDI3YTVlODE1MDZjNjZlNGNiYTNjMjZmZGVhNzVjNzQ2ZWE="
}
```
7. OK, you are almost done, now copy that token without quotes in your **Authorization** header field for your next API requests
```
Authorization: Bearer ZWQyZDNmYzcxNDI3YTVlODE1MDZjNjZlNGNiYTNjMjZmZGVhNzVjNzQ2ZWE
```
Note:  You can add your credentials using `src/DevTools/importDataFixtures.php` (don't forget to run the script again).  <br/>
Note2: I decided to couple Token based Authentication (RFC6750) to HTTP Digest (RFC7616) for performances purposes.  <br/>
For more information about authentication, please refer [AUTHENTICATION.md](src/Authentication/AUTHENTICATION.md) document.
<br/>
<br/>
### cURL examples

```bash
curl -H "Authorization: Digest username=\"juju\", \
realm=\"zelty.fr\", \
uri=\"/login\", \
nonce=\"a5aa3b538f4ece7880c8fc994e74544fc90fc1dd8b483d95\", \
nc=00000001, \
cnonce=\"21950c85fbebd31fc353a96fbc41bdfd1aace7be5bac5613\", \
qop=auth, \
opaque=\"218de4a8abefd1a8f9294bcfd9e9c94b\", \
response=\"b286319489ec31037c8d2a65f287cd4c356a8993d0e2527c91b51791380b9170\" \
" -v localhost:8080/login 
```

```bash
curl -H "Authorization: Bearer NWYxMGE0OTRiYmFlMTczZTk4OTJjNzI1M2FmYjc4MmVkOGMwMWQ5OGEwYmM=" localhost:8080/login
```
```bash
curl -H "Authorization: Bearer NWYxMGE0OTRiYmFlMTczZTk4OTJjNzI1M2FmYjc4MmVkOGMwMWQ5OGEwYmM=" localhost:8080/articles
```

## Basic API usage
`GET`   -                  `http(s)://hots:port`  `/`
<br/>
<br/>
`POST`  -                  `http(s)://hots:port`  `/article/`
```javascript
{"title": "Votre titre ici","content": "...","state": "draft", "publishedOn": "2022/11/15", "author": "thierry"}
```
  
`GET`   -                  `http(s)://hots:port`  `/article/{id}`
  
`PATCH`  -                  `http(s)://hots:port`  `/article/{id}`
```javascript
{"state": "published", "author": "test admin"}
```
  
`DELETE`  -                  `http(s)://hots:port`  `/article/{id}`
  
`GET`  -                  `http(s)://hots:port`  `/articles?page=1&perPage=4&renderHtml=on&filters=name%20LIKE%julie%20AND%20title%20LIKE%20click%20and%20collect`
  
<br/>
<br/>
  
## Project API support
API payloads support solely JSON (Javascript Object Notation), for responses as well as requests, and otherwise it throws errors like:
```javascript
{
    "statusCode": 400,
    "error": {
        "type": "BAD_REQUEST",
        "message": "Content-Type header is missing, JSON expected"
    }
}
```

## Final word
API performances could be improved using a memory based key => value Data Store for caching utilities. For instance, [Redis](https://redis.io) =]

The original exercise subject available [here](TEST.md) (in french).
