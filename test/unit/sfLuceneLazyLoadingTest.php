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

require dirname(__FILE__) . '/../bootstrap/unit.php';

$t = new limeade_test(2, limeade_output::get());
$limeade = new limeade_sf($t);
$limeade->bootstrap();

$luceneade = new limeade_lucene($limeade);
$luceneade->configure()->clear_sandbox()->load_models();

$forum = new FakeForum;
$t->not_like_included('#/Zend/Search/Lucene/#', 'Zend libraries were not loaded when just reading from a model');

$forum->setTitle('test');
$forum->saveIndex();

$t->like_included('#/Zend/Search/Lucene/#', 'Zend libraries were loaded when writing to the index');

$forum->deleteIndex();