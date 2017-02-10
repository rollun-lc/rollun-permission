# rollun-permission

Библиотка позволяющая настроить доступ к приложение используя DataStore в качестве хранилища.
И использовать OAuth2 в качестве метода аутентификации.

В данной библиотеки используется аутентификация c сервисом google.

В конфиге `acl.global.php` описаны настройи acl и авторизации.

Для того что бы сконфигурировать ACL вам нужно указать в конфиге `acl` четыри сервиса dataStore 
которые будет представлять из себя хрангилища для 
* AclFromDataStoreFactory::KEY_DS_RULE_SERVICE - правил  
* AclFromDataStoreFactory::KEY_DS_ROLE_SERVICE - ролей 
* AclFromDataStoreFactory::KEY_DS_RESOURCE_SERVICE - ресурсов 
* AclFromDataStoreFactory::KEY_DS_PRIVILEGE_SERVICE - привилегий

Данные хранилища должны иметь предопределенные поля
AclFromDataStoreFactory::KEY_DS_RULE_SERVICE 
* id - id
* role_id - id роли 
* resource_id - id ресурса 
* privilege_id - id привелегии 
* allow_flag - флаг указывающий на запрет или разрешение правила

AclFromDataStoreFactory::KEY_DS_ROLE_SERVICE - ролей 

* id - id
* name - имя роли
* parent_id - id родительской роли

AclFromDataStoreFactory::KEY_DS_RESOURCE_SERVICE - ресурсов 
*id - id
*name - имя ресурса
*pattern - патерн определеня url+query
*parent_id - id родительского ресурса

* AclFromDataStoreFactory::KEY_DS_PRIVILEGE_SERVICE - привилегий
* id - id
* name - имя привелегии.