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

$t = new limeade_test(15, limeade_output::get());

$highlighter = new sfLuceneHighlighterMarkerDry;

try {
  $kw = new sfLuceneHighlighterKeywordNamedInsensitive($highlighter, 'FOOBAR');
  $t->pass('__construct() accepts a valid highlighter and valid name');
} catch (Exception $e) {
  $t->fail('__construct() accepts a valid highlighter and valid name');
}

$t->is($kw->getHighlighter(), $highlighter, '->getHighlighter() returns the correct highlighter');
$t->is($kw->getName(), 'FOOBAR', '->getName() returns the correct name');
$t->is($kw->getLength(), 6, '->getLength() returns the correct length');

$got = $kw->tokenize('Foobar is my favorite foobar, but it needs the bar to be foobar');
$expected = array(
  new sfLuceneHighlighterToken($kw, 'Foobar', 0, 6),
  new sfLuceneHighlighterToken($kw, 'foobar', 22, 28),
  new sfLuceneHighlighterToken($kw, 'foobar', 57, 63)
);

$t->is($got, $expected, '->tokenize() returns correct positions for case-insensitivity');
$t->is_deeply($kw->tokenize('nothing interesting here.  move along!'), array(), '->tokenize() returns nothing if it does not appear in the string');
$t->is($kw->tokenize('mr foobar, where are the foobars?'), array(new sfLuceneHighlighterToken($kw, 'foobar', 3, 9)), '->tokenize() only tokenizes exact matches');

$lighters = array(
  new sfLuceneHighlighterMarkerUppercase(),
  new sfLuceneHighlighterMarkerSprint('[h]%s[/h]'),
);

$harness = new sfLuceneHighlighterMarkerHarness($lighters);

$keywords = sfLuceneHighlighterKeywordNamedInsensitive::generate($harness, array('a', 'b', 'c'));

$t->is(count($keywords), 3, '::generate() returns the same number of initial keywords');
$t->ok($keywords[0]->getName() == 'a' && $keywords[1]->getName() == 'b' && $keywords[2]->getName() == 'c', '::generate() returns the keywords in the same order with the correct names');
$t->isa_ok($keywords[0], 'sfLuceneHighlighterKeywordNamedInsensitive', '::generate() returns instances of sfLuceneHighlighterKeywordNamedInsensitive');
$t->ok($keywords[0]->getHighlighter() === $lighters[0] && $keywords[1]->getHighlighter() == $lighters[1] && $keywords[2]->getHighlighter() == $lighters[0], '::generate() modulates on the same order as the harness');

$keywords = sfLuceneHighlighterKeywordNamedInsensitive::explode($harness, 'foobar António baz.....symf0ny');
$t->is(count($keywords), 4, '::explode() correctly splits the query into separate words');
$t->is($keywords[1]->getName(), 'António', '::explode() handles UTF8 strings correctly');
$t->is($keywords[2]->getName(), 'baz', '::explode() breaks correctly after a UTF8 string');
$t->is($keywords[3]->getName(), 'symf0ny', '::explode() breaks correctly after non-word but non-space character and handles numbers correctly');