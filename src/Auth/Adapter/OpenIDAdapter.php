<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 30.01.17
 * Time: 13:37
 */

namespace rollun\permission\Auth\Adapter;

use rollun\api\Api\Google\Client\Web;
use rollun\datastore\DataStore\DataStoreAbstract;
use rollun\datastore\Rql\RqlQuery;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class OpenIDAdapter implements AdapterInterface
{

    /** @var  DataStoreAbstract */
    protected $userDataStore;

    /** @var  Web */
    protected $webClient;

    /** @var  string */
    protected $code;

    /** @var string */
    protected $state;

    /**
     * OpenIDAdapter constructor.
     * @param Web $webClient
     * @param DataStoreAbstract $dataStore
     */
    public function __construct(Web $webClient, DataStoreAbstract $dataStore)
    {
        $this->webClient = $webClient;
        $this->userDataStore = $dataStore;
    }

    /**
     * @param $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    public function setState($state)
    {
        $this->state = $state;
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
            if($this->webClient->getResponseState() !== $this->state) {
                return new Result(
                    Result::FAILURE,
                    null,
                    ["State not equalse."]
                );
            }
            if ($this->webClient->authByCredential()) {
                $userId = $this->webClient->getUserId();
                $user = $this->userDataStore->read($userId);
                if (!empty($user)) {
                    //unset($user['pass'])
                    return new Result(
                        Result::SUCCESS,
                        $user,
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
