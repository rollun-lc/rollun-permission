<?php
/**
 * @copyright Copyright © 2014 Rollun LC (http://rollun.com/)
 * @license LICENSE.md New BSD License
 */

namespace rollun\permission\Acl\ResourceProducer;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Strategy of creating acl resources
 *
 * Interface ResourceProducerInterface
 * @package rollun\permission\Acl\ResourceProducer
 */
interface ResourceProducerInterface
{
    /**
     * Produce acl resource from any data, which can get from request
     * If method cannot produce resource it throw exception
     *
     * @param Request $request
     * @return string
     * @throws InvalidArgumentException
     */
    public function produce(Request $request): string;

    /**
     * Check if this resource producer can produce resource
     *
     * @param Request $request
     * @return bool
     */
    public function canProduce(Request $request): bool;
}
