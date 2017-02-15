<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 15.02.17
 * Time: 15:59
 */

namespace rollun\permission\Auth\Adapter\Resolver;

use InvalidArgumentException;
use rollun\datastore\DataStore\DataStoreAbstract;
use rollun\datastore\Rql\RqlQuery;
use Zend\Authentication\Adapter\Http\ResolverInterface;
use Zend\Authentication\Result;

class UserDataStore implements ResolverInterface
{

    const KEY_NAME = 'name';

    const KEY_PASSWORD = 'password';

    /** @var  DataStoreAbstract $userDataStore */
    protected $userDataStore;

    /**
     * DataStore constructor.
     * @param $userDataStore
     */
    public function __construct($userDataStore)
    {
        $this->userDataStore = $userDataStore;
    }

    /**
     * Resolve username/realm to password/hash/etc.
     *
     * @param  string $username Username
     * @param  string $realm Authentication Realm
     * @param  string $password Password (optional)
     * @return string|array|false User's shared secret as string if found in realm, or User's identity as array
     *         if resolved, false otherwise.
     */
    public function resolve($username, $realm, $password = null)
    {
        if (empty($username)) {
            throw new InvalidArgumentException('Username is required');
        } elseif (!ctype_print($username) || strpos($username, ':') !== false) {
            throw new InvalidArgumentException(
                'Username must consist only of printable characters, excluding the colon'
            );
        }
        if (empty($realm)) {
            throw new InvalidArgumentException('Realm is required');
        } elseif (!ctype_print($realm) || strpos($realm, ':') !== false) {
            throw new InvalidArgumentException(
                'Realm must consist only of printable characters, excluding the colon.'
            );
        }
        if (empty($password)) {
            throw new InvalidArgumentException('Password is required');
        }

        $result = $this->userDataStore->query(
            new RqlQuery("and(eq(" . static::KEY_NAME . ",$username),eq(" . static::KEY_PASSWORD . ",$password)")
        );
        if(empty($result)) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null, ['Username not found in provided htpasswd file']);
        }
        //todo identity
        return new Result(Result::SUCCESS, $username);
    }
}
