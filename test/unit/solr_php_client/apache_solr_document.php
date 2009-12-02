<?php

require dirname(__FILE__) . '/../../bootstrap/unit.php';

$t = new limeade_test(2, limeade_output::get());

$t->diag('document format a document');
$document = new Apache_Solr_Document();
$document->setBoost(10);
$document->setField('sfl_guid', 'GUID_1234');
$document->setField('name', 'Thomas Rabaix', 1);
$document->setMultiValue('skills', 'php');
$document->setMultiValue('skills', 'symfony');
$document->addField('skills', 'objective-c');

$expected = array (
  'name' => 'skills',
  'value' =>
  array (
    0 => 'php',
    1 => 'symfony',
    2 => 'objective-c'
  ),
  'boost' => false,
);

$t->cmp_ok($document->getField('skills'), '==', $expected, '::getField test multivalue setter');

$expected = array (
  'name' => 'name',
  'value' => 'Thomas Rabaix',
  'boost' => 1,
);
$t->cmp_ok($document->getField('name'), '==', $expected, '::getField test setter');


