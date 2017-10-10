<?php

use PHPUnit\Framework\TestCase;
use PixelStorage\Client;
use PixelStorage\Image;

class BasicTest extends TestCase
{

    public static function methodProvider()
    {
        $reflection = new ReflectionClass('PixelStorage\Image');

        $methods = [];
        $client  = new Client('http://prefix.com/', 'foo', 'bar');
        $object  = new Image('1', '2', $client);
        foreach ($reflection->getMethods() as $method) {
            if (substr($method->getName(), 0, 2) === '__' || !$method->isPublic() || $method->getName() === 'url') {
                continue;
            }
            $params = array_fill(0, $method->getNumberOfRequiredParameters(), 1);
            $methods[] = [$object, $method->getName(), $params];
        }
        return $methods;
    }

    /**
     * @dataProvider methodProvider
     */
    public function testFluent(Image $object, $method, $params)
    {
        $this->assertEquals($object, call_user_func_array([$object, $method], $params));
    }

    /**
     * @dataProvider methodProvider
     */
    public function testFiltersQueueing(Image $object, $method, $params)
    {
        $this->assertFalse((bool)preg_match("@/{$method}/@", $object->url()));
        $this->assertEquals($object, call_user_func_array([$object, $method], $params));
        $this->assertTrue((bool)preg_match("@/{$method}/@", $object->url()));
    }
}
