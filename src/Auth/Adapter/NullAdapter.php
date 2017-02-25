<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 24.02.17
 * Time: 4:02 PM
 */

namespace rollun\permission\Auth\Adapter;


use InvalidArgumentException;
use rollun\permission\Comparator\AllowAuth;
use rollun\utils\Json\Serializer;
use Zend\Authentication\Adapter\Http\ResolverInterface;
use Zend\Authentication\Result;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class NullAdapter extends AbstractWebAdapter
{
    /** @var  AllowAuth */
    protected $allowAuthResolver;



    /**
     * Performs an authentication attempt
     *
     * @return Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {

        $data = Serializer::jsonSerialize($this->allowAuthResolver->getAllowAuth($this->request));
        $this->request->withAttribute('requestData', $data);
        return new Result(
            Result::FAILURE_CREDENTIAL_INVALID,
            null,
            ['Invalid or absent credentials; challenging client']
        );
    }


}