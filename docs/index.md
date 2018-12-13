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

Чтобы начать пользоваться библиотекой, нужно подключить `rollun\permission\ConfigProvider` для
[ServiceManager](https://github.com/zendframework/zend-servicemanager).

##### Permissions

`PermissionMiddleware` - это объект который представляет собой основной `middleware pipe` для аутентификации и 
авторизации, поэтому нужно вызвать его перед вызовом `endpoint`, к которому нужно получить доступ. Есть как минимум два 
варианта как это сделать.

Первый вариант заключается в том чтобы поместить `PermissionMiddleware` в `middleware pipe` вашего приложения (если 
используется [zendframework/zend-expressive](https://github.com/zendframework/zend-expressive), то это можно сделать
в файле `config/pipeline.php`):

```php
$app->pipe(PermissionMiddleware::class);
```

В этом случае вызов `PermissionMiddleware` будет для абсолютно всех `endpoint`.

Второй вариант это подключить `PermissionMiddleware` вместе з `endpoint` в настройках роутинга (если 
используется [zendframework/zend-expressive](https://github.com/zendframework/zend-expressive), то это можно сделать
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


Также нужно настроить базу данных. Подразумевается что конфигурация для БД уже настроена. Здесь также есть два 
варианта.

* Использовать инсталлер `rollun\Permission\AssetInstaller`:

```bash
composer lib install
```

* Использовать `sql` [файл](/src/Permission/src/acl.sql).

##### OAuth

Как работет `Google OAuth 2.0` в `rollun-permission`. Для того чтобы получить `authorization code` от Google (который 
потом будет обменен на `access token`) нужно перенаправить пользователя на страницу google авторизации. Таким
редиректом занимается `RedirectMiddleware`. 

```php
$app->get(
    '/your/oauth/redirect/path',
    RedirectMiddleware::class,
    'oauth-redirect-route-name'
);
```

`RedirectMiddleware` должен указать куда google отправить пользователя 
после его успешной авторизации. Поэтому при вызове роута с редиректом нужно указать `action` параметр.
Есть два варианта этого параметра: `login`, `register` - для обратного редиракта на логин и регистрацию соответственно.

```bash
curl example.com/your/oauth/redirect/path?action=login
```

Для обратного редиректа на логин и регистрации нужны соответствующие сконфигурированные роутинги:
Названия роутов для логина и регистрации должны бить обязательно `ConfigProvider::OAUTH_LOGIN_ROUTE_NAME` и
`ConfigProvider::OAUTH_REGISTER_ROUTE_NAME` соответственно как показано в примере ниже.

```php
$app->get(
    '/your/oauth/login/path',
    LoginMiddleware::class,
    ConfigProvider::OAUTH_LOGIN_ROUTE_NAME
);

$app->get(
    '/your/oauth/register/path',
    RegisterMiddleware::class,
    ConfigProvider::OAUTH_REGISTER_ROUTE_NAME
);
```

Пример роутинга для логаута:

```php
$app->get(
    '/your/oauth/logout/path',
    LoginMiddleware::class,
     'logout-route-name'
);
```

В [config/routes.php](/config/routes.php) можно увидеть реальный пример конфигурации роутингов.

Обязательные переменные окружения для корректной работы `oauth`:
* GOOGLE_CLIENT_SECRET - `client_secret` в личном кабинете google
* GOOGLE_CLIENT_ID - `client_id` в личном кабинете google
* GOOGLE_PROJECT_ID - `project_id` в личном кабинете google
* HOST - домен сайт где происходит авторизация
* EMAIL_FROM - от кого отправить email для подтверждения регистрации
* EMAIL_TO - кому отправить email для подтверждения регистрации
