<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\OAuth;

use Exception;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Zend\Expressive\Authentication\UserRepositoryInterface;
use Zend\Expressive\Helper\UrlHelper;

abstract class CredentialMiddleware extends OAuthMiddleware
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @var callable
     */
    private $authorizedResponseFactory;

    /**
     * @var callable
     */
    private $unauthorizedResponseFactory;

    /**
     * CredentialMiddleware constructor.
     * @param GoogleClient $googleClient
     * @param UserRepositoryInterface $userRepository
     * @param UrlHelper $urlHelper
     * @param callable $unauthorizedResponseFactory
     * @param callable $authorizedResponseFactory
     * @param LoggerInterface $logger
     * @param array $config
     */
    public function __construct(
        GoogleClient $googleClient,
        UserRepositoryInterface $userRepository,
        UrlHelper $urlHelper,
        callable $unauthorizedResponseFactory,
        callable $authorizedResponseFactory,
        LoggerInterface $logger,
        $config = []
    ) {
        parent::__construct($googleClient, $urlHelper, $logger, $config);

        $this->userRepository = $userRepository;

        // Ensures type safety of the composed factory
        $this->authorizedResponseFactory = function (
            ServerRequestInterface $request
        ) use ($authorizedResponseFactory) : ResponseInterface {
            return $authorizedResponseFactory($request);
        };

        // Ensures type safety of the composed factory
        $this->unauthorizedResponseFactory = function (
            ServerRequestInterface $request
        ) use ($unauthorizedResponseFactory) : ResponseInterface {
            return $unauthorizedResponseFactory($request);
        };
    }

    /**
     * Check if user-agent is authenticated
     *
     * 1. Check state according to OAuth 2.0
     * 2. Try to retrieve access token with authorization code
     *
     * @param ServerRequestInterface $request
     * @return bool|ResponseInterface
     * @throws Exception
     */
    public function isOAuthAuthenticated(ServerRequestInterface $request)
    {
        $queryParams = $request->getQueryParams();
        $code = $queryParams[GoogleClient::KEY_CODE] ?? null;
        $state = $queryParams[GoogleClient::KEY_STATE] ?? null;

        $this->logger->debug(
            json_encode(
                [
                    'Incoming authorization code: ' . $code,
                    'Incoming state: ' . $state,
                    'Redirect uri: ' . $this->getConfig('redirectUri'),
                ]
            )
        );

        if ($this->getSession($request)->get(GoogleClient::KEY_STATE) != $state) {
            $this->logger->debug("Invalid incoming state");

            return false;
        }

        if (!$this->googleClient->authenticateWithAuthCode($code)) {
            $this->logger->debug("Authentication with authorization code failed");

            return false;
        }

        return true;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    protected function getAuthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->authorizedResponseFactory)($request)->withStatus(200);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    protected function getUnAuthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->unauthorizedResponseFactory)($request)->withStatus(401);
    }
}
