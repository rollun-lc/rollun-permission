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
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class OpenIDAdapter implements AdapterInterface
{

    /** @var  DataStoreAbstract */
    protected $userDataStore;

    /** @var  ClientAbstract */
    protected $googleClient;

    /** @var  string */
    protected $code;

    /**
     * OpenIDAdapter constructor.
     * @param ClientAbstract $googleClient
     */
    public function __construct(ClientAbstract $googleClient)
    {
        $this->googleClient = $googleClient;
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
        $result = '';
        try {
            $authData = $this->googleClient->authenticate($this->code);
            $authData[''];
        } catch (\Exception $e) {
            $result = new Result(
                Result::FAILURE,
                null,
                [$e->getMessage()]
            );
        }

        return $result;
    }
}
