<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 22.02.17
 * Time: 11:41
 */

namespace rollun\permission\Auth\Adapter;

use InvalidArgumentException;
use rollun\permission\Auth\Adapter\Factory\AuthAdapterAbstractFactory;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Adapter\Http\ResolverInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

abstract class AbstractWebAdapter
{
    /** @var  Request */
    protected $request;

    /** @var  Response */
    protected $response;

    /** @var string */
    protected $realm;

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * OpenIDAdapter constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        // Double-quotes are used to delimit the realm string in the HTTP header,
        // and colons are field delimiters in the password file.
        if (empty($config['realm'])) {
            $this->realm = AuthAdapterAbstractFactory::DEFAULT_REALM;
        } else if (!ctype_print($config['realm']) ||
            strpos($config['realm'], ':') !== false ||
            strpos($config['realm'], '"') !== false
        ) {
            throw new InvalidArgumentException(
                'Config key \'realm\' is required, and must contain only printable characters,'
                . 'excluding quotation marks and colons'
            );
        } else {
            $this->realm = $config['realm'];
        }
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

}
