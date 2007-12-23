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

$t = new lime_test(7, new lime_output_color());

clearstatcache();

$d = new sfLuceneDirectoryStorage(SANDBOX_DIR . '/really/long/path/to/something');

clearstatcache();

$t->is(substr(sprintf('%o', fileperms(SANDBOX_DIR . '/really/long/path/to/something')), -4), '0777', '__construct() sets permission to 0777');

$file = $d->createFile('foo');
$t->isa_ok($file, 'sfLuceneFileStorage', '->createFile() returns an instance of sfLuceneFileStorage');
$t->is(substr(sprintf('%o', fileperms(SANDBOX_DIR . '/really/long/path/to/something/foo')), -4), '0777', '->createFile() sets permission to 0777');

$file = $d->createFile('foo');
$t->is(substr(sprintf('%o', fileperms(SANDBOX_DIR . '/really/long/path/to/something/foo')), -4), '0777', '->createFile() sets permission to 0777 if it\'s created again');

clearstatcache();

$t->is($d->getFileObject('foo'), $file, '->getFileObject() returns the same instance of the file handler');

$t->isa_ok($d->getFileObject('foo', false), 'sfLuceneFileStorage', '->getFileObject() returns an instance of sfLuceneFileStorage if told not to share');

touch(SANDBOX_DIR . '/really/long/path/to/something/bar');

$t->isa_ok($d->getFileObject('bar'), 'sfLuceneFileStorage', '->getFileObject() returns an instance of sfLuceneFileStorage if the file exists but not handled');