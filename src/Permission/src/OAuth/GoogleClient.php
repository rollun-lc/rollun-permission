<?php

namespace rollun\permission\OAuth;

use Exception;
use Google_Client;
use Google_Service_Oauth2;
use Psr\Log\LoggerInterface;
use rollun\dic\InsideConstruct;

class GoogleClient extends Google_Client
{
    /**
     * Query param in google response
     * Use for indicate user-agent according to OAuth 2.0 RFC
     */
    const KEY_STATE = 'state';

    /**
     * Special authorization code that google give
     * It is using to fetch access token
     * Query param in google response
     */
    const KEY_CODE = 'code';

    public function __construct(array $config = [], LoggerInterface $logger = null)
    {
        parent::__construct($config);
        $this->setApprovalPrompt(null);
        InsideConstruct::setConstructParams(["logger" => LoggerInterface::class]);
    }

    /**
     * Authentication passed if access token can be fetch using authorization code
     *
     * @param $authCode
     * @return bool
     * @throws Exception
     */
    public function authenticateWithAuthCode($authCode)
    {
        $accessToken = $this->fetchAccessTokenWithAuthCode($authCode);
        $this->getLogger()->debug('Access token with auth code: ' . json_encode($accessToken));

        return !array_key_exists('error', $accessToken);
    }

    /**
     * Proxy for parent method
     * Get new access token if it expired using refresh token
     *
     * @return array
     */
    public function getAccessToken()
    {
        $this->getLogger()->debug('Get access token. If expired try to update');

        if ($this->isAccessTokenExpired() && $this->getRefreshToken()) {
            $this->fetchAccessTokenWithRefreshToken($this->getRefreshToken());
        }

        return parent::getAccessToken();
    }

    /**
     * @return string|null
     */
    public function getIdToken()
    {
        $token = $this->getAccessToken();
        $idToken = $token['id_token'];

        if (!isset($token['id_token'])) {
            $this->getLogger()->debug("Missing 'id_token' in access token");
            return null;
        }

        if ($this->verifyIdToken($idToken)) {
            [$headerEndoced, $payloadEndoced, $signEndoced] = explode('.', $idToken);
            $payload = \Firebase\JWT\JWT::jsonDecode(\Firebase\JWT\JWT::urlsafeB64Decode($payloadEndoced));
            $payload = json_decode(json_encode($payload), true);
            $this->getLogger()->debug('auth payload', ['payload' => $payload]);


            return $payload['sub'];
        }

        return null;
    }

    /**
     * @return string
     */
    public function getUserEmail()
    {
        $service = new Google_Service_Oauth2($this);
        $user = $service->userinfo->get();
        $this->getLogger()->debug('UserInfo', ['user' => $user]);


        return $user->email;
    }
}
