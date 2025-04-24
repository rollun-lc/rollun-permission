<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Authorization\ResourceProducer;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;
use rollun\permission\Authorization\ResourceProducer\RouteReceiver\RouteNameReceiverInterface;

class RouteAttribute extends RouteName
{
    const DEFAULT_DELIMITER = '-';

    /**
     * @var string
     */
    protected $attributeName;

    /**
     * RouteAttribute constructor.
     * @param RouteNameReceiverInterface $routeNameReceiver
     * @param mixed $attributeName
     */
    public function __construct(RouteNameReceiverInterface $routeNameReceiver, $attributeName)
    {
        parent::__construct($routeNameReceiver);
        $this->attributeName = $attributeName;
    }

    /**
     * @param Request $request
     * @return string
     */
    public function produce(Request $request): string
    {
        $attributeValue = (string)$request->getAttribute($this->attributeName);

        if (empty($attributeValue)) {
            throw new InvalidArgumentException("Invalid request attribute '{$this->attributeName}'");
        }

        return parent::produce($request) . self::DEFAULT_DELIMITER . $attributeValue;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function canProduce(Request $request): bool
    {
        return parent::canProduce($request) && !empty($request->getAttribute($this->attributeName));
    }
}
