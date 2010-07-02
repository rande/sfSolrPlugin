<?php

/**
 * Subclass for representing a row from the 'fake_forum' table.
 *
 *
 *
 * @package plugins.sfLucenePlugin.test.bin.model
 */
class FakeForum extends BaseFakeForum
{
  public $shouldIndex = true;

  public function isIndexable()
  {
    return $this->shouldIndex;
  }

  public function getNonScalar()
  {
    return range(1, 100);
  }

  public function getStringableObject()
  {
    return new Stringable;
  }

  public function getZendDocument()
  {
    $doc = new Zend_Search_Lucene_Document();
    $doc->addField(Zend_Search_Lucene_Field::Keyword('callback', 'foo'));

    return $doc;
  }
}

class Stringable
{
  public function __toString()
  {
    return 'Strings!';
  }
}

sfLucenePropelBehavior::getInitializer()->setup('FakeForum');