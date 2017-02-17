<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 16.02.17
 * Time: 14:53
 */

namespace rollun\permission\Comparator;

use Psr\Http\Message\ServerRequestInterface as Request;

class AttributeRequestComparator
{
    protected $attributeKey ;

    /**
     * AttributeRequestComparator constructor.
     * @param $attributeKey
     */
    public function __construct($attributeKey)
    {
        $this->attributeKey = $attributeKey;
    }

    public function __invoke(Request $request, $pattern)
    {
        $attribute = $request->getAttribute($this->attributeKey);
        return preg_match($pattern, $attribute);
    }
}
