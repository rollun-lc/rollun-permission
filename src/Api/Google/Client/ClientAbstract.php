<?php

namespace rollun\permission\Api\Google\Client;

use \Google_Client;
use Zend\Diactoros\Response\RedirectResponse;
use rollun\api\ApiException;

/**
 * vendor\bin\InstallerSelfCall.bat "rollun\api\Api\Gmail\CredentialsInstaller" install
 */
abstract class ClientAbstract extends Google_Client
{
    const SECRET_PATH = 'data/Api/Google/';

    const DEFAULT_CLIENT_NAME = 'client_secret';

    protected $clientName;

    protected $code;

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function __construct($config = [], $code = null, $clientName = null)
    {
        parent::__construct($config);
        $this->code = $code;
        $this->clientName = $clientName ?: static::DEFAULT_CLIENT_NAME;
        $this->setConfigFromSecretFile();
    }

    protected function setConfigFromSecretFile()
    {
        $clientSecretFilename = $this->getClientName() . '.json';
        $clientSecretFullFilename = static::SECRET_PATH . $clientSecretFilename;
        if (file_exists($clientSecretFullFilename)) {
            $this->setAuthConfig($clientSecretFullFilename);
            return $clientSecretFullFilename;
        }
        return false;
    }

    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * @return bool
     * If credential set return true another else.
     */
    public function trySetCredential()
    {
        $accessToken = $this->getAccessToken();
        $accessToken = $accessToken ?: $this->getSavedCredential();
        if ($accessToken) {
            if ($this->isAccessTokenExpired()) {
                $accessToken = $this->refreshAccessToken($accessToken);
                $this->saveCredential($accessToken);
                return true;
            }
        } elseif (($authCode = $this->getAuthCode()) !== null) {
            $accessToken = $this->refreshAccessToken($accessToken);
            $this->saveCredential($accessToken);
            return true;
        }
        return false;
    }

    abstract public function getSavedCredential();

    public function refreshAccessToken($accessToken = null)
    {
        // save refresh token to some variable
        $refreshTokenSaved = $this->getRefreshToken();
        if ($refreshTokenSaved && $accessToken) {
            // update access token
            $this->fetchAccessTokenWithRefreshToken($refreshTokenSaved);
            // append refresh token
            $accessToken['refresh_token'] = $refreshTokenSaved;
        } else {
            $authCode = $this->getAuthCode();
            $accessToken = $this->fetchAccessTokenWithAuthCode($authCode);
        }
        return $accessToken;
    }

    abstract public function saveCredential($accessToken);

    public function getAuthCode()
    {
        return $this->code;
    }

    /**
     * @param $state string crypt token
     * @return RedirectResponse
     */
    public function getCodeResponse($state)
    {
        $this->setState($state);
        $authUrl = $this->createAuthUrl();
        return new RedirectResponse($authUrl, 302, ['Location' => filter_var($authUrl, FILTER_SANITIZE_URL)]);
    }


}
