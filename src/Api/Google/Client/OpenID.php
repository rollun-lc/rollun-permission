<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 30.01.17
 * Time: 15:55
 */

namespace rollun\permission\Api\Google\Client;

use rollun\api\Api\Google\ClientAbstract;
use Zend\Session\Container as SessionContainer;

class OpenID extends ClientAbstract
{

    /** @var SessionContainer */
    protected $sessionContainer;

    public function __construct(array $config = [], $code = null, $clientName = null)
    {
        parent::__construct($config, $code, $clientName);
    }

    public function saveCredential($accessToken)
    {
        $this->sessionContainer->accessToken = $accessToken;
    }

    public function getSavedCredential()
    {
        return $this->sessionContainer->accessToken ?: null;
    }

    /**
     * Return user unique id.
     * An identifier for the user, unique among all Google accounts and never reused.
     * @return string|null
     */
    public function getUniqueId()
    {
        $token = $this->getAccessToken();
        $idToken = $token['id_token'];
        if ($this->verifyIdToken($idToken)) {
            $tks = explode('.', $idToken);
            list($headb64, $bodyb64, $cryptob64) = $tks;
            $playload = json_decode(base64_decode($bodyb64));
            return $playload->sub;
        }
        return null;
    }
}
