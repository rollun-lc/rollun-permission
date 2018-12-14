<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authorization\ResourceProducer;

use rollun\permission\Authorization\ResourceProducer\RouteReceiver\RouteNameReceiverInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class RouteName implements ResourceProducerInterface
{
    /**
     * @var RouteNameReceiverInterface
     */
    protected $routeNameReceiver;

    /**
     * RouteAttribute constructor.
     * @param RouteNameReceiverInterface $routeNameReceiver
     */
    public function __construct(RouteNameReceiverInterface $routeNameReceiver)
    {
        $this->routeNameReceiver = $routeNameReceiver;
    }

    /**
     * @param Request $request
     * @return string
     */
    public function produce(Request $request): string
    {
        return (string)$this->routeNameReceiver->receiveRouteName($request);
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function canProduce(Request $request): bool
    {
        return true;
    }
}
