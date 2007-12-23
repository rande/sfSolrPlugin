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

$t = new lime_test(1, new lime_output_color());

clearstatcache();

touch(SANDBOX_DIR . '/foo');
chmod(SANDBOX_DIR . '/foo', 0666);

clearstatcache();

new sfLuceneFileStorage(SANDBOX_DIR . '/foo');

clearstatcache();

$t->is(substr(sprintf('%o', fileperms(SANDBOX_DIR . '/foo')), -4), '0777', '__construct() sets permission to 0777');