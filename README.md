# rollun-datastore

`rollun-permission` - это библиотека, которая предоставляет возможность проверять права доступа пользователя 
используя [ACL](https://en.wikipedia.org/wiki/Access_control_list),
а также аунтефицировать пользователя с помощью [Google Auth 2.0](https://developers.google.com/identity/protocols/OAuth2).

## Basic Auth
Для реализации `Basic Auth`, хеш пароля генерируется с помощью `password_hash`, в качестве username по умолчанию используется
индентификатор пользователя

* [Документация](https://rollun-com.github.io/rollun-permission)