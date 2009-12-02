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

$t = new limeade_test(15, limeade_output::get());


$lucene = sfLucene::getInstance('index', 'en', $app_configuration);

$writer = new sfLuceneStorageBlackhole('foo');

$holder = new sfLuceneCategories($lucene, $writer);

$t->diag('testing __construct');
try {
  new sfLuceneCategory('foo', 'bar');
  $t->fail('__construct() must reject invalid holders');
} catch (Exception $e) {
  $t->pass('__construct() must reject invalid holders');
}

try {
  $c = new sfLuceneCategory($holder, 'bar', 5);
  $t->pass('__construct() must accept valid holders');
} catch (Exception $e) {
  $t->fail('__construct() must accept valid holders');
}

$t->diag('testing initialization parameters');
$t->is($c->getCount(), 5, '->getCount() returns default count');
$t->is($c->getName(), 'bar', '->getName() returns the correct name');
$t->ok($c->getHolder() === $holder, '->getHolder() returns the same holder');

$t->diag('testing ->add() and ->subtract()');
$t->is($c->add()->getCount(), 6, '->add() adds one to the count');
$t->is($c->add(5)->getCount(), 11, '->add() can add more than one to the count');
$t->is($c->subtract()->getCount(), 10, '->subtract() subtracts one from the count');
$t->is($c->subtract(3)->getCount(), 7, '->subtract() can subtract more than one from the count');
$t->is($c->setCount(0)->getCount(), 0, '->setCount() can explicitly change the count');

$t->ok($holder->isModified(), 'changing the count flags the holder for modication');

$t->diag('testing saving methods');
$t->ok(!$c->worthSaving(), '->worthSaving() returns false if the count is 0');
$c->setCount(8);
$t->ok($c->worthSaving(), '->worthSaving() returns true if the count is greater than 0');

$t->is($c->getPhp(), '$categories[\'bar\'] = 8;', '->getPhp() returns valid PHP to save');

$t->diag('testing magic methods');
$t->is($c->__toString(), 'bar', '__toString() returns the name');