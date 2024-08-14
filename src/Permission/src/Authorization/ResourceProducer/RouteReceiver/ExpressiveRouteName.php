<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authorization\ResourceProducer\RouteReceiver;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;

class ExpressiveRouteName implements RouteNameReceiverInterface
{
    public function receiveRouteName(Request $request)
    {
        $expressiveRouteClassName = 'Mezzio\Router\RouteResult';
        $routeResult = $request->getAttribute($expressiveRouteClassName);

        if (!is_a($routeResult, $expressiveRouteClassName, true)) {
            throw new InvalidArgumentException(
                "Invalid request attribute '{$expressiveRouteClassName}', "
                . "instance of '{$expressiveRouteClassName}' expected"
            );
        }

        return $routeResult->getMatchedRouteName();
    }
}
