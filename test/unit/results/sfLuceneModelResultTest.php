<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * @package sfLucenePlugin
  * @subpackage Test
  * @author Carl Vondrick
  * @version SVN: $Id$
  */

require dirname(__FILE__) . '/../../bootstrap/unit.php';

$t = new limeade_test(12, limeade_output::get());
$limeade = new limeade_sf($t);
$app = $limeade->bootstrap();

$luceneade = new limeade_lucene($limeade);
$luceneade->configure()->clear_sandbox();

$lucene = sfLucene::getInstance('testLucene');

class MockResult extends Zend_Search_Lucene_Search_QueryHit
{
  public function __construct($a)
  {
  }

  public function getDocument()
  {
    return $this->doc;
  }
}

class MockDocument
{
  public function getFieldValue($field)
  {
    if (!isset($this->$field)) throw new Exception('You said to');

    return $this->$field;
  }
}

$mockresult = new MockResult($lucene->getLucene());
$mockresult->score = .425;
$mockresult->id = 1;
$doc = new MockDocument;
$doc->sfl_type = 'model';
$doc->sfl_model = 'FakeForum';
$doc->title = 'Registered title';
$doc->description = 'Registered <b>description</b>';
$doc->id = 42;
$mockresult->doc = $doc;

$t->diag('testing constructor');

try {
  $result = new sfLuceneModelResult($mockresult, $lucene);
  $t->pass('__construct() accepts a valid result and valid sfLucene instance');
} catch (Exception $e) {
  $t->fail('__construct() accepts a valid result and valid sfLucene instance');
}

$t->is($result->getInternalModel(), 'FakeForum', '->getInternalModel() returns the correct model');

$h = $lucene->getParameter('models')->get('FakeForum');

$t->diag('testing ->getInternalTitle()');

$t->is($result->getInternalTitle(), 'Registered title', '->getInternalTitle() returns the title registered in the document');

$h->remove('title');
$t->is($result->getInternalTitle(), 'Registered title', '->getInternalTitle() can guess the title');

$fields = clone $h->get('fields');
$h->get('fields')->clear();

try {
  $t->is($result->getInternalTitle(), 'No title available.', '->getInternalTitle() executes with no possible title');
} catch (Exception $e) {
  $t->fail('->getInternalTitle() executes with no possible title');
}

$h->set('fields', $fields);

$t->diag('testing ->getInternalUri()');
$t->is($result->getInternalUri(), 'forum/showForum?id=42', '->getInternalTitle() does simple substituion on routes');

$h->remove('route');

try {
  $result->getInternalUri();
  $t->fail('->getInternalUri() fails if no route is set');
} catch (Exception $e) {
  $t->pass('->getInternalUri() fails if no route is set');
}

$t->diag('testing ->getInternalPartial()');

$t->is($result->getInternalPartial(), 'forumResult', '->getInternalPartial() returns model overloaded partial');

$h->remove('partial');
$t->is($result->getInternalPartial(), 'sfLucene/modelResult', '->getInternalPartial() returns default partial if none given');

$t->diag('testing ->getInternalDescription()');

$t->is($result->getInternalDescription(), 'Registered description', '->getInternalDescription() returns the description registered in the document and strips HTML');

$h->remove('description');
$t->is($result->getInternalDescription(), 'Registered description', '->getInternalDescription() can guess the description');

$fields = clone $h->get('fields');
$h->get('fields')->clear();

try {
  $t->is($result->getInternalDescription(), 'No description available.', '->getInternalDescription() executes with no possible description');
} catch (Exception $e) {
  $t->fail('->getInternalDescription() executes with no possible description');
}