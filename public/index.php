<?php
// Delegate static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server'
    && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
) {
    return false;
}
// try http://__zaboy-rest/api/rest/index_StoreMiddleware?fNumberOfHours=8&fWeekday=Monday
// Change to the project root, to simplify resolving paths
chdir(dirname(__DIR__));

require 'vendor/autoload.php';
require_once 'config/env_configurator.php';

// Define application environment - 'dev' or 'prop'
if (constant('APP_ENV') === 'dev') {
    error_reporting(E_ALL &(~E_WARNING));
    ini_set('display_errors', 1);
}
//todo:: add remove error

/** @var \Interop\Container\ContainerInterface $container */
$container = require 'config/container.php';

/** @var \Zend\Expressive\Application $app */
$app = $container->get(\Zend\Expressive\Application::class);
$app->run();
//file_put_contents(realpath('data/log'), "", FILE_APPEND);
//file_put_contents(realpath('data/log'), "query: $queryString\n", FILE_APPEND);
/*try{

} catch(\Exception $e) {
    file_put_contents(realpath('data/log'), "error" . $e->getMessage() . "\n", FILE_APPEND);
}*/