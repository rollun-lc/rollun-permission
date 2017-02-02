<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 30.01.17
 * Time: 13:37
 */

namespace rollun\permission\Auth\Adapter;

use rollun\api\Api\Google\ClientAbstract;
use rollun\datastore\DataStore\DataStoreAbstract;
use rollun\datastore\Rql\RqlQuery;
use rollun\permission\Api\Google\Client\OpenID;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class OpenIDAdapter implements AdapterInterface
{

    /** @var  DataStoreAbstract */
    protected $userDataStore;

    /** @var  OpenID */
    protected $openIDGoogleClient;

    /** @var  string */
    protected $code;

    /**
     * OpenIDAdapter constructor.
     * @param OpenID $googleClient
     * @param DataStoreAbstract $dataStore
     */
    public function __construct(OpenID $googleClient, DataStoreAbstract $dataStore)
    {
        $this->openIDGoogleClient = $googleClient;
        $this->userDataStore = $dataStore;
    }

    /**
     * @param $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {
        try {
            if ($this->openIDGoogleClient->trySetCredential()) {
                $userId = $this->openIDGoogleClient->getUniqueId();
                $user = $this->userDataStore->read($userId);
                if (!empty($user)) {
                    return new Result(
                        Result::SUCCESS,
                        $userId,//$userId
                        ['Fail credential']
                    );
                }
            }
            return new Result(
                Result::FAILURE,
                null,
                ['Fail credential']
            );
        } catch (\Exception $e) {
            return new Result(
                Result::FAILURE,
                null,
                [$e->getMessage()]
            );
        }
    }
}
