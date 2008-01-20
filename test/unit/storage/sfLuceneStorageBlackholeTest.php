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

$t = new limeade_test(6, limeade_output::get());
$limeade = new limeade_sf($t);
$app = $limeade->bootstrap();

try {
  $bh = new sfLuceneStorageBlackhole('foo');
  $t->pass('__construct() accepts a string');
} catch (Exception $e) {
  $t->fail('__construct() accepts a string');
  $t->skip('the previous test must pass to continue');
  die;
}

$t->ok($bh instanceof sfLuceneStorage, 'sfLuceneStorageBlackhole implements sfLuceneStorage interface');

$t->is($bh->read(), null, '->read() is null initially');

try {
  $bh->write('foobar');
  $t->pass('->write() can write data');
  $t->is($bh->read(), 'foobar', '->read() reads the data written by ->write()');
} catch (Exception $e) {
  $t->fail('->write() can write data');
  $t->skip('->read() reads the data written by ->write()');
}

$bh->delete();
$t->is($bh->read(), null, '->delete() causes ->read() to return null');