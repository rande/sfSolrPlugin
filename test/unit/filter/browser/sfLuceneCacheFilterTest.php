<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
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

$t = new lime_test(2, new lime_output_color());

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
  $filter->execute($chain);
  $t->pass('->execute() runs without an exception');
} catch (Exception $e) {
  $t->fail('->execute() runs without an exception');
}

$t->ok($chain->executed, '->execute() runs ->execute() on the chain');