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
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Authentication\Result;
use Zend\Authentication\Storage\Session as SessionStorage;

class Session extends AbstractWebAdapter implements IdentityAdapterInterface
{
    const DEFAULT_SESSION_NAMESPACE = 'Auth\Adapter\Session';

    const DEFAULT_SESSION_MEMBER = 'identity';

    const DEFAULT_SESSION_STORAGE_SERVICE = SessionStorage::class;

    /** @var  SessionStorage */
    protected $sessionStorage;

    public function __construct(array $config, SessionStorage $sessionStorage = null)
    {
        InsideConstruct::setConstructParams(['sessionStorage' => static::DEFAULT_SESSION_STORAGE_SERVICE]);
        if (!isset($this->$sessionStorage)) {
            $this->sessionStorage = new SessionStorage(static::DEFAULT_SESSION_NAMESPACE, static::DEFAULT_SESSION_MEMBER);
        }
        parent::__construct($config);
    }

    /**
     * @return Result
     */
    public function identify()
    {
        if ($this->sessionStorage->isEmpty()) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND,
                null,
                ['SessionStorage is empty']
            );
        } else {
            return new Result(
                Result::SUCCESS,
                $this->sessionStorage->read(),
                ['SessionStorage is empty']
            );
        }
    }
}
