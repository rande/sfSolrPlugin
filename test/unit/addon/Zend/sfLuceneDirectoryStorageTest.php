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

$t = new limeade_test(7, limeade_output::get());
$limeade = new limeade_sf($t);
$app = $limeade->bootstrap();

$luceneade = new limeade_lucene($limeade);
$luceneade->configure()->clear_sandbox();

clearstatcache();

$d = new sfLuceneDirectoryStorage($luceneade->sandbox_dir . '/really/long/path/to/something');

clearstatcache();

$t->is(substr(sprintf('%o', fileperms($luceneade->sandbox_dir . '/really/long/path/to/something')), -4), '0777', '__construct() sets permission to 0777');

$file = $d->createFile('foo');
$t->isa_ok($file, 'sfLuceneFileStorage', '->createFile() returns an instance of sfLuceneFileStorage');
$t->is(substr(sprintf('%o', fileperms($luceneade->sandbox_dir . '/really/long/path/to/something/foo')), -4), '0777', '->createFile() sets permission to 0777');

$file = $d->createFile('foo');
$t->is(substr(sprintf('%o', fileperms($luceneade->sandbox_dir . '/really/long/path/to/something/foo')), -4), '0777', '->createFile() sets permission to 0777 if it\'s created again');

clearstatcache();

$t->is($d->getFileObject('foo'), $file, '->getFileObject() returns the same instance of the file handler');

$t->isa_ok($d->getFileObject('foo', false), 'sfLuceneFileStorage', '->getFileObject() returns an instance of sfLuceneFileStorage if told not to share');

touch($luceneade->sandbox_dir . '/really/long/path/to/something/bar');

$t->isa_ok($d->getFileObject('bar'), 'sfLuceneFileStorage', '->getFileObject() returns an instance of sfLuceneFileStorage if the file exists but not handled');