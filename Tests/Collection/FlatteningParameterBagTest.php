<?php

namespace Ebutik\MongoSessionBundle\Test\Collection;

use Ebutik\MongoSessionBundle\Collection\FlatteningParameterBag;

use Mockery as M;

/**
* 
*/
class FlatteningParameterBagTest extends \PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    $this->bag = new FlatteningParameterBagMockProxy(M::mock());
  }

  public function testSetFlat()
  {
    $this->bag->mock->shouldReceive(array('_read' => array()));
    $this->bag->mock->shouldReceive('_write')->with(array('foo' => 'bar'))->once();

    $this->bag->set('foo', 'bar');
  }

  public function testSetDeep()
  {
    $this->bag->mock->shouldReceive(array('_read' => array()));
    $this->bag->mock->shouldReceive('_write')->with(array('foo/bar' => 'baz'))->once();

    $this->bag->set('foo', array('bar' => 'baz'));
  }

  public function testSetToReplace()
  {
    $this->bag->mock->shouldReceive(array('_read' => array('foo/bar' => 'baz')));
    $this->bag->mock->shouldReceive('_write')->with(array('foo/quux' => 'zoinks'))->once();

    $this->bag->set('foo', array('quux' => 'zoinks'));
  }

  public function testSetWithSlash()
  {
    $this->bag->mock->shouldReceive(array('_read' => array()));
    $this->bag->mock->shouldReceive('_write')->with(array('foo%>baz' => 'bar'))->once();

    $this->bag->set('foo/baz', 'bar');
  }

  public function testGetFlat()
  {
    $this->bag->mock->shouldReceive(array('_read' => array('foo' => 'bar')));

    $this->assertEquals('bar', $this->bag->get('foo'));
  }

  public function testGetDeep()
  {
    $this->bag->mock->shouldReceive(array('_read' => array('foo/bar' => 'baz')));

    $this->assertEquals(array('bar' => 'baz'), $this->bag->get('foo'));
  }

  public function testGetDefault()
  {
    $this->bag->mock->shouldReceive(array('_read' => array()));

    $this->assertEquals('bar', $this->bag->get('foo', 'bar'));
  }

  public function testGetWithSlash()
  {
    $this->bag->mock->shouldReceive(array('_read' => array('foo%>baz' => 'bar')));

    $this->assertEquals('bar', $this->bag->get('foo/baz'));
  }

  public function testGetAll()
  {
    $this->bag->mock->shouldReceive(array('_read' => array('foo/bar' => 'baz', 'foo/baz' => 'bar', 'quux' => 'zoinks')));

    $this->assertEquals(array('foo' => array('baz' => 'bar', 'bar' => 'baz'), 'quux' => 'zoinks'), $this->bag->all());
  }

  public function testGetKeys()
  {
    $this->bag->mock->shouldReceive(array('_read' => array('foo/bar' => 'baz', 'foo/baz' => 'bar', 'quux' => 'zoinks')));

    $this->assertEquals(array('foo', 'quux'), $this->bag->keys());
  }

  public function testAdd()
  {
    $this->bag->mock->shouldReceive(array('_read' => array('baz/bar' => 'baz')));
    $this->bag->mock->shouldReceive('_write')->with(array('baz/bar' => 'baz', 'quux' => 'zoinks', 'foo/bar' => 'baz'))->once();

    $this->bag->add(array('quux' => 'zoinks', 'foo' => array('bar' => 'baz')));
  }

  public function testReplace()
  {
    $this->bag->mock->shouldReceive('_write')->with(array());
    $this->bag->mock->shouldReceive(array('_read' => array()));
    $this->bag->mock->shouldReceive('_write')->with(array('quux' => 'zoinks', 'foo/bar' => 'baz'))->once();

    $this->bag->replace(array('quux' => 'zoinks', 'foo' => array('bar' => 'baz')));
  }

  public function testHasFlat()
  {
    $this->bag->mock->shouldReceive(array('_read' => array('baz' => 'bar')));

    $this->assertTrue($this->bag->has('baz'));
  }

  public function testHasDeep()
  {
    $this->bag->mock->shouldReceive(array('_read' => array('baz/foo' => 'bar')));

    $this->assertTrue($this->bag->has('baz'));
  }

  public function testHasWithSlash()
  {
    $this->bag->mock->shouldReceive(array('_read' => array('foo%>baz' => 'bar')));

    $this->assertTrue($this->bag->has('foo/baz'));
  }

  public function testHasnt()
  {
    $this->bag->mock->shouldReceive(array('_read' => array('baz/foo' => 'bar')));

    $this->assertFalse($this->bag->has('foo'));
  }

  public function testRemove()
  {
    $this->bag->mock->shouldReceive(array('_read' => array('foo/bar' => 'baz', 'foo/baz' => 'bar', 'quux' => 'zoinks')));
    $this->bag->mock->shouldReceive('_write')->with(array('quux' => 'zoinks'))->once();

    $this->bag->remove('foo');
  }

  public function tearDown()
  {
    M::close();
  }
}

/**
* 
*/
class FlatteningParameterBagMockProxy extends FlatteningParameterBag
{
  public $mock;

  public function __construct($mock)
  {
    $this->mock = $mock;
  }

  protected function _read()
  {
    return $this->mock->_read();
  }

  protected function _write(array $data)
  {
    $this->mock->_write($data);
  }
}