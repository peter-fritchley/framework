<?php

namespace Tomahawk\Cache\Tests;

use Doctrine\Common\Cache\MemcacheCache;
use Tomahawk\Cache\Provider\MemcacheProvider;
use Tomahawk\Test\TestCase;

class CacheMemcacheProviderTest extends TestCase
{
    public function testGetNameReturnsArray()
    {
        $cache = $this->getCacheMock();

        $arrayProvider = new MemcacheProvider($cache);

        $this->assertEquals('memcache', $arrayProvider->getName());
    }

    public function testArrayCacheSavesAndReturnsTrue()
    {
        $cache = $this->getCacheMock();
        
        $cache->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $arrayProvider = new MemcacheProvider($cache);
        
        $this->assertTrue($arrayProvider->save('foo', 'bar'));
    }

    public function testArrayCacheSavesAndReturnsFalse()
    {
        $cache = $this->getCacheMock();

        $cache->expects($this->once())
            ->method('save')
            ->will($this->returnValue(false));

        $arrayProvider = new MemcacheProvider($cache);

        $this->assertFalse($arrayProvider->save('foo', 'bar'));
    }

    public function testContains()
    {
        $cache = $this->getCacheMock();

        $cache->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $cache->expects($this->once())
            ->method('contains')
            ->will($this->returnValue(true));

        $arrayProvider = new MemcacheProvider($cache);

        $arrayProvider->save('foo', 'bar');

        $this->assertTrue($arrayProvider->contains('foo'));
    }

    public function testFetch()
    {
        $cache = $this->getCacheMock();

        $cache->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $cache->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue('bar'));

        $arrayProvider = new MemcacheProvider($cache);

        $arrayProvider->save('foo', 'bar');

        $this->assertEquals('bar', $arrayProvider->fetch('foo'));
    }

    public function testDelete()
    {
        $cache = $this->getCacheMock();

        $cache->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $cache->expects($this->once())
            ->method('delete');

        $cache->expects($this->once())
            ->method('contains')
            ->will($this->returnValue(false));

        $arrayProvider = new MemcacheProvider($cache);

        $arrayProvider->save('foo', 'bar');

        $arrayProvider->delete('foo');

        $this->assertFalse($arrayProvider->contains('foo'));
    }

    public function testFlush()
    {
        $cache = $this->getCacheMock();

        $cache->expects($this->exactly(2))
            ->method('save')
            ->will($this->returnValue(true));

        $cache->expects($this->once())
            ->method('flushAll');

        $cache->expects($this->exactly(2))
            ->method('contains')
            ->will($this->returnValue(false));

        $arrayProvider = new MemcacheProvider($cache);

        $arrayProvider->save('foo', 'bar');
        $arrayProvider->save('baz', 'boom');

        $arrayProvider->flush();

        $this->assertFalse($arrayProvider->contains('foo'));
        $this->assertFalse($arrayProvider->contains('baz'));
    }

    protected function getCacheMock()
    {
        $cache = $this->getMockBuilder('Doctrine\Common\Cache\MemcacheCache')
            ->disableOriginalConstructor()
            ->getMock();

        return $cache;
    }

}
