<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.01.17
 * Time: 14:56
 */

namespace rollun\permission\Api;

use Google_Service;
use Google_Service_Drive;
use Google_Service_Gmail;
use Google_Service_Plus;
use Google_Service_Plus_PersonEmails;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\installer\Command;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Stratigility\MiddlewareInterface;

class OAuth2Action implements MiddlewareInterface
{

    /**
     * Process an incoming request and/or response.
     *
     * Accepts a server-side request and a response instance, and does
     * something with them.
     *
     * If the response is not complete and/or further processing would not
     * interfere with the work done in the middleware, or if the middleware
     * wants to delegate to another process, it can use the `$out` callable
     * if present.
     *
     * If the middleware does not return a value, execution of the current
     * request is considered complete, and the response instance provided will
     * be considered the response to return.
     *
     * Alternately, the middleware may return a response instance.
     *
     * Often, middleware will `return $out();`, with the assumption that a
     * later middleware will return a response.
     *
     * @param Request $request
     * @param Response $response
     * @param null|callable $out
     * @return null|Response                                7е
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        session_start();
        $client = new \Google_Client();
        $clientCredentials = Command::getDataDir() . DIRECTORY_SEPARATOR .
            'Api' . DIRECTORY_SEPARATOR .
            'Google' . DIRECTORY_SEPARATOR .
            'OpenIDAuthClient.json';
        $client->setAccessType('offline');
        $client->setAuthConfig($clientCredentials);
        $client->setRedirectUri('http://' . constant("HOST") . '/oauth2r');
        //$client->setAccessType("offline");
        $client->addScope(Google_Service_Plus::USERINFO_EMAIL);
/*unset($_SESSION['access_token']);
return null;*/
        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            //if($client->isAccessTokenExpired()) {
            $about = [];
            //TODO: можно рефрешить сколько влезет
            for ($i = 0; $i < 250; $i++) {
                try {
                    $client->setAccessToken($_SESSION['access_token']);
                    $refreshToken = $client->getRefreshToken();
                    $client->fetchAccessTokenWithRefreshToken($refreshToken);
                    $accessToken = $client->getAccessToken();
                    $accessToken['refresh_token'] = $refreshToken;
                    $_SESSION['access_token'] = $accessToken;

                    $service = new Google_Service_Plus($client);
                    $about[] = $service->people->get('me')->getEmails();
                    //$client->revokeToken($accessToken);
                } catch (\Exception $exception) {
                    $about[] = $exception->getMessage();
                }
            }

            //}
            //http://stackoverflow.com/questions/11606101/how-to-get-user-email-from-google-plus-oauth
            $email = '';
            $response = new JsonResponse($about);
        } else {
            $authUrl = $client->createAuthUrl();
            $response = new RedirectResponse($authUrl, 302, ['Location' => filter_var($authUrl, FILTER_SANITIZE_URL)]);
        }

        return $response;
    }
}
