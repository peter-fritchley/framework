<?php

/*
 * This file is part of Tomahawk.
 *
 * As the Tomahawk container is heavily based on pimple, so are the tests
 *
 */

namespace Tomahawk\DI\Tests;

use Tomahawk\Test\TestCase;
use Tomahawk\DI\Container;
use Tomahawk\DI\Test\Service;
use Tomahawk\DI\Test\Invokable;
use Tomahawk\DI\Test\NonInvokable;

class ContainerTest extends TestCase
{
    /**
     * @expectedException \Tomahawk\DI\Exception\InstantiateException
     */
    public function testNonInstantiable()
    {
        $container = new Container();
        $container->get('Tomahawk\DI\Test\AbstractService');
    }

    public function testNoConstructor()
    {
        $container = new Container();
        $container->get('Tomahawk\DI\Test\Service2');
    }

    public function testDefaultValue()
    {
        $container = new Container();
        $container->get('Tomahawk\DI\Test\Service3');
    }

    /**
     * @expectedException \Tomahawk\DI\Exception\BindingResolutionException
     */
    public function testNoDefaultValue()
    {
        $container = new Container();
        $container->get('Tomahawk\DI\Test\Service4');
    }

    public function testClassDefaultValue()
    {
        $container = new Container();
        $container->get('Tomahawk\DI\Test\Service5');
    }

    /**
     * @expectedException \Tomahawk\DI\Exception\BindingResolutionException
     */
    public function testClassNoDefaultValue()
    {
        $container = new Container();
        $container->get('Tomahawk\DI\Test\Service6');
    }

    public function testClassBuildable()
    {
        $container = new Container();
        $container['ServiceInterface'] = new Service();

        $this->assertTrue($container->has('ServiceInterface'));
        $this->assertFalse($container->has('NotExistentInterface'));
    }


    public function testClassBuildableNonRegistered()
    {
        $container = new Container();
        $service = $container->get('Tomahawk\DI\Test\Service');

        $this->assertInstanceOf('Tomahawk\DI\Test\Service', $service);
    }

    public function testWithString()
    {
        $container = new Container();
        $container['param'] = 'value';

        $this->assertEquals('value', $container['param']);
    }

    public function testAlias()
    {
        $container = new Container();
        $container['ServiceInterface'] = new Service();
        $container->addAlias('my_service', 'ServiceInterface');
        $service = $container->get('my_service');

        $this->assertInstanceOf('Tomahawk\DI\Test\Service', $service);

        $service = $container['my_service'];

        $this->assertInstanceOf('Tomahawk\DI\Test\Service', $service);

        $container->removeAlias('my_service');
        $this->assertFalse($container->hasAlias('my_service'));
    }

    public function testWhenObjectIsPassesItIsReturned()
    {
        $container = new Container();
        $this->assertInstanceOf('Tomahawk\DI\Test\Service', $container->get(new Service()));
    }

    public function testWithClosure()
    {
        $container = new Container();
        $container['service'] = function () {
            return new Service();
        };

        $this->assertInstanceOf('Tomahawk\DI\Test\Service', $container['service']);
    }

    public function testServicesShouldBeDifferent()
    {
        $container = new Container();
        $container['service'] = $container->factory(function () {
            return new Service();
        });

        $serviceOne = $container['service'];
        $this->assertInstanceOf('Tomahawk\DI\Test\Service', $serviceOne);

        $serviceTwo = $container['service'];
        $this->assertInstanceOf('Tomahawk\DI\Test\Service', $serviceTwo);

        $this->assertNotSame($serviceOne, $serviceTwo);
    }

    public function testShouldPassContainerAsParameter()
    {
        $container = new Container();
        $container['service'] = function () {
            return new Service();
        };
        $container['container'] = function ($container) {
            return $container;
        };

        $this->assertNotSame($container, $container['service']);
        $this->assertSame($container, $container['container']);
    }

    public function testIsset()
    {
        $container = new Container();
        $container['param'] = 'value';
        $container['service'] = function () {
            return new Service();
        };

        $container['null'] = null;

        $this->assertTrue(isset($container['param']));
        $this->assertTrue(isset($container['service']));
        $this->assertTrue(isset($container['null']));
        $this->assertFalse(isset($container['non_existent']));
    }

    public function testConstructorInjection()
    {
        $params = array("param" => "value");
        $container = new Container($params);

        $this->assertSame($params['param'], $container['param']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" is not defined.
     */
    public function testOffsetGetValidatesKeyIsPresent()
    {
        $container = new Container();
        echo $container['foo'];
    }

    public function testOffsetGetHonorsNullValues()
    {
        $container = new Container();
        $container['foo'] = null;
        $this->assertNull($container['foo']);
    }

    public function testUnset()
    {
        $container = new Container();
        $container['param'] = 'value';
        $container['service'] = function () {
            return new Service();
        };

        unset($container['param'], $container['service']);
        $this->assertFalse(isset($container['param']));
        $this->assertFalse(isset($container['service']));
    }

    /**
     * @dataProvider serviceDefinitionProvider
     */
    public function testShare($service)
    {
        $container = new Container();
        $container['shared_service'] = $service;

        $serviceOne = $container['shared_service'];
        $this->assertInstanceOf('Tomahawk\DI\Test\Service', $serviceOne);

        $serviceTwo = $container['shared_service'];
        $this->assertInstanceOf('Tomahawk\DI\Test\Service', $serviceTwo);

        $this->assertSame($serviceOne, $serviceTwo);
    }

    /**
     * @dataProvider serviceDefinitionProvider
     */
    public function testProtect($service)
    {
        $container = new Container();
        $container['protected'] = $container->protect($service);

        $this->assertSame($service, $container['protected']);
    }

    public function testGlobalFunctionNameAsParameterValue()
    {
        $container = new Container();
        $container['global_function'] = 'strlen';
        $this->assertSame('strlen', $container['global_function']);
    }

    public function testRawFactory()
    {
        $container = new Container();
        $container['service'] = $definition = $container->factory(function () { return 'foo'; });

        $this->assertSame($definition, $container->raw('service'));
    }

    public function testRawClosure()
    {
        $container = new Container();

        $container['service'] = function () {
            return new Service();
        };

        $service = $container->get('service');

        $this->assertInstanceOf('Tomahawk\DI\Test\Service', $service);

        $this->assertInstanceOf('Closure', $container->raw('service'));
    }

    public function testRawHonorsNullValues()
    {
        $container = new Container();
        $container['foo'] = null;
        $this->assertNull($container->raw('foo'));
    }

    public function testFluentRegister()
    {
        $container = new Container;
        $this->assertSame($container, $container->register($this->getMock('Tomahawk\DI\ServiceProviderInterface')));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" is not defined.
     */
    public function testRawValidatesKeyIsPresent()
    {
        $container = new Container();
        $container->raw('foo');
    }

    /**
     * @dataProvider serviceDefinitionProvider
     */
    public function testExtend($service)
    {
        $container = new Container();
        $container['shared_service'] = function () {
            return new Service();
        };
        $container['factory_service'] = $container->factory(function () {
            return new Service();
        });

        $container->extend('shared_service', $service);
        $serviceOne = $container['shared_service'];
        $this->assertInstanceOf('Tomahawk\DI\Test\Service', $serviceOne);
        $serviceTwo = $container['shared_service'];
        $this->assertInstanceOf('Tomahawk\DI\Test\Service', $serviceTwo);
        $this->assertSame($serviceOne, $serviceTwo);
        $this->assertSame($serviceOne->value, $serviceTwo->value);

        $container->extend('factory_service', $service);
        $serviceOne = $container['factory_service'];
        $this->assertInstanceOf('Tomahawk\DI\Test\Service', $serviceOne);
        $serviceTwo = $container['factory_service'];
        $this->assertInstanceOf('Tomahawk\DI\Test\Service', $serviceTwo);
        $this->assertNotSame($serviceOne, $serviceTwo);
        $this->assertNotSame($serviceOne->value, $serviceTwo->value);
    }

    public function testExtendDoesNotLeakWithFactories()
    {
        if (extension_loaded('pimple')) {
            $this->markTestSkipped('Pimple extension does not support this test');
        }
        $container = new Container();

        $container['foo'] = $container->factory(function () { return; });
        $container['foo'] = $container->extend('foo', function ($foo, $container) { return; });
        unset($container['foo']);

        $p = new \ReflectionProperty($container, 'values');
        $p->setAccessible(true);
        $this->assertEmpty($p->getValue($container));

        $p = new \ReflectionProperty($container, 'factories');
        $p->setAccessible(true);
        $this->assertCount(0, $p->getValue($container));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" is not defined.
     */
    public function testExtendValidatesKeyIsPresent()
    {
        $container = new Container();
        $container->extend('foo', function () {});
    }

    public function testKeys()
    {
        $container = new Container();
        $container['foo'] = 123;
        $container['bar'] = 123;

        $this->assertEquals(array('foo', 'bar'), $container->keys());
    }

    /** @test */
    public function settingAnInvokableObjectShouldTreatItAsFactory()
    {
        $container = new Container();
        $container['invokable'] = new Invokable();

        $this->assertInstanceOf('Tomahawk\DI\Test\Service', $container['invokable']);
    }

    /** @test */
    public function settingNonInvokableObjectShouldTreatItAsParameter()
    {
        $container = new Container();
        $container['non_invokable'] = new NonInvokable();

        $this->assertInstanceOf('Tomahawk\DI\Test\NonInvokable', $container['non_invokable']);
    }

    /**
     * @dataProvider badServiceDefinitionProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Service definition is not a Closure or invokable object.
     */
    public function testFactoryFailsForInvalidServiceDefinitions($service)
    {
        $container = new Container();
        $container->factory($service);
    }

    /**
     * @dataProvider badServiceDefinitionProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Callable is not a Closure or invokable object.
     */
    public function testProtectFailsForInvalidServiceDefinitions($service)
    {
        $container = new Container();
        $container->protect($service);
    }

    /**
     * @dataProvider badServiceDefinitionProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Identifier "foo" does not contain an object definition.
     */
    public function testExtendFailsForKeysNotContainingServiceDefinitions($service)
    {
        $container = new Container();
        $container['foo'] = $service;
        $container->extend('foo', function () {});
    }

    /**
     * @dataProvider badServiceDefinitionProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Extension service definition is not a Closure or invokable object.
     */
    public function testExtendFailsForInvalidServiceDefinitions($service)
    {
        $container = new Container();
        $container['foo'] = function () {};
        $container->extend('foo', $service);
    }

    /**
     * Provider for invalid service definitions
     */
    public function badServiceDefinitionProvider()
    {
        return array(
            array(123),
            array(new NonInvokable())
        );
    }

    /**
     * Provider for service definitions
     */
    public function serviceDefinitionProvider()
    {
        return array(
            array(function ($value) {
                $service = new Service();
                $service->value = $value;

                return $service;
            }),
            array(new Invokable())
        );
    }

    public function testDefiningNewServiceAfterFreeze()
    {
        $container = new Container();
        $container['foo'] = function () {
            return 'foo';
        };
        $foo = $container['foo'];

        $container['bar'] = function () {
            return 'bar';
        };
        $this->assertSame('bar', $container['bar']);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Cannot override frozen service "foo".
     */
    public function testOverridingServiceAfterFreeze()
    {
        $container = new Container();
        $container['foo'] = function () {
            return 'foo';
        };
        $foo = $container['foo'];

        $container['foo'] = function () {
            return 'bar';
        };
    }

    public function testRemovingServiceAfterFreeze()
    {
        $container = new Container();
        $container['foo'] = function () {
            return 'foo';
        };
        $foo = $container['foo'];

        unset($container['foo']);
        $container['foo'] = function () {
            return 'bar';
        };
        $this->assertSame('bar', $container['foo']);
    }

    public function testExtendingService()
    {
        $container = new Container();
        $container['foo'] = function () {
            return 'foo';
        };
        $container['foo'] = $container->extend('foo', function ($foo, $app) {
            return "$foo.bar";
        });
        $container['foo'] = $container->extend('foo', function ($foo, $app) {
            return "$foo.baz";
        });
        $this->assertSame('foo.bar.baz', $container['foo']);
    }

    public function testExtendingServiceAfterOtherServiceFreeze()
    {
        $container = new Container();
        $container['foo'] = function () {
            return 'foo';
        };
        $container['bar'] = function () {
            return 'bar';
        };
        $foo = $container['foo'];

        $container['bar'] = $container->extend('bar', function ($bar, $app) {
            return "$bar.baz";
        });
        $this->assertSame('bar.baz', $container['bar']);
    }
}
