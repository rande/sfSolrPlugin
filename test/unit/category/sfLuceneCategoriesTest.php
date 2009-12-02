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

$t = new limeade_test(20, limeade_output::get());

$lucene = sfLucene::getInstance('index', 'en', $app_configuration);

$writer = new sfLuceneStorageBlackhole('foo');

$t->diag('testing constructor and initialization');

try {
  new sfLuceneCategories('foo');
  $t->fail('__construct() must reject invalid search instances');
} catch (Exception $e) {
  $t->pass('__construct() must reject invalid search instances');
}

try {
  new sfLuceneCategories($lucene);
  $t->pass('__construct() must accept an instance of sfLucene');
} catch (Exception $e) {
  $t->fail('__construct() must accept an instance of sfLucene');

  $e->printStackTrace();
}

try {
  new sfLuceneCategories($lucene, 'dd');
  $t->fail('__construct() must reject invalid writers');
} catch (Exception $e) {
  $t->pass('__construct() must reject invalid writers');
}

try {
  $c = new sfLuceneCategories($lucene, $writer);
  $t->pass('__construct() must accept valid writers');
} catch (Exception $e) {
  $t->fail('__construct() must accept valid writers');
  $t->skip('the previous test must pass to continue');
  die();
}

$t->is($c->getAllCategories(), array(), '->getAllCategories() returns an empty array in the beginning');

$t->diag('testing ->getCategory()');

$category = $c->getCategory('foo');
$t->ok($category instanceof sfLuceneCategory, '->getCategory() returns an instance of sfLuceneCategory');
$t->is($category->getName(), 'foo', '->getCategory() returns a category with the correct name');
$t->is($category->getCount(), 0, '->getCategory() returns a category with a default score of 0');
$t->ok($category->getHolder() === $c, '->getCategory()->getHolder() returns the same instance as the holder');
$t->ok($category === $c->getCategory('foo'), '->getCategory() returns the same category each time, for a given name');

$t->diag('testing ->save()');

$category->add(10);
$t->ok($c->isModified(), 'modifying a category flags the holder for modification');

$c->save();
$t->is($writer->read(), '$categories = array();$categories[\'foo\'] = 10;', '->save() writes the changes to the writer');

$c->getCategory('bar')->add(2)->getHolder()->save();
$t->is($writer->read(), '$categories = array();$categories[\'foo\'] = 10;$categories[\'bar\'] = 2;', '->save() writes multiple changes to the writer');

$t->ok($c->isModified() == false, '->save() resets the modification flag');

$writer->write('foobarbaz');
$c->save();
$t->is($writer->read(), 'foobarbaz', '->save() does nothing if it is not modified');

$t->diag('testing ->load()');

$writer->write('$categories = array();$categories[\'baz\'] = 4;');

$t->is($c->load()->getCategory('baz')->getCount(), 4, '->load() reloads the categories list from the writer');
$t->is(count($c->getAllCategories()), 1, '->load() removes any old categories');

$writer->write('$foo = array();');
try {
  $c->load();
  $t->fail('->load() throws an exception with malformed data');
} catch (Exception $e) {
  $t->pass('->load() throws an exception with malformed data');
}

$t->diag('testing ->clear()');
$c->clear();

$t->ok($c->isModified(), '->clear() flags the holder for modication');
$t->is(count($c->getAllCategories()), 0, '->clear() removes all categories');
