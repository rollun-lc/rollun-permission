<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 16.02.17
 * Time: 14:53
 */

namespace rollun\permission\Comparator;

use Psr\Http\Message\ServerRequestInterface as Request;

class AllowAuthResolver
{
    protected $configs;

    const DEFAULT_TYPE = 'null';

    /**
     * [
     *      'path' => [
     *          \\type,...
     *      ],
     * ]
     * AllowAuthResolver constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->configs = $config;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getAllowAuth(Request $request)
    {
        $actualPath = $request->getUri()->getPath();
        foreach ($this->configs as $expectedPathPattern => $typeList) {
            if(preg_match($expectedPathPattern, $actualPath)) {
                return $typeList;
            }
        }
        return [static::DEFAULT_TYPE];
     }
}
