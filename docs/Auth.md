# Authentication

## Введение

Authentication - модуль который позволяет идентифицироавть, а так же авторизоать пользователя.

Сам модуль Authentication можно разбить на несколько частей
* Identification - идентифицирует пользователя.
* Authentication - аутентифицирует пользователя.

Давайте разберем эти части.
> Если вы не собираетесь использовать middleware, то можете пропустить эту часть и перейти к разделу [Adapter].

## QuickStart 
> Если вы нехотите вдоваться в подробности работы Authentication, и хотите получить рабочий минимум, то достаточно прочесть данный параграф.

Что бы получить минимальо рабочий вариант вам достаточно указать настройки в конфигурационном файле.

Добавить `'authPipe'` и `'identityPipe'` в конфиг `middleware_pipeline`.
> Приоритет у `'identityPipe'` должен быть выше.

Так же добавте роут 
```php
'routes' => [
        [
            'name' => 'login',
            'path' => '/login',
            'middleware' => 'authPipe',
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'logout',
            'path' => '/logout',
            'middleware' => \rollun\permission\Auth\Middleware\LogoutAction::class,
            'allowed_methods' => ['GET', 'POST'],
        ],
]
```

Используйте файл `acl.dataStore.php` с настройкой DataStore по умолчанию.
>Не забудте изменить название файла, а так же добавить в него свои правила в случае надобности.

Так же добавте конфиг.

```php
    MiddlewarePipeAbstractFactory::KEY_AMP => [
        'identityPipe' => [
            'middlewares' => [
                \rollun\permission\Auth\Middleware\IdentifyAction::class,
                \rollun\permission\Auth\Middleware\UserResolver::class,
            ]
        ],
        'authPipe' => [
            'middlewares' => [
               'openId'
               'authReturnSwitch'
            ]
        ],
    ],
    LazyLoadSwitchAbstractFactory::LAZY_LOAD_SWITCH => [
            'authReturnSwitch' => [
                LazyLoadSwitchAbstractFactory::KEY_COMPARATOR_SERVICE => 'returnResultAttributeRequestComparator',
                LazyLoadSwitchAbstractFactory::KEY_MIDDLEWARES_SERVICE => [
                    '/^true$/' => \rollun\actionrender\ReturnMiddleware::class,
                    '/^false$/' => \rollun\permission\Auth\Middleware\UserResolver::class,
                ]
            ]
    ],
    AuthenticationAbstractFactory::KEY_AUTHENTICATION => [
        'openId' => [
            AuthenticationAbstractFactory::KEY_ADAPTER => 'openIdAdapter'
        ],
    ],
```
И добавте конфиг для сессий.
```php
 'dependencies' => [
        'factories' => [
            \Zend\Session\SessionManager::class =>
                \Zend\Session\Service\SessionManagerFactory::class,
        ],
        'abstract_factories' => [
            \Zend\Session\Service\ContainerAbstractServiceFactory::class,
        ],
    ],
    'session_containers' => [
        'WebSessionContainer'
    ],
```

## Identification

Идентификация пользователя происходит с помощью `IdentifyAction:class` Middleware.
Данный middleware используя `AuthenticationService` проверяет наличие пользователя, если такой существует
он кладет его id в атребут зарпоса, иначе он использует `IdentifyAction::DEFAULT_IDENTITY` в качестве id по умолчанию.

> Если вы не хотите использовать middleware тогдва вам достаточно использовать AuthenticationService. 
Но в этом случае вам прийдеться реализовывать логику работы с пользователями в запросе.

## Authentication middleware
Давйте рассмотрим как работает Authentication.
Для начала мы глянем middleware который производит авторизацию называеться `AuthenticationAction::class`.
Данный middleware устроен таким образом что весь процес авторизации скрыт под `Adapter`, 
их мы расмотрим чуть позже. 
Давайте пока взглянем на то как работает сам middleware.
Итак `AuthenticationAction` для своего использования требует `Adapter` и `AuthenticationService`.
Для данного middleware существует абстрактная фабрика - `AuthenticationAbstractFactory::class`,
 которая моможет нам инициализировать обьект.
Давайте взглянем на ее конфиг:

```php
    AuthenticationAbstractFactory::KEY_AUTHENTICATION => [
        'openId' => [
            AuthenticationAbstractFactory::KEY_ADAPTER => 'openIdAdapter'
        ],
    ],
```
Мы видем что у нас есть `AuthenticationAction` с именем `'openId'`.
Так же с помощью ключа `AuthenticationAbstractFactory::KEY_ADAPTER` мы в качестве значения 
укажем ммя сервиса требуемого нам адаптера. 
Так же, конфиг имеет необязательный параметр `AuthenticationAbstractFactory::KEY_AUTHENTICATION_SERVICE`, 
который указывает на имя сервиса по которому можем получить требуемый  `AuthenticationService`. 
> По умолчанию будет использоваться `AuthenticationService::class` в качествве имени, либо данный сервис будет постоен вручную.

`AuthenticationAction` middleware проверит авторезирован ли пользователь, 
и если пользователь уже был авторезироавн он выбросит `AlreadyLogginException`.
Далее пройдет авторизация, и в случае если пользователь авторезирован, 
id пользователя будет положено в аттребут запроса - `identity`. Иначе, будет подготовлен ответ.

## Adapter

Давайте теперь перейдем к адаптерам. 
В данном случае мы используем WebAdapter, это адаптары которые сами получают данные из Request и готовят Response.
Так же дааные адапторы используют Resolver в качестве обьекта который валидирует получиные данные из Request.

По умолчанию доступны два WebAdapter. 

* `rollun\permission\Auth\Adapter\OpenID` - авторизация типа openID(/OAuth2).
* `Zend\Authentication\Adapter\Http` - авторизация типа baseAuth.

### OpenID adapter

Расмотрим конфиг для `OpenID`
    
```php
    OpenIDAdapterAbstractFactory::KEY_ADAPTER => [
        'openIdAdapter' => [
            OpenIDAdapterAbstractFactory::KEY_RESOLVER => 'openIdResolver',
        ]
    ],
```
Мы видим что у нас есть `OpenID` adapter с иминем `'openIdAdapter'`.
В качестве значения парамтра `OpenIDAdapterAbstractFactory::KEY_RESOLVER` мы указываем имя сервиса который вернет нам resolver.
Так же имеется не обязательный параметр `OpenIDAdapterAbstractFactory::KEY_WEB_CLIENT` который указывает на сервис WebClient.
 > По умолчанию будет использован `OpenIDAdapterAbstractFactory::DEFAULT_WEB_CLIENT` в качестве значения.

И не обязательный параметр `OpenIDAdapterAbstractFactory::KEY_ADAPTER_CONFIG` значение которого явзяеться массив с конфигом для адаптера.
> По умолчанию будет использован массив: 
```php
    [ OpenIDAdapterAbstractFactory::KEY_AC_REALM => OpenIDAdapterAbstractFactory::DEFAULT_REALM ]
```

### Http adapter

Расмотрим конфиг для `Http`

```php
    HttpAdapterAbstractFactory::KEY_ADAPTER => [
        'basicHttpAdapter' => [
            HttpAdapterAbstractFactory::KEY_BASIC_RESOLVER => 'httpBasicResolver',
        ]
    ],
```
Мы видим что у нас есть `Http` adapter с иминем `'basicHttpAdapter'`.
В качестве значения парамтра `HttpAdapterAbstractFactory::KEY_BASIC_RESOLVER` мы указываем имя сервиса который вернет нам resolver для basic Auth.
а качестве значения парамтра `HttpAdapterAbstractFactory::KEY_DIGEST_RESOLVER` мы указываем имя сервиса который вернет нам resolver для digest Auth.
> Относительно использоавного конфига требование указывать занчение данных параметров меняеся.
> По умолчанию, обязательным считаеться только `OpenIDAdapterAbstractFactory::KEY_BASIC_RESOLVER`. 

И не обязательный параметр `HttpAdapterAbstractFactory::KEY_ADAPTER_CONFIG` значение которого явзяеться массив с конфигом для адаптера.
Подробнее о конфиге можно прочесть в [документации к `Http` Adapter](https://framework.zend.com/manual/2.1/en/modules/zend.authentication.adapter.http.html#configuration-options)

> По умолчанию будет использован массив: 
```php
    [
        'accept_schemes' => 'basic',
        HttpAdapterAbstractFactory::KEY_AC_REALM => HttpAdapterAbstractFactory::DEFAULT_REALM,
        'nonce_timeout' => 3600,
    ]
```

## Resolver

Давайте теперь расмотрим предоставляемые данной библиотекой Resolver, доступные для адаптеров.

* OpenIDResolver - используя переданые credential, проверяет их валидность с помощью Web Client.

* UserDSResolver - используя переданые credential,  проверяет их валидность с помощью DataStore.

### OpenIDResolver 

Давайте подробнее рассмотрим данный Resolver.

```php
    OpenIDResolverAbstractFactory::KEY_RESOLVER => [
        'openIdResolver' => [
            OpenIDResolverAbstractFactory::KEY_USER_DS_SERVICE => 'userDS',
        ]
    ],
```

Мы видим что у нас есть `OpenID` resolver с иминем `'openIdResolver'`.
В качестве значения парамтра `OpenIDResolverAbstractFactory::KEY_USER_DS_SERVICE` мы указываем имя сервиса DataStore в котором храняться пользователи.
Так же имеется не обязательный параметр `OpenIDResolverAbstractFactory::KEY_WEB_CLIENT` который указывает на сервис WebClient.
> По умолчанию будет использован `OpenIDResolverAbstractFactory::DEFAULT_WEB_CLIENT` в качестве значения.
 
### UserDSResolver 

Давайте подробнее рассмотрим данный Resolver.

```php
    UserDSResolverAbstractFactory::KEY_RESOLVER => [
        'httpBasicResolver' => [
            UserDSResolverAbstractFactory::KEY_DS_SERVICE => 'userDS',
        ],
    ],
```

Мы видим что у нас есть `UserDS` resolver с иминем `'httpBasicResolver'`.
В качестве значения парамтра `UserDSResolverAbstractFactory::KEY_USER_DS_SERVICE` мы указываем имя сервиса DataStore в котором храняться пользователи.

## Authentication
Тпереь увидев как работает `AuthenticationAction` middleware, давайте посмотрим на работу системы в целом.

```php
    MiddlewarePipeAbstractFactory::KEY_AMP => [
        'identityPipe' => [
            'middlewares' => [
                \rollun\permission\Auth\Middleware\IdentifyAction::class,
                \rollun\permission\Auth\Middleware\UserResolver::class,
            ]
        ],
        'authPipe' => [
            'middlewares' => [
               'openId'
               'authReturnSwitch'
            ]
        ],
    ],
```
> Если вы не знакомы с [`MiddlewarePipeAbstractFactory`](https://github.com/rollun-com/rollun-actionrender/blob/master/docs/MiddlewarePipeAbstractFactory.md).

У нас есть два определенный pipeLine
* `identityPipe` - производит идентификацию пользователя.

* `authPipe` - производит аутентификацию пользователя.

### identityPipe 

Данный pipe состоит из двух middleware. 
Он идентифицирует пользоваеля, а потом кладет его данные в атребут запроса с иминем `user`.
C `IdentifyAction` мы уже разобрались, теперь посмотрим на `UserResolver`.

### UserResolver

UserResolver -  middleware который используя атребут запроса `identity`, ищет пользователя, и его роли.
Совмещает это в один массив, и помещает в атребут запроса `user`.

```php
    UserResolverFactory::KEY_USER_RESOLVER => [
        UserResolverFactory::KEY_USER_DS_SERVICE => 'userDS',
        UserResolverFactory::KEY_ROLES_DS_SERVICE => 'rolesDS',
        UserResolverFactory::KEY_USER_ROLES_DS_SERVICE => 'aclUserRolesDS',
    ],
```
Конфиг имеет три параметра
* `UserResolverFactory::KEY_USER_DS_SERVICE` - dataStore в котором храняться пользователи
* `UserResolverFactory::KEY_ROLES_DS_SERVICE` - dataStore в котором храняться роли пользователей
* `UserResolverFactory::KEY_USER_ROLES_DS_SERVICE` - dataStore в котором храняться соответсвия пользователя и роли.


### authPipe 

Pipe который производит аутентификацию пользователя.
Если пользователь аутентифицирован, то управление передается в [UserResolver](###%20UserResolver), а потом дальше по цепочке.
В случае если пользователь не был авторезирован, будет возвращен соответсвенный ответ.

C middleware [`'openId'`](##%20Authentication middleware) мы уже разобрались.
Давайте взглянем на `'authReturnSwitch'`.

### authReturnSwitch

Конфиг `LazyLoadSwitchAbstractFactory`.

```php
    LazyLoadSwitchAbstractFactory::LAZY_LOAD_SWITCH => [
        'authReturnSwitch' => [
            LazyLoadSwitchAbstractFactory::KEY_COMPARATOR_SERVICE => 'returnResultAttributeRequestComparator',
            LazyLoadSwitchAbstractFactory::KEY_MIDDLEWARES_SERVICE => [
                '/^true$/' => \rollun\actionrender\ReturnMiddleware::class,
                '/^false$/' => \rollun\permission\Auth\Middleware\UserResolver::class,
            ]
        ]
    ],
```
> Если вы не знакомы с [`LazyLoadSwitchAbstractFactory`](https://github.com/rollun-com/rollun-actionrender/blob/master/docs/LazyLoadSwitchAbstractFactory.md#requestcomparator)

Используется `returnResultAttributeRequestComparator`, в случае если значение атрибута запроса `returnResult` будет `true`
то управление будет переданно в `ReturnMiddleware`.
Иначе если оно будет `false`, то управление пойдет в `UserResolver` и дальше по цепочке запроса.
> Этот компаратор позволяет искать соответсвие по атребутам. 
Деталнее о нем прочесть можно [тут.](https://github.com/rollun-com/rollun-actionrender/blob/master/docs/LazyLoadSwitchAbstractFactory.md#requestcomparator)


Так же, если вы используете ACL, то вы запросто пожете связать ее с данным модулем аутентификации.
Для этого вам достаточно будет подключить `AccessForbiddenHandlerMiddleware`. 
После этого если пользователь зашел как гость, и у него небыло прав что бы получить доступ на какй то ресурс, 
он будет перенаправлен на страницу аутентификации.

> Подроюнее об [ACL читать тут](./ACL.md)

## Итог

Используя приведенную в примере настройку смистемы аутентификации, 
вы можете достаточно быстро и гибко настроить ее под свои нужды.