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

require_once dirname(__FILE__) . '/bootstrap/unit.php';

$h = new lime_harness(new lime_output_color());
$h->base_dir = realpath(dirname(__FILE__));

// unit tests
$h->register_glob($h->base_dir.'/unit/*Test.php');
$h->register_glob($h->base_dir.'/unit/*/*Test.php');

$c = new lime_coverage($h);
$c->extension = '.class.php';
$c->verbose = true;
$c->base_dir = realpath(dirname(__FILE__).'/../lib');

$finder = sfFinder::type('file')->name('*.php')->prune('vendor');
$c->register($finder->in($c->base_dir));
$c->run();
