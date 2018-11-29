<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Auth;

use Zend\ServiceManager\Factory\AbstractFactoryInterface;

/**
 * Create instance of BasicAccess using 'config' service stored in ContainerInterface
 *
 * Config example
 *
 * <code>
 *  [
 *      PhpSessionAbstractFactory::class => [
 *          'serviceRequestedName1' => [
 *              'realm' => 'realmValue',
 *              'userRepository' => 'userRepositoryService'
 *              'responseFactory' => 'responseFactoryServiceOrCallable', optional
 *          ],
 *          'serviceRequestedName2' => [
 *              'authenticationServices' => 'authenticationServiceName2'
 *          ],
 *          // ...
 *      ]
 *  ]
 * </code>
 *
 * Class PhpSessionAbstractFactory
 * @package rollun\permission\Auth
 */
class PhpSessionAbstractFactory implements AbstractFactoryInterface
{

}
