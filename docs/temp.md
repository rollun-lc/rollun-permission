### error-handler
role: guest ? +
non-ajax ? +
redirect login

role: guest ? +
non-ajax ? -
return forbidden.

role: guest ? -
return forbidden.

### quick-auth

quick-auth-type
try-auth ? +
session-set

quick-auth-type
try-auth ? -

### login-service
lazy-auth-type ? baseAuth, login-pass, openID-google, openID-facebook, openID-google(code), openID-facebook(code), openID(Authtoken)
auth ? +
session-set
{next action-render}

lazy-auth-type
auth ? ~
{next action-render }

lazy-auth-type
auth ? -
{next action-render }

### session+quick
session ? +

session ? -
quick-auth
session ? +

session ? -
quick-auth
session ? -


session+quick ? +
identification role: user
ACL ? +
{next action}

session+quick ? +
identification role: user
ACL ? -
error-handler

session+quick ? -
identification role: guest
ACL ? -
error-handler

session+quick ? -
identification role: guest
ACL ? +
{next action}


Аутентификация разделаетья на нсколько этапов.

С самого начала происходит проверка., возможно ли провести быструю аутентификацию, 
в случае если такову провести возможно, собираеться массив из возможных способов провести аутентификацию.
Далее происходит аутеньтификация, если пользователю удалось аутентифицироваться, т