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

$t = new lime_test(3, new lime_output_color());

class Foo
{
  public $executed = false;

  public function execute()
  {
    $this->executed = true;
  }
}

$context = sfContext::getInstance();
$context->getResponse()->setContent('foobar 2357');

$filter = new sfLuceneRenderingFilter($context);
$chain = new Foo;

try {
  ob_start();
  $filter->execute($chain);
  $content = ob_get_clean();

  $t->pass('->execute() runs without an exception');
  $t->like($content, '/^foobar 2357.*/', '->execute() sends response content');
} catch (Exception $e) {
  ob_end_clean();

  $t->fail('->execute() runs without an exception');
  $t->skip('->execute() sends response content');
}

$t->ok($chain->executed, '->execute() runs ->execute() on the chain');