<?php

use Tomahawk\Http\Response\Cookies;

use Symfony\Component\HttpFoundation\Request;

class CookiesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    protected $request;

    public function setUp()
    {
        $this->request = Request::createFromGlobals();
        parent::setUp();
    }

    public function testSet()
    {
        $cookies = new Cookies($this->request, array());

        $cookies->set('name', 'Tom');

        $this->assertCount(1, $cookies->getQueued());
        $this->assertFalse($cookies->has('name'));
    }

    public function testHas()
    {
        $request = new Request(array(), array(), array(), array('name' => 'Tom'));
        $cookies = new Cookies($request, array());

        $this->assertTrue($cookies->has('name'));
        $this->assertEquals('Tom', $cookies->get('name'));
    }

    public function testHasNotExists()
    {
        $request = new Request();
        $cookies = new Cookies($request, array());

        $this->assertFalse($cookies->has('name'));
        $this->assertEquals(null, $cookies->get('name'));
        $this->assertEquals('default', $cookies->get('name', 'default'));
    }

}