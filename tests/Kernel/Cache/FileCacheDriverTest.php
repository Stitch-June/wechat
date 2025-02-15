<?php


namespace EasySwoole\WeChat\Tests\Kernel\Cache;


use EasySwoole\WeChat\Kernel\Cache\FileCacheDriver;
use EasySwoole\WeChat\Tests\TestCase;
use Iterator;
use Psr\SimpleCache\CacheInterface;

class FileCacheDriverTest extends TestCase
{

    /** @var CacheInterface */
    protected $fileCache;

    public function setUp(): void
    {
        $this->fileCache = new FileCacheDriver(__DIR__ . '/Tmp');
        parent::setUp(); // TODO: Change the autogenerated stub
    }

    public function testSet()
    {
        $key = 'testKey';
        $value = ['a', 'b', 'c'];
        $result = $this->fileCache->set($key . '1', $value);
        $this->assertTrue($result);

        $result = $this->fileCache->set($key . '2', $value, -1);
        $this->assertFalse($result);

        $result = $this->fileCache->set($key . '3', $value, 1);
        $this->assertTrue($result);
    }

    public function testGet()
    {
        $key = 'testKey';
        $value = ['a', 'b', 'c'];
        $this->testSet();
        $this->assertEquals($value, $this->fileCache->get($key . '1'));
        $this->assertEquals('no', $this->fileCache->get($key . '2', 'no'));
        $this->assertEquals($value, $this->fileCache->get($key . '3'));
        sleep(2);
        $this->assertEquals('no', $this->fileCache->get($key . '3', 'no'));
    }

    public function testDelete()
    {
        $key = 'testKey';
        $this->testSet();
        $this->assertTrue($this->fileCache->delete($key . '1'));
        $this->assertFalse($this->fileCache->delete($key . '2'));
    }

    public function testGetMultiple()
    {
        $this->testSetMultiple();
        $result = $this->fileCache->getMultiple(['testKey1', 'testKey2', 'testKey3'], 'no');
        $this->assertEquals($result, array(
                'testKey1' =>
                    array(
                        0 => 'a',
                        1 => 'b',
                        2 => 'c',
                    ),
                'testKey2' => 'abc',
                'testKey3' =>
                    array(
                        0 => 'a',
                        1 => 'b',
                        2 => 'c',
                    ),
            )
        );

        $iterator = new class implements Iterator {
            private $array = array(
                "testKey1" => '1',
                "testKey2" => '2',
                "testKey3" => '3'
            );

            function rewind()
            {
                reset($this->array);
            }

            function current()
            {
                return current($this->array);
            }

            function key()
            {
                return key($this->array);
            }

            function next()
            {
                return next($this->array);
            }

            function valid()
            {
                return $this->key() !== null;
            }
        };
        $this->assertTrue($this->fileCache->setMultiple($iterator));

        $iterator = new class implements Iterator {
            private $array = array(
                "testKey1",
                "testKey2",
                "testKey3"
            );

            public function __construct()
            {
                $this->position = 0;
            }

            function rewind()
            {
                $this->position = 0;
            }

            function current()
            {
                return $this->array[$this->position];
            }

            function key()
            {
                return $this->position;
            }

            function next()
            {
                ++$this->position;
            }

            function valid()
            {
                return isset($this->array[$this->position]);
            }
        };

        $this->assertEquals([
            "testKey1" => '1',
            "testKey2" => '2',
            "testKey3" => '3'
        ], $this->fileCache->getMultiple($iterator));
    }

    public function testSetMultiple()
    {
        $result = $this->fileCache->setMultiple(array(
            'testKey1' =>
                array(
                    0 => 'a',
                    1 => 'b',
                    2 => 'c',
                ),
            'testKey2' => 'abc',
            'testKey3' =>
                array(
                    0 => 'a',
                    1 => 'b',
                    2 => 'c',
                ),
        ));
        $this->assertTrue($result);
    }

    public function testDeleteMultiple()
    {


        $this->testSetMultiple();
        $res = $this->fileCache->deleteMultiple(['testKey1', 'testKey2', 'testKey3']);
        $this->assertTrue($res);

        $this->testSetMultiple();
        $res = $this->fileCache->deleteMultiple($iterator = new class implements Iterator {
            private $array = array(
                "testKey1",
                "testKey2",
                "testKey3"
            );

            public function __construct()
            {
                $this->position = 0;
            }

            function rewind()
            {
                $this->position = 0;
            }

            function current()
            {
                return $this->array[$this->position];
            }

            function key()
            {
                return $this->position;
            }

            function next()
            {
                ++$this->position;
            }

            function valid()
            {
                return isset($this->array[$this->position]);
            }
        });
        $this->assertTrue($res);

        $res = $this->fileCache->deleteMultiple(['testKey1', 'testKey2', 'testKey3']);
        $this->assertFalse($res);
    }

    public function testHas()
    {
        $this->testSet();
        $this->assertTrue($this->fileCache->has('testKey1'));
        $this->assertFalse($this->fileCache->has('testKey5'));
    }

    public function testClear()
    {
        $this->testSet();
        $this->assertTrue($this->fileCache->clear());
    }
}
