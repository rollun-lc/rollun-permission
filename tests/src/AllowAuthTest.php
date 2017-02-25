<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 25.02.17
 * Time: 10:47 AM
 */

namespace rollun\test\permission;


use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use rollun\permission\Comparator\AllowAuth;


class AllowAuthTest extends \PHPUnit_Framework_TestCase
{
    /** @var  AllowAuth */
    protected $object;

    public function setUp()
    {
        $config = [
            '/\/api/' => [
                'null',
            ],
            '/\/webhook/' => [
                'BaseAuth',
            ],
            '/\/rest/' => [
                'BaseAuth',
            ],
            '/\/login/' => [
                'OpenID',
                'null',
            ],
            '/\//' => [
                'null',
            ],
        ];
        $this->object = new AllowAuth($config);
    }

    /**
     *
     */
    public function pathDataProvider()
    {
        return [
            ["/", ['null']],
            ["/asdsd", ['null']],
            ["/login", ['OpenID', 'null',]],
            ["/logout", ['null']],
            ["/rest/rew", ['BaseAuth']],
            ["/rest/asd/12", ['BaseAuth']],
            ["/rest/asd?eq(q,q)", ['BaseAuth']],
            ["/api/asd/12", ['null']],
            ["/api/asd?eq(q,q)", ['null']],
            ["/webhook/asd?eq(q,q)", ['BaseAuth']],
            ["/webhook/asd/12", ['BaseAuth']],
        ];
    }

    /**
     * @param $path
     * @param $expectedAllow
     * @dataProvider pathDataProvider()
     */
    public function testAllowAuth($path, $expectedAllow)
    {
        $request = $this->getMock(ServerRequestInterface::class);
        //getPath
        $uri = $this->getMock(UriInterface::class);
        $uri->method('getPath')->will($this->returnValue($path));
        $request->method('getUri')->will($this->returnValue($uri));
        $actualAllow = $this->object->getAllowAuth($request);

        $this->assertEquals($expectedAllow, $actualAllow);
    }
}
