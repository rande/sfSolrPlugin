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

require dirname(__FILE__) . '/../../bootstrap/unit.php';

$t = new lime_test(1, new lime_output_color());

try {
  $d = new sfLuceneDoctrineIndexer('a', 'b');
  $t->fail('sfLuceneDoctrineIndexer fails no matter what (because it\'s not implemented yet!');
} catch (Exception $e) {
  $t->pass('sfLuceneDoctrineIndexer fails no matter what (because it\'s not implemented yet!');
}