# rollun-datastore

`rollun-permission` - это библиотека, которая предоставляет возможность проверять права доступа пользователя 
используя [ACL](https://en.wikipedia.org/wiki/Access_control_list),
а также аунтефицировать пользователя с помощью [Google Auth 2.0](https://developers.google.com/identity/protocols/OAuth2).

### Установка

Установить с помощью [composer](https://getcomposer.org/).
```bash
composer require rollun-com/rollun-permission
```

Чтобы запустить тесты нужно установить обязательные переменные указанные в `.env`, установить файлы конфигурации 
(`rollun\permission\AssetInstaller`) для тестового окружения и подключить `rollun\permission\ConfigProvider` в 
конфигурационный файл.

```bash
composer lib install
```

### Getting Started

Библиотеку `rollun-permission` можно условно поделить на две независимые части: `permissions` и `oauth`.
`rollun-permission` использует концепцию посредников (`middleware`) и последовательный вызов посредников 
(`middleware pipe`) основанную на [PSR-7](https://www.php-fig.org/psr/psr-7/)
и [PSR-15](https://www.php-fig.org/psr/psr-15/). Фактически `permissions` это посредник
(или если быть более точным - последовательный вызов посредников), который нужно поместить перед визовом конечных 
обработчиков запросов (`endpoint`), а вот `oauth` это несколько `endpoint`.

Зачастую `endpoint` и есть [MiddlewareInterface](https://www.php-fig.org/psr/psr-15/#22-psrhttpservermiddlewareinterface),
просто вместо того чтобы вызвать следующий обработчик они возвращают ответ.
Терминология "`endpoint`" была выбрана для того чтобы подчеркнуть что эта сущность именно возвращает ответ.
Хотя в роли `endpoint` может служить и
[RequestHandlerInterface](https://www.php-fig.org/psr/psr-15/#21-psrhttpserverrequesthandlerinterface).

##### Permissions

`Permissions` представляет возможность аутентифицировать, а затем авторизировать пользователя. Для авторизации 
используются посредник (`middleware`) `AuthenticationMiddleware` от
[zendframework/zend-expressive-authentication](https://github.com/zendframework/zend-expressive-authentication),
а для авторизации используется последовательный вызов посредников (`middleware pipe`): `RoleResolver`,
`ResourceResolver`, `PrivilegeResolver`, `AclMiddleware`.

##### OAuth

`OAuth` предоставляет возможность логинить и регистрировать пользователя через
[Google Auth 2.0](https://developers.google.com/identity/protocols/OAuth2). Для этого используются
`LoginMiddleware`, `LogoutMiddleware`, `RedirectMiddleware` и `RegisterMiddleware`.


### Использование

Чтобы начать пользовать библиотекой, нужно подключить `rollun\permission\ConfigProvider` для
[ServiceManager](https://github.com/zendframework/zend-servicemanager).

##### Permissions

`PermissionMiddleware` - это объект который представляет собой основной `middleware pipe` для аутентификации и 
авторизации, поєтому нужно вызвать эго перед вызовом `endpoint`, к которому нужно получить доступ. Есть как минимум два 
варианта как это сделать.

Первый вариант заключаеться в том чтобы поместить `PermissionMiddleware` в `middleware pipe` вашего приложения (если 
используеться [zendframework/zend-expressive](https://github.com/zendframework/zend-expressive), то это можна сделать
в файле `config/pipeline.php`):

```php
$app->pipe(PermissionMiddleware::class);
```

В этом случае вызов `PermissionMiddleware` будет для абсолютно всех `endpoint`.

Второй вариант это подключить `PermissionMiddleware` вместе з `endpoint` в настройках роутинга (если 
используеться [zendframework/zend-expressive](https://github.com/zendframework/zend-expressive), то это можна сделать
в файле `config/routes.php`):.

```php
$app->get(
    '/',
    [
        PermissionMiddleware::class,
        function (ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
            return new HtmlResponse('Home page!');
        },
    ]
    'home-page'
);
```

Тогда аутентификация и авторизация будет вызвана только для этого роутинга.


Также нужно настроить базу данных. Подразумеваеться что конфигурация для БД уже настроена. Здесь также есть два 
варианта.

* Использовать инсталлер `rollun\Permission\AssetInstaller`:

```bash
composer lib install
```

* Использовать `sql` [файл](/src/Permission/src/acl.sql).

##### OAuth

Как работет `Google OAuth 2.0` в `rollun-permission`. Для того чтобы получить `authorization code` от Google (который 
потом будет обемен на `access token`)

Для того чтобы спользовать возможность `Google OAuth 2.0` авторизации нужно сконфигуриров
