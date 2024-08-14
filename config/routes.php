<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use rollun\datastore\Middleware\DataStoreApi;
use rollun\permission\ConfigProvider;
use rollun\permission\OAuth\LoginMiddleware;
use rollun\permission\OAuth\LogoutMiddleware;
use rollun\permission\OAuth\RedirectMiddleware;
use rollun\permission\OAuth\RegisterMiddleware;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Application;
use Mezzio\MiddlewareFactory;

/**
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Action\HomePageAction::class, 'home');
 * $app->post('/album', App\Action\AlbumCreateAction::class, 'album.create');
 * $app->put('/album/:id', App\Action\AlbumUpdateAction::class, 'album.put');
 * $app->patch('/album/:id', App\Action\AlbumUpdateAction::class, 'album.patch');
 * $app->delete('/album/:id', App\Action\AlbumDeleteAction::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Action\ContactAction::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Action\ContactAction::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Action\ContactAction::class,
 *     Mezzio\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 *
 * @param Application $app
 * @param MiddlewareFactory $factory
 * @param ContainerInterface $container
 * @return void
 */
return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    // Show logs in debugging mode
    $app->route(
        '/api/datastore[/{resourceName}[/{id}]]',
        DataStoreApi::class,
        ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
        'datastore'
    );

    $app->get(
        '/',
        function (ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {
            return new HtmlResponse('Home page!');
        },
        'home-page'
    );

    $app->get(
        '/oauth/redirect',
        RedirectMiddleware::class,
        'oauth-redirect'
    );

    $app->get(
        '/oauth/login',
        LoginMiddleware::class,
        ConfigProvider::OAUTH_LOGIN_ROUTE_NAME
    );

    $app->get(
        '/oauth/register',
        RegisterMiddleware::class,
        ConfigProvider::OAUTH_REGISTER_ROUTE_NAME
    );

    $app->get(
        '/oauth/logout',
        LogoutMiddleware::class,
        'oauth-logout'
    );
};
