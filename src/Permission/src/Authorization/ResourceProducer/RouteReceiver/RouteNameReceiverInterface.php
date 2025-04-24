<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authorization\ResourceProducer\RouteReceiver;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Strategy of getting route name from request
 *
 * Interface RouteReceiverInterface
 * @package rollun\permission\Acl\ResourceProducer\RouteReceiver
 */
interface RouteNameReceiverInterface
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function receiveRouteName(Request $request);
}
