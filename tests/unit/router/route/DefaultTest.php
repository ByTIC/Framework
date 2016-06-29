<?php

/**
 * Test class for Nip_Route_Abstract.
 * Generated by PHPUnit on 2010-11-17 at 15:16:44.
 */
class Nip_Route_DefaultTest extends  \Codeception\TestCase\Test
{

    /**
     * @var Nip_Route_Default
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
		$this->object = new Nip_Route_Default();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

//    public function testAssemble()
//    {
//        $params = array(
//            'controller' => 'lorem',
//            'action' => 'ipsum',
//            'company' => 'dolo&rem',
//        );
//		$this->assertEquals('lorem/ipsum?company=dolo%26rem', $this->object->assemble($params));
//
//        $this->object->setMap('admin/:controller/:action');
//		$this->assertEquals('admin/lorem/ipsum?company=dolo%26rem', $this->object->assemble($params));
//
//        unset ($params['action']);
//		$this->assertEquals('admin/lorem/?company=dolo%26rem', $this->object->assemble($params));
//    }
//
//    public function testMatch()
//    {
//		$this->assertFalse($this->object->match('shop/category_cast/asdasd'));
//		$this->assertTrue($this->object->match('shop/category_cast/'));
//
//		$this->assertTrue($this->object->match('shop/cart'));
//		$this->assertEquals(array('controller' => 'shop', 'action' => 'cart'), $this->object->getParams());
//
//		$this->assertTrue($this->object->match('shop/'));
//		$this->assertEquals(array('controller' => 'shop', 'action' => ''), $this->object->getParams());
//
//		$this->assertTrue($this->object->match('shop'));
//		$this->assertEquals(array('controller' => 'shop', 'action' => ''), $this->object->getParams());
//    }
//
//    public function testMatchCustom()
//    {
//        $this->object->setMap('admin/:controller/:action');
//        
//		$this->assertFalse($this->object->match('shop/category_cast/asdasd'));
//		$this->assertFalse($this->object->match('shop/category_cast/'));
//
//		$this->assertFalse($this->object->match('admin/test/asd/category_cast/'));
//
//		$this->assertTrue($this->object->match('admin/shop/cart'));
//		$this->assertEquals(array('controller' => 'shop', 'action' => 'cart'), $this->object->getParams());
//
//		$this->assertTrue($this->object->match('admin/shop/'));
//		$this->assertEquals(array('controller' => 'shop', 'action' => ''), $this->object->getParams());
//
//		$this->assertTrue($this->object->match('admin/shop'));
//		$this->assertEquals(array('controller' => 'shop', 'action' => ''), $this->object->getParams());
//    }


}