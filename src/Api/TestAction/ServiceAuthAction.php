<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 07.02.17
 * Time: 14:58
 */

namespace rollun\permission\Api;

use Firebase\JWT\JWT;
use Google\Auth\Credentials\AppIdentityCredentials;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\OAuth2;
use Google_Service_Drive;
use Google_Service_Gmail;
use Google_Service_Oauth2;
use function GuzzleHttp\Psr7\build_query;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\StreamWrapper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\installer\Command;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Http\Client;
use Zend\Stratigility\MiddlewareInterface;

class ServiceAuthAction implements MiddlewareInterface
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
     * @return null|Response
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $clientCredentials = Command::getDataDir() . DIRECTORY_SEPARATOR .
            'Api' . DIRECTORY_SEPARATOR .
            'Google' . DIRECTORY_SEPARATOR .
            'OpenIDAuthClient.json';
        //$client->setAuthConfig($clientCredentials);

        //$credentials = new AppIdentityCredentials('https://www.googleapis.com/auth/sqlservice.admin');

        $stream = new Stream(fopen($clientCredentials, "r"));
        $credentials = ServiceAccountCredentials::makeCredentials([\Google_Service_Drive::DRIVE_READONLY], $stream);
        $data1 = [];
        try {
            //$credentials->setSub("it.professor02@gmail.com");
            $data1 = $credentials->fetchAuthToken();
            $http = new Client("www.googleapis.com/oauth2/v4/token");
            $http->setHeaders([
                'Cache-Control' => 'no-store',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]);
            $http->setMethod("POST");
            //$http->setRawBody(build_query($params));
            $params = [
                "grant_type" => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                "assertion" => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJ0ZXN0LTgwOUByb2xsdW4tcGVybWlzc2lvbi5pYW0uZ3NlcnZpY2VhY2NvdW50LmNvbSIsImF1ZCI6Imh0dHBzOlwvXC93d3cuZ29vZ2xlYXBpcy5jb21cL29hdXRoMlwvdjRcL3Rva2VuIiwiZXhwIjoxNDg2NTY3NTEyLCJpYXQiOjE0ODY1NjM4NTIsInNjb3BlIjoiaHR0cHM6XC9cL3d3dy5nb29nbGVhcGlzLmNvbVwvYXV0aFwvZHJpdmUucmVhZG9ubHkifQ.Whaa5EaJkmffB7O8t4286OlnxKkjEMt3Ygpk7RihLbf1I7zu918zsDDsotMzVTwIWJKNTJ6VhnOc59FAmRt_3l3o_NDPOyzsDMpZ3c-rAHaxhM-WR6ErXMuKOOAE-x_fPFar9uwtmHaHtMgVmyQDzpyjyRLYKsDw-nMbHRb6ZWFFviHjb1KLniffq33hGHSGp142JICGAlr1iFpxciemWsRgcs2sJ8wjYhP54Uvk95fZxjh3F7pBFRled6cJrPNV2a599ZqMRGUHy4VPjDLdmBm_MmCsGINszM5PxndlNY_dLs4lUJszpP9kAP-tcx24R4x9VPFOuNNy0SAU8iptnA'
            ];
            $http->setParameterPost($params);


        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        $data2 = [];
        try {
            $client = new \Google_Client();
            $client->setAccessToken($data1);
            //$client->setAuthConfigFile($clientCredentials);
            $client->addScope([\Google_Service_Drive::DRIVE_READONLY]);
            //$client->setSubject("test-809@rollun-permission.iam.gserviceaccount.com");
            $client->setAccessType('offline');

            $gDriveService = new Google_Service_Drive($client);
            $files = $gDriveService->files->listFiles()->getFiles();

        } catch (\Exception $e) {
            $error = isset($error) ? [ $error, $e->getMessage() ]: $e->getMessage();
        }
        /*$date = new \DateTime();
        $data = [
            "iss" => $client->getClientId(),
            "scope" => 'https://www.googleapis.com/auth/prediction',
            "aud" => "https://accounts.google.com/o/oauth2/token",
            "exp" => $date->getTimestamp() + 1800,
            "iat" => $date->getTimestamp(),
        ];

        $key = "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCfm51sNioAExAn\nS0cQOxWUzlBpbHRCzLgRaOuMXJy82qRQxbmMORlbMc6Y+/x6Ltq+YUItFZmSfuRM\n4ceQ4NRIJh4fhggdhsO2X08Su/YkqmjN83asa9LjKkW/bBKcXXNFahVVwNYF+bRp\nJTYU7cRJ6PKoD6qI8YlWP7KXJFO4Vl4lrm55k7UO06024d2tG/a3vQtgZzNdM6/l\nFhdNfXWvlwEvRAXQ0oIgk+DGv5uu940S8XhLXzMR45C+MmEAX84KxWtMrrSPgp5s\ns1/OtSXnhjqRYOeHmQ9V150U7FWxL4YfmNdYmIayqjSVibxXKpHuw6p7cOj4auWW\n5STOoDxXAgMBAAECggEAB0MnaJMWWiaD56XPN/fYRYsVsZZTu+5guboSbbKRFy7D\np0E37h9y/elIQ0HD/TNAlMUMI80FzdqkPLNRR1BTmGzTlg+dMuq84QF5MZsH2ic+\n2ZAoaDQHxnpX7hvWxPKjmhb3nY1Gr8Lq6JXe3hjrg4lr8SW1VsHWw+vmDmaCwLJq\n8i4/LWI7L77u6c5T4+lDGojVn4D6uzIapYouxXkrs3KG0EElAM8aapX8RMrBE/Zx\ni1Qk3jwHzTpyiJH/uqSsCxOONQy0dNYTdcsRWnZC/zU2yhiPPjA8x6iXDEUpUJ7L\nNyL1SfQEhxbEev6zmWPh8BATRkWRHmA7vECUbb3/wQKBgQDRWaol3Sh05hsPNLN/\nmckkVFyEeF7pzKWrM10Xla32VVs52rNgwiSLxeHemjNIZS/BSU4pvC8gmwBRbnnZ\n7MV6z8e1UXR8HvJCXrHlLECnDH9H6uJr5Mt6EVp8wa9cw9TYpOIU3wAXzsDFw5X+\nb1FmxqSEP++HmBa9c8EcJg9JcQKBgQDDLGhwIgzEtgRzXT7gX3U+6wXbhh0K4Vot\n7pqU87zaeG7ADVB+bfZQLa0gq1gkApE9IIsUqj0eNBODuyhGzovX+UP/En/4x5GJ\nOOJ5pRBFYfZ3Fn8OB3ZHJ4auPp3YUtFVk5hVMpKN31YnaiVPqxy0APlC4EMbVDGi\n29wIzXG+RwKBgHAY5vanWUZfABZATe0BV6bQVUnJemkOX5cwRaSfTSsdwV9VL7+b\ntR3ys2MShms5YzzIF8ZZMZLv7FeuJCkAky2TnIgGOa1MlMPdGLxx2ZyZIH5N0zea\npymRqTYsL84oPgxTHYu3bMFSv/4lIGfBC4FQ1D7MTWH5mhOPq9N6vazBAoGAKhR/\nB/4vZpIf74ehMNsj+kbN4oDN7jScLt8M8SSECU6CAmJcgoXO34aZlzuaK1lqWxWT\nJwd1Wfe8ZWCK1Ilf2Vbi9DHW6ZqNpFphafzOv0bZzt9I0YuGUt0Qyqyxd5yTibHi\n3CulMXV/q8vU85JfA+hZ1bNohJHoicaBcFxrM60CgYEAy2B08EkuDojX+mX8SkiC\ndl16u33Y7vubEOr9xaLDdbO+95vNVMuG0ryHUBVq5r0Tv2jR5mZ9QtwJFOpDLMHH\nMBDDwRW3EvXh1TdKjpWEqsfqpsTF2rHwGuZ1kT+OU9nnjzKMgBeEaJfOBbXYPQYf\nII7VgHGhi5tn8agOzNAHxs0=\n-----END PRIVATE KEY-----\n";
        $keyId = "e9a3538dec7570ab19e76e9387826a7910c62877";
        $jwt = JWT::encode($data, $key, "RS256", $keyId);

        $url = 'https://accounts.google.com/o/oauth2/token';
        $clientHttp = new Client($url);
        $clientHttp->setMethod("POST");
        $clientHttp->setParameterPost([
            'grant_type' => rawurlencode('urn:ietf:params:oauth:grant-type:jwt-bearer'),
            'assertion' => $jwt
        ]);



        $resp = $clientHttp->send();*/
        if (!empty($data1) && isset($files) && !isset($error)) {
            return new JsonResponse(['status' => 'success', 'data1' => $data1, 'files' => $files ]);
        } else {
            return new JsonResponse(['status' => 'error', 'error' => $error]);
        }
    }
}
