<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 28.01.17
 * Time: 10:22 AM
 */

namespace rollun\permission\Auth\Adapter;


use Zend\Authentication\Adapter\AdapterInterface;

class OAuth2Adapter implements AdapterInterface
{

    /** @var  string OAuth2 token */
    protected $code;

    /** @var  \Google_Client */
    protected $client;

    public function __construct(\Google_Client $client)
    {
        $this->client = $client;
    }



    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {
        $result = $this->client->authenticate($this->code);
    }
}