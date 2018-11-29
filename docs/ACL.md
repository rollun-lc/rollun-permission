# ACL

## Введение

Access Control List или ACL — список контроля доступа, который определяет, 
кто или что может получать доступ к конкретному объекту, и какие именно операции разрешено или запрещено этому субъекту 
проводить над объектом.

Данный модуль предоставляет возможность настроить и использовать Zend ACL вметсе с Middleware.

## QuickStart 

> Если вы нехотите вдоваться в подробности работы ACL, и хотите получить рабочий минимум, то достаточно прочесть данный параграф.

Что бы получить минимальо рабочий вариант вам достаточно указать настройки в конфигурационном файле.

Добавить `'aclPipe'` и `'identityPipe'` в конфиг `middleware_pipeline`.
> Приоритет у `'identityPipe'` должен быть выше.

Используйте файл `acl.dataStore.php` с настройкой DataStore по умолчанию.
>Не забудте изменить название файла, а так же добавить в него свои правила в случае надобности.

Пример конфига пайпов.

```php
    MiddlewarePipeAbstractFactory::KEY_AMP => [
        'identityPipe' => [
            'middlewares' => [
                \rollun\permission\Auth\Middleware\IdentifyAction::class,
                \rollun\permission\Auth\Middleware\UserResolver::class,
            ]
        ],
        'aclPipes' => [
            'middlewares' => [
                \rollun\permission\Acl\Middleware\RoleResolver::class,
                \rollun\permission\Acl\Middleware\ResourceResolver::class,
                \rollun\permission\Acl\Middleware\PrivilegeResolver::class,
                \rollun\permission\Acl\Middleware\AclMiddleware::class,
            ]
        ]
    ],
```

## Принцип работы 

Что бы разобраться  в работа ACL давайте взгоянем на `'aclPipe'`.

```php
    'aclPipes' => [
        'middlewares' => [
            \rollun\permission\Acl\Middleware\RoleResolver::class,
            \rollun\permission\Acl\Middleware\ResourceResolver::class,
            \rollun\permission\Acl\Middleware\PrivilegeResolver::class,
            \rollun\permission\Acl\Middleware\AclMiddleware::class,
        ]
    ]
```

Как мы видем он сожержить в себе четыре middleware.
Давайте ознакомимся с ними поближе.

### RoleResolver
RoleResolver - middleware цель которого получить роли пользователя и положить их в запрос под атребутом `roles`.
> Данный middleware предпологает что он был запущен после `'identityPipe'`, так что вам стоит убедиться в правильности приоритетов.
    
### ResourceResolver
ResourceResolver - middleware который получает ресурс.
В данном случае ресурсом является запись в dataStore, паттерн который удволетворяет строка `url + query` запроса.
Данный middleware зависит от resourceDS который ему передаст фабрика из конфига ACL.
Имя данного ресурса он кладет в атребут запроса с иминем `resource`.

### PrivilegeResolver
PrivilegeResolver - middleware который получает из запроса привилегию и кладет ее в ардебут под иминем `privilege`.
В данном случае в роли привелегии выступает тип запроса.

### AclMiddleware
AclMiddleware - middleware в котором происходит проверка доступа.
Используя атребуты которые установили предыдущее Resolver, проверяет наличие доступа.
В случае если доступ разрешен, запрос идет в свой роут, иначе будет выброшен `AccessForbiddenException`.
В качетсве зависимости использует саму ACL.

## ACL

Сам обект ACL конструируется с помощью фабрики `rollun\permission\Acl\Factory\AclFromDataStoreFactory`

## Конфигурация ACL  


Для того что бы сконфигурировать ACL вам нужно указать в конфиге `acl` четыри сервиса dataStore 
которые будет представлять из себя dataStore.

```php
    'acl' => [
        AclFromDataStoreFactory::KEY_DS_RULE_SERVICE => 'rulesDS',
        AclFromDataStoreFactory::KEY_DS_ROLE_SERVICE => 'rolesDS',
        AclFromDataStoreFactory::KEY_DS_RESOURCE_SERVICE => 'resourceDS',
        AclFromDataStoreFactory::KEY_DS_PRIVILEGE_SERVICE => 'privilegeDS',
    ],
```

* AclFromDataStoreFactory::KEY_DS_RULE_SERVICE - хранилище правил  
* AclFromDataStoreFactory::KEY_DS_ROLE_SERVICE - хранилище ролей 
* AclFromDataStoreFactory::KEY_DS_RESOURCE_SERVICE - хранилище ресурсов 
* AclFromDataStoreFactory::KEY_DS_PRIVILEGE_SERVICE - хранилище привилегий


### Обязательные DataStore 

Данные хранилища должны обязательно иметь указаные предопределенные поля.
Данные назавния будут использоваться по умолчанию.

#### rulesDS
* id - id
* role_id - id роли 
* resource_id - id ресурса 
* privilege_id - id привелегии 
* allow_flag - флаг указывающий на запрет или разрешение правила

#### rolesDS
* id - id  
* name - имя роли  
* parent_id - id родительской роли  

#### resourceDS
* id - id  
* name - имя ресурса  
* pattern - патерн определеня url+query   
* parent_id - id родительского ресурса  

#### privilegeDS
* id - id  
* name - имя привелегии.  

#### userDS 
* id - id  
* name - имя пользователя

#### userRolesDS
* id - id  
* role_id - id роли
* user_id - id пользователя


