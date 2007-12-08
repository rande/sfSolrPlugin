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

$t = new lime_test(10, new lime_output_color());

$file = SANDBOX_DIR . '/storage/long/folder/tree';

$t->ok(!file_exists($file), 'target file does not exist initially');

try {
  $bh = new sfLuceneStorageFilesystem($file);
  $t->pass('__construct() accepts a file');
} catch (Exception $e) {
  $t->fail('__construct() accepts a file');
  $t->skip('the previous test must pass to continue');
  die;
}

$t->ok($bh instanceof sfLuceneStorage, 'sfLuceneStorageFilesystem implements sfLuceneStorage interface');

$t->is($bh->read(), null, '->read() is null initially');

try {
  $bh->write('foobar');
  $t->pass('->write() can write data');
  $t->is($bh->read(), 'foobar', '->read() reads the data written by ->write()');
  $t->ok(file_exists($file), '->write() creates the file');
  $t->is(file_get_contents($file), 'foobar', '->write() writes the data to the file');
} catch (Exception $e) {
  $t->fail('->write() can write data');
  $t->skip('->read() reads the data written by ->write()');
}

$bh->delete();
$t->is($bh->read(), null, '->delete() causes ->read() to return null');
$t->ok(!file_exists($file), '->delete() deletes the file');