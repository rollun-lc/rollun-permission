<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Acl\ResourceProducer;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Acl\ResourceProducer\RouteReceiver\RouteNameReceiverInterface;

class RouteAttribute
{
    const DEFAULT_DELIMITER = '-';

    /**
     * @var string
     */
    protected $attributeName;

    /**
     * @var RouteNameReceiverInterface
     */
    protected $routeNameReceiver;

    /**
     * RouteAttribute constructor.
     * @param RouteNameReceiverInterface $routeNameReceiver
     * @param mixed $attributeName
     */
    public function __construct(RouteNameReceiverInterface $routeNameReceiver, $attributeName)
    {
        $this->routeNameReceiver = $routeNameReceiver;
        $this->attributeName = $attributeName;
    }

    /**
     * @param Request $request
     * @return string
     */
    public function produce(Request $request): string
    {
        $dataStoreService = $request->getAttribute($this->attributeName);

        if (empty($dataStoreService)) {
            throw new InvalidArgumentException("Invalid request attribute '{$this->attributeName}'");
        }

        $routeName = $this->routeNameReceiver->receiveRouteName($request);

        return $routeName . self::DEFAULT_DELIMITER . $dataStoreService;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function canProduce(Request $request): bool
    {
        return !empty($request->getAttribute($this->attributeName));
    }
}
