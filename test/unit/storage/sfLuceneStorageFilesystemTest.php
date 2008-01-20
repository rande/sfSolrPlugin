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

clearstatcache(); // for some reason this unit test will go crazy without this...

$t = new limeade_test(12, limeade_output::get());
$limeade = new limeade_sf($t);
$app = $limeade->bootstrap();

$luceneade = new limeade_lucene($limeade);
$luceneade->configure()->clear_sandbox();

$file = $luceneade->sandbox_dir . '/storage/long/folder/tree';

$t->ok(!file_exists($file), 'target file does not exist initially');

try {
  $bh = new sfLuceneStorageFilesystem($file);
  $t->pass('__construct() accepts a file');
} catch (Exception $e) {
  $t->fail('__construct() accepts a file');
  $t->skip('the previous test must pass to continue');
  die;
}

$t->instanceof_ok($bh, 'sfLuceneStorage', 'sfLuceneStorageFilesystem implements sfLuceneStorage interface');

$t->is($bh->read(), null, '->read() is null initially');

try {
  $bh->write('foobar');
  $t->pass('->write() can write data');
  $t->is($bh->read(), 'foobar', '->read() reads the data written by ->write()');
} catch (Exception $e) {
  $t->fail('->write() can write data');
  $t->skip('->read() reads the data written by ->write()');
}

$t->ok(file_exists($file), '->write() creates the file');
$t->is(file_get_contents($file), 'foobar', '->write() writes the data to the file');

try {
  $ex = $t->no_exception('__construct() functions if the directory tree already exists');
  $bh2 = new sfLuceneStorageFileSystem($luceneade->sandbox_dir . '/storage/long/folder/flower');
  $ex->no();
} catch (Exception $e) {
  $ex->caught($e);
}

try {
  $ex = $t->no_exception('->write() functions if the directory tree already exists');
  $bh2->write('foobar');
  $ex->no();
} catch (Exception $e) {
  $ex->caught($e);
}

$bh->delete();
$t->is($bh->read(), null, '->delete() causes ->read() to return null');
$t->ok(!file_exists($file), '->delete() deletes the file');


