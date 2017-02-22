<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 22.02.17
 * Time: 11:41
 */

namespace rollun\permission\Auth\Adapter;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Adapter\Http\ResolverInterface;
use Zend\Http\Request as HTTPRequest;
use Zend\Http\Response as HTTPResponse;

abstract class AbstractWebAdapter implements AdapterInterface
{
    abstract public function setRequest(HTTPRequest $request);

    abstract public function setResponse(HTTPResponse $response);

    abstract public function setResolver(ResolverInterface $resolver);
}
