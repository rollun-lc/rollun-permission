<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 01.03.17
 * Time: 18:02
 */

namespace rollun\permission\Auth\Adapter;

use rollun\dic\InsideConstruct;
use rollun\permission\Auth\Adapter\Interfaces\IdentityAdapterInterface;
use Zend\Authentication\Result;
use Zend\Session\Container;

class Session extends AbstractWebAdapter implements IdentityAdapterInterface
{
    const DEFAULT_SESSION_SERVICE_NAME = 'WebSessionContainer';

    const DEFAULT_SESSION_MEMBER = 'identity';

    /** @var  Container */
    protected $sessionContainer;

    /**
     * Session constructor.
     * @param array $config
     * @param Container|null $sessionContainer
     * @throws SessionContainerNotExistException
     */
    public function __construct(array $config, Container $sessionContainer = null)
    {
        InsideConstruct::setConstructParams(['sessionContainer' => static::DEFAULT_SESSION_SERVICE_NAME]);
        if (is_null($this->sessionContainer)) {
            throw new SessionContainerNotExistException(static::DEFAULT_SESSION_SERVICE_NAME);
        }
        parent::__construct($config);
    }

    /**
     * @return Result
     */
    public function identify()
    {
        if (!$this->sessionContainer->offsetExists(static::DEFAULT_SESSION_MEMBER)) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND,
                null,
                ['SessionStorage is empty']
            );
        } else {
            return new Result(
                Result::SUCCESS,
                $this->sessionContainer->offsetGet(static::DEFAULT_SESSION_MEMBER),
                ['SessionStorage is empty']
            );
        }
    }
}
