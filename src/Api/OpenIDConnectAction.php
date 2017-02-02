<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 27.01.17
 * Time: 14:56
 */

namespace rollun\permission\Api;

use Firebase\JWT\JWT;
use Google_Service;
use Google_Service_Drive;
use Google_Service_Gmail;
use Google_Service_Oauth2;
use Google_Service_Oauth2_Jwk;
use Google_Service_Plus;
use Google_Service_Plus_PersonEmails;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\installer\Command;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Http\Client;
use Zend\Stratigility\MiddlewareInterface;

class OpenIDConnectAction implements MiddlewareInterface
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
            'Google' . DIRECTORY_SEPARATOR .
            'Api' . DIRECTORY_SEPARATOR .
            'client_secret.json';
        $client->setAuthConfig($clientCredentials);
        //$client->setAccessType("offline");
        $client->setRedirectUri('http://' . constant("HOST") . '/openidr');

        if (!isset($_SESSION['code']) ) {
            if (!isset($_SESSION['state'])) {
                $state = sha1(openssl_random_pseudo_bytes(1024));
                $_SESSION['state'] = $state;
            } else {
                $state = $_SESSION['state'];
            }

            $client->setState($state);
            $client->addScope('openid');

            $authUrl = $client->createAuthUrl();
            $response = new RedirectResponse($authUrl, 302, ['Location' => filter_var($authUrl, FILTER_SANITIZE_URL)]);
        } else {
            $jwt = $_SESSION['auth']['id_token'];
            if ($client->verifyIdToken($jwt)) {
                $tks = explode('.', $jwt);
                list($headb64, $bodyb64, $cryptob64) = $tks;
                $playload = json_decode(base64_decode($bodyb64));
                $data = [
                    'status' => 'success',
                    'sub' => $playload->sub
                ];
            } else {
                $data = ['status' => 'error'];
            };
            $response = new JsonResponse($data);
        }

        return $response;
    }
}
