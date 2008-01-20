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

require dirname(__FILE__) . '/../../../bootstrap/unit.php';

$t = new limeade_test(2, limeade_output::get());
$limeade = new limeade_sf($t);
$app = $limeade->bootstrap();

class Foo
{
  public $executed = false;

  public function execute()
  {
    $this->executed = true;
  }
}

$filter = new sfLuceneCacheFilter(sfContext::getInstance());
$chain = new Foo;

try {
  $ex = $t->no_exception('->execute() runs without an exception');
  $filter->execute($chain);
  $ex->no();
} catch (Exception $e) {
  $ex->caught($e);
}

$t->ok($chain->executed, '->execute() runs ->execute() on the chain');