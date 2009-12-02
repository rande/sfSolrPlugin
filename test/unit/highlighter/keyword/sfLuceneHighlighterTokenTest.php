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

$t = new limeade_test(8, limeade_output::get());

$kw = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerDry, 'foo');

try {
  $token = new sfLuceneHighlighterToken($kw, 'foo', 10, 15);
  $t->pass('__construct() accepts a valid text and positions');
} catch (Exception $e) {
  $t->fail('__construct() accepts a valid text and positions');
}

try {
  new sfLuceneHighlighterToken($kw, 'foo', 20, 10);
  $t->fail('__construct() rejects end positions that are less than the start position');
} catch (Exception $e) {
  $t->pass('__construct() rejects end positions that are less than the start position');
}

$t->is($token->getKeyword(), $kw, '->getKeyword() returns the keyword');
$t->is($token->getText(), 'foo', '->getText() returns the token text');
$t->is($token->getStart(), 10, '->getStart() returns the start position');
$t->is($token->getEnd(), 15, '->getEnd() returns the end position');
$t->is($token->getLength(), 5, '->getLength() returns the length');

$tokens = array(
  new sfLuceneHighlighterToken($kw, 'foo', 0, 3),
  new sfLuceneHighlighterToken($kw, 'foo', 50, 53),
  new sfLuceneHighlighterToken($kw, 'foo', 10, 13),
  new sfLuceneHighlighterToken($kw, 'foo', 10, 13),
  new sfLuceneHighlighterToken($kw, 'foo', 25, 28),
);

$expected = array(
  new sfLuceneHighlighterToken($kw, 'foo', 50, 53),
  new sfLuceneHighlighterToken($kw, 'foo', 25, 28),
  new sfLuceneHighlighterToken($kw, 'foo', 10, 13),
  new sfLuceneHighlighterToken($kw, 'foo', 10, 13),
  new sfLuceneHighlighterToken($kw, 'foo', 0, 3),
);

usort($tokens, array('sfLuceneHighlighterToken', 'prepareForHighlighting'));

$t->is($tokens, $expected, '::prepareForHighlighting() sorts in reverse order by starting position');