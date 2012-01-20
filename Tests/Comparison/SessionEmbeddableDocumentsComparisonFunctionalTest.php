<?php

namespace Ebutik\MongoSessionBundle\Tests\Comparison;

use Ebutik\MongoSessionBundle\Comparison\SessionEmbeddableDocumentsComparison;
use Ebutik\MongoSessionBundle\Document\EmbeddableSessionAttributeWrapper;

use Mockery as M;

class SessionEmbeddableDocumentsComparisonFunctionalTest extends \PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    $this->obj1 = M::mock('Ebutik\MongoSessionBundle\Interfaces\SessionEmbeddable');
    $this->obj2 = M::mock('Ebutik\MongoSessionBundle\Interfaces\SessionEmbeddable');
    $this->obj3 = M::mock('Ebutik\MongoSessionBundle\Interfaces\SessionEmbeddable');
    $this->obj4 = M::mock('Ebutik\MongoSessionBundle\Interfaces\SessionEmbeddable');
    $this->obj5 = M::mock('Ebutik\MongoSessionBundle\Interfaces\SessionEmbeddable');
    $this->obj6 = M::mock('Ebutik\MongoSessionBundle\Interfaces\SessionEmbeddable');
    $this->obj7 = M::mock('Ebutik\MongoSessionBundle\Interfaces\SessionEmbeddable');

    $this->wrapper1 = new EmbeddableSessionAttributeWrapper('obj1', $this->obj1);
    $this->wrapper2 = new EmbeddableSessionAttributeWrapper('obj2', $this->obj2);
    $this->wrapper3 = new EmbeddableSessionAttributeWrapper('obj3', $this->obj3);
    $this->wrapper4 = new EmbeddableSessionAttributeWrapper('obj4', $this->obj4);
    $this->wrapper5 = new EmbeddableSessionAttributeWrapper('obj5', $this->obj5);
    $this->wrapper6 = new EmbeddableSessionAttributeWrapper('obj6', $this->obj6);
    $this->wrapper7 = new EmbeddableSessionAttributeWrapper('obj7', $this->obj7);
  }

  public function testSameObjects()
  {
    $wrappers = array($this->wrapper1, $this->wrapper2, $this->wrapper3, $this->wrapper4, $this->wrapper5);
    $new_objs = array('obj1' => $this->obj1, 'obj2' => $this->obj2, 'obj3' => $this->obj3, 'obj4' => $this->obj4, 'obj5' => $this->obj5);

    $comparison = new SessionEmbeddableDocumentsComparison($wrappers, $new_objs);

    $this->assertEmpty($comparison->getRemovedWrappers());
    $this->assertEmpty($comparison->getAddedKeyDocumentArray());
    $this->assertEmpty($comparison->getKeyUpdateTranslationArray());
  }

  public function testAddedObjects()
  {
    $wrappers = array($this->wrapper1, $this->wrapper2, $this->wrapper3, $this->wrapper4, $this->wrapper5);
    $new_objs = array('obj1' => $this->obj1, 'obj2' => $this->obj2, 'obj3' => $this->obj3, 'obj4' => $this->obj4, 'obj5' => $this->obj5, 'obj6' => $this->obj6, 'obj7' => $this->obj7);

    $comparison = new SessionEmbeddableDocumentsComparison($wrappers, $new_objs);

    $this->assertEmpty($comparison->getRemovedWrappers());
    $this->assertEquals(array('obj6' => $this->obj6, 'obj7' => $this->obj7), $comparison->getAddedKeyDocumentArray());
    $this->assertEmpty($comparison->getKeyUpdateTranslationArray());
  }

  public function testRemovedObjects()
  {
    $wrappers = array($this->wrapper1, $this->wrapper2, $this->wrapper3, $this->wrapper4, $this->wrapper5);
    $new_objs = array('obj3' => $this->obj3, 'obj4' => $this->obj4, 'obj5' => $this->obj5);

    $comparison = new SessionEmbeddableDocumentsComparison($wrappers, $new_objs);

    $this->assertEquals(array($this->wrapper1, $this->wrapper2), $comparison->getRemovedWrappers());
    $this->assertEmpty($comparison->getAddedKeyDocumentArray());
    $this->assertEmpty($comparison->getKeyUpdateTranslationArray());
  }

  public function testAddedAndRemovedObjects()
  {
    $wrappers = array($this->wrapper1, $this->wrapper2, $this->wrapper3, $this->wrapper4, $this->wrapper5);
    $new_objs = array('obj3' => $this->obj3, 'obj4' => $this->obj4, 'obj5' => $this->obj5, 'obj6' => $this->obj6, 'obj7' => $this->obj7);

    $comparison = new SessionEmbeddableDocumentsComparison($wrappers, $new_objs);

    $this->assertEquals(array($this->wrapper1, $this->wrapper2), $comparison->getRemovedWrappers());
    $this->assertEquals(array('obj6' => $this->obj6, 'obj7' => $this->obj7), $comparison->getAddedKeyDocumentArray());
    $this->assertEmpty($comparison->getKeyUpdateTranslationArray());
  }

  public function testKeysUpdated()
  {
    $wrappers = array($this->wrapper1, $this->wrapper2, $this->wrapper3, $this->wrapper4, $this->wrapper5);
    $new_objs = array('obj1' => $this->obj1, 'objfoo' => $this->obj2, 'obj3' => $this->obj3, 'obj4' => $this->obj4, 'objbar' => $this->obj5);

    $comparison = new SessionEmbeddableDocumentsComparison($wrappers, $new_objs);

    $this->assertEmpty($comparison->getRemovedWrappers());
    $this->assertEmpty($comparison->getAddedKeyDocumentArray());
    $this->assertEquals(array('obj2' => 'objfoo', 'obj5' => 'objbar'), $comparison->getKeyUpdateTranslationArray());
  }

  public function testAddedAndRemovedObjectsKeysUpdated()
  {
    $wrappers = array($this->wrapper1, $this->wrapper2, $this->wrapper3, $this->wrapper4, $this->wrapper5);
    $new_objs = array('objfoo' => $this->obj3, 'obj4' => $this->obj4, 'objbar' => $this->obj5, 'obj6' => $this->obj6, 'obj7' => $this->obj7);

    $comparison = new SessionEmbeddableDocumentsComparison($wrappers, $new_objs);

    $this->assertEquals(array($this->wrapper1, $this->wrapper2), $comparison->getRemovedWrappers());
    $this->assertEquals(array('obj6' => $this->obj6, 'obj7' => $this->obj7), $comparison->getAddedKeyDocumentArray());
    $this->assertEquals(array('obj3' => 'objfoo', 'obj5' => 'objbar'), $comparison->getKeyUpdateTranslationArray());
  }
}