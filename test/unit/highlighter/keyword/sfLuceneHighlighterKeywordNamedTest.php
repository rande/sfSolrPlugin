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

$t = new limeade_test(18, limeade_output::get());


$highlighter = new sfLuceneHighlighterMarkerDry;

try {
  $kw = new sfLuceneHighlighterKeywordNamed($highlighter, 'foobar');
  $t->pass('__construct() accepts a valid highlighter and valid name');
} catch (Exception $e) {
  $t->fail('__construct() accepts a valid highlighter and valid name');
}

$t->is($kw->getHighlighter(), $highlighter, '->getHighlighter() returns the correct highlighter');
$t->is($kw->getName(), 'foobar', '->getName() returns the correct name');
$t->is($kw->getLength(), 6, '->getLength() returns the correct length');

$got = $kw->tokenize('Foobar is my favorite foobar, but it needs the bar to be foobar');
$expected = array(
  new sfLuceneHighlighterToken($kw, 'foobar', 22, 28),
  new sfLuceneHighlighterToken($kw, 'foobar', 57, 63)
);

$t->diag('testing ->tokenize()');

$t->is($got, $expected, '->tokenize() returns correct positions for case-sensitivity');
$t->is($kw->tokenize('nothing interesting here.  move along!'), array(), '->tokenize() returns nothing if it does not appear in the string');
$t->ok($kw->tokenize('mr foobar, where are the foobars?') == array(new sfLuceneHighlighterToken($kw, 'foobar', 3, 9)), '->tokenize() only tokenizes exact matches');
$t->ok($kw->tokenize('foobar where art thou?') == array(new sfLuceneHighlighterToken($kw, 'foobar', 0, 6)), '->tokenize() can tokenize tokens in the very beginning');
$t->is($kw->tokenize('to be or not to be, that is the foobar'), array(new sfLuceneHighlighterToken($kw, 'foobar', 32, 38)), '->tokenize() can tokenize tokens in the very end');

$t->is($kw->tokenize('foobar'), array(new sfLuceneHighlighterToken($kw, 'foobar', 0, 6)), '->tokenize() can tokenize tokens in the very end and very beginning');
$t->is($kw->tokenize("\nfoobar\n"), array(new sfLuceneHighlighterToken($kw, 'foobar', 1, 7)), '->tokenize() can tokenize tokens with line returns around it');

$t->diag('testing ::generate()');

$lighters = array(
  new sfLuceneHighlighterMarkerUppercase(),
  new sfLuceneHighlighterMarkerSprint('[h]%s[/h]'),
);

$harness = new sfLuceneHighlighterMarkerHarness($lighters);

$keywords = sfLuceneHighlighterKeywordNamed::generate($harness, array('a', 'b', 'c'));

$t->is(count($keywords), 3, '::generate() returns the same number of initial keywords');
$t->ok($keywords[0]->getName() == 'a' && $keywords[1]->getName() == 'b' && $keywords[2]->getName() == 'c', '::generate() returns the keywords in the same order with the correct names');

$t->ok($keywords[0]->getHighlighter() === $lighters[0] && $keywords[1]->getHighlighter() == $lighters[1] && $keywords[2]->getHighlighter() == $lighters[0], '::generate() modulates on the same order as the harness');

$t->diag('testing ::explode()');

$keywords = sfLuceneHighlighterKeywordNamed::explode($harness, 'foobar António baz.....symf0ny');
$t->is(count($keywords), 4, '::explode() correctly splits the query into separate words');
$t->is($keywords[1]->getName(), 'António', '::explode() handles UTF8 strings correctly');
$t->is($keywords[2]->getName(), 'baz', '::explode() breaks correctly after a UTF8 string');
$t->is($keywords[3]->getName(), 'symf0ny', '::explode() breaks correctly after non-word but non-space character and handles numbers correctly');

