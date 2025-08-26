<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

declare(strict_types = 1);

use Symfony\Component\Dotenv\Dotenv;
use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

// To enable or disable caching, set the `ConfigAggregator::ENABLE_CACHE` boolean in
// `config/autoload/local.php`.
$cacheConfig = [
    'config_cache_path' => 'data/cache/config-cache.php',
];

// Determine application environment ('dev', 'test' or 'prod').
if(file_exists('.env')) {
    (new Dotenv())->usePutenv(true)->load('.env');
}
// Determine application environment ('dev' or 'prod').
$appEnv = getenv('APP_ENV');

$aggregator = new ConfigAggregator([
    \Laminas\Cache\Storage\Adapter\Redis\ConfigProvider::class,
    \Mezzio\Authentication\Session\ConfigProvider::class,
    \Mezzio\Authentication\Basic\ConfigProvider::class,
    \Laminas\Filter\ConfigProvider::class,
    \rollun\utils\Metrics\ConfigProvider::class,
    \rollun\utils\FailedProcesses\ConfigProvider::class,
    \Laminas\Cache\Storage\Adapter\Filesystem\ConfigProvider::class,
    \Laminas\Diactoros\ConfigProvider::class,
    \Mezzio\Session\ConfigProvider::class,
    \Mezzio\Session\Ext\ConfigProvider::class,
    \Mezzio\Authentication\ConfigProvider::class,
    \rollun\repository\ConfigProvider::class,
    \Mezzio\ConfigProvider::class,
    \Laminas\Cache\ConfigProvider::class,
    \Laminas\Mail\ConfigProvider::class,
    \Laminas\Db\ConfigProvider::class,
    \Laminas\Validator\ConfigProvider::class,
    \Mezzio\Router\FastRouteRouter\ConfigProvider::class,
    \Laminas\HttpHandlerRunner\ConfigProvider::class,
    \Mezzio\Helper\ConfigProvider::class,
    \Mezzio\Router\ConfigProvider::class,
//    \OpenAPI\ConfigProvider::class,
//    \Laminas\Serializer\ConfigProvider::class,
//    \Laminas\Cache\Storage\Adapter\BlackHole\ConfigProvider::class,
    // Include cache configuration
    new ArrayProvider($cacheConfig),
    // Rollun config
    \rollun\uploader\ConfigProvider::class,
    \rollun\datastore\ConfigProvider::class,
    \rollun\logger\ConfigProvider::class,
    \rollun\permission\ConfigProvider::class,


    // Default App module config
    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `global.php`
    //   - `*.global.php`
    //   - `local.php`
    //   - `*.local.php`
    new PhpFileProvider('config/autoload/{{,*.}global,{,*.}local}.php'),
    // Load application config according to environment:
    //   - `global.dev.php`,   `global.test.php`,   `prod.global.prod.php`
    //   - `*.global.dev.php`, `*.global.test.php`, `*.prod.global.prod.php`
    //   - `local.dev.php`,    `local.test.php`,     `prod.local.prod.php`
    //   - `*.local.dev.php`,  `*.local.test.php`,  `*.prod.local.prod.php`
    new PhpFileProvider(realpath(__DIR__) . "/autoload/{{,*.}global.{$appEnv},{,*.}local.{$appEnv}}.php"),
    // Load development config if it exists
    new PhpFileProvider(realpath(__DIR__) . '/development.config.php'),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
