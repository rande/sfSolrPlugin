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

$t = new limeade_test(6, limeade_output::get());

$lighters = array(
  new sfLuceneHighlighterMarkerDry(),
  new sfLuceneHighlighterMarkerUppercase(),
  new sfLuceneHighlighterMarkerSprint('[h]%s[/h]'),
);

try {
  $harness = new sfLuceneHighlighterMarkerHarness($lighters);
  $t->pass('__construct() accepts an array of highlighters');
} catch (Exception $e) {
  $t->fail('__construct() accepts an array of highlighters');
}

try {
  new sfLuceneHighlighterMarkerHarness(array());
  $t->fail('__construct() rejects an empty array of highlighters');
} catch (Exception $e) {
  $t->pass('__construct() rejects an empty array of highlighters');
}

$t->is($harness->getHighlighter(), $lighters[0], '->getHighlighter() returns the first highlighter initially');
$t->is($harness->getHighlighter(), $lighters[1], '->getHighlighter() increments the internal pointer');
$t->is($harness->getHighlighter(), $lighters[2], '->getHighlighter() reaches the end of the array');
$t->is($harness->getHighlighter(), $lighters[0], '->getHighlighter() returns to the first highlighter at the end');