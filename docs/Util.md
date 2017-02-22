# rollun-permission

Перечень классов и фабрик.

## Error Handler

* AccessForbiddenHandlerMiddleware - обрабатывает ошибки связаные с ограничением доспутпа к ресерсу.

* AlreadyLogginHandler - обрабатывает ошибку повторной попытки логина 
 
* CredentialErrorHandlerMiddleware - обрабатывает ошибки данных для авторизации

## Adapter 

* OpenID - адаптер логина по openID для AuthenticationService. 

### Factory
* AdapterAbstractFactoryAbstract - абстрактная фабрика для адаптеров.
* HttpAdapterAbstractFactory - фабрика для http адаптера
* OpenIDAdapterAbstractFactory - фабрика для OpenID адаптера.

## Resolver
* OpenIDResolver - Резолвер который проверяет коректность credential используя OpenID Google WebClient 
* UserDSResolver - Резолвер который проверяет коректность credential в DataStore  

### Factory
* OpenIDResolverAbstractFactory - фабрика для OpenIDResolver.
* UserDSResolverAbstractFactory - фабрика для UserDSResolver.

## Utils

Используя ConfigDataSourceAbstractFactory мы можем поднимать dataSource из конфигов.

## Middleware 

* AuthenticationAction - middleware для аутентификации. 
В зависимоти от переданного адаптера, будет изменяться метод авторизации.  
* IdentifyAction - middleware который идентифицирует пользователя. И кладет его id в request под атребутом identity.
* LogoutAction - middleware который позволяет вылогиниться.
* UserResolver - middleware который по id достает пользователя и его роли. Кладет их в request под атребутом user.