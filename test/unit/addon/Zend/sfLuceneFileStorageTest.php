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

$t = new limeade_test(1, limeade_output::get());
$limeade = new limeade_sf($t);
$app = $limeade->bootstrap();

$luceneade = new limeade_lucene($limeade);
$luceneade->configure()->clear_sandbox();

clearstatcache();

touch($luceneade->sandbox_dir . '/foo');
chmod($luceneade->sandbox_dir . '/foo', 0666);

clearstatcache();

new sfLuceneFileStorage($luceneade->sandbox_dir . '/foo');

clearstatcache();

$t->is(substr(sprintf('%o', fileperms($luceneade->sandbox_dir . '/foo')), -4), '0777', '__construct() sets permission to 0777');