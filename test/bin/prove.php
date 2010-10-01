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

$_test_dir = realpath(dirname(__FILE__). '/../../../..');

require dirname(__FILE__) . '/../bootstrap/unit.php';

$h = new lime_harness(new lime_output_color());
$h->base_dir = dirname(__FILE__).'/../unit';

// register unit tests
$finder = sfFinder::type('file')->ignore_version_control()->follow_link()->name('*Test.php');
$h->register($finder->in($h->base_dir));

$h->run();