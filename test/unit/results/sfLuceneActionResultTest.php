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

$t = new limeade_test(1, limeade_output::get());
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
}

$doc = new MockResult('a');

try {
  new sfLuceneActionResult($doc, $lucene);
  $t->pass('__construct() can be called');
} catch (Exception $e) {
  $t->fail('__construct() can be called');
}