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

require dirname(__FILE__) . '/../../bootstrap/unit.php';

$t = new limeade_test(8, limeade_output::get());

$keywords = array(new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerUppercase, 'foobar'), new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('"%s"'), 'foobarbaz'));

$highlighter = new sfLuceneHighlighterString('Once foo married bar, they become a foobar, conquering the world.');
$highlighter->addKeywords($keywords);
$highlighter->highlight();

$t->is($highlighter->export(), 'Once foo married bar, they become a FOOBAR, conquering the world.', '->highlight() correctly highlights the string');
$t->is($highlighter->getKeywords(), $keywords, '->getKeywords() returns the array of keywords');

$highlighter = new sfLuceneHighlighterstring('Please wait for foobar to turn not into foobar, but only a foobarbaz with is foobar but just foobarbaz');
$highlighter->addKeywords($keywords);
$highlighter->highlight();

$t->is($highlighter->export(), 'Please wait for FOOBAR to turn not into FOOBAR, but only a "foobarbaz" with is FOOBAR but just "foobarbaz"', '->highlight() handles changing data length');

$keyword = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<h>%s</h>'), 'foobar');
$keyword2 = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<s>%s</s>'), 'baz');

$highlighter = new sfLuceneHighlighterString('i am foobar and my friend is baz');
$highlighter->addKeywords(array($keyword, $keyword2));
$highlighter->highlight();

$t->is($highlighter->export(), 'i am <h>foobar</h> and my friend is <s>baz</s>', '->highlight() handles multiple keywords');

$highlighter = new sfLuceneHighlighterString('there is <em>some</em> <strong>html</strong> here');
$t->is($highlighter->strip()->export(), 'there is some html here', '->strip() strips out the HTML');

$keywords = array(new sfLuceneHighlighterKeywordNamedInsensitive(new sfLuceneHighlighterMarkerDry, 'lorem'), new sfLuceneHighlighterKeywordNamedInsensitive(new sfLuceneHighlighterMarkerDry, 'dictum'));

$highlighter = new sfLuceneHighlighterString('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Cras rhoncus fermentum diam. Mauris lobortis. Integer eros. Nulla facilisi. Nulla ultrices, massa eget vehicula tincidunt, dui lorem dictum arcu, et molestie risus sem non odio. Quisque venenatis odio nec orci. Aenean diam nulla, auctor ac, molestie et, venenatis ut, libero. Sed tellus risus, adipiscing ut, sagittis at, feugiat eget, sem. Nullam metus risus, dignissim ac, pellentesque a, euismod in, turpis. Donec auctor elit nec sem.');
$t->is($highlighter->addKeywords($keywords)->crop(200)->export(), 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Cras rhoncus fermentum diam. Mauris lobortis. Integer eros. Nulla facilisi. Nulla ultrices, massa eget vehicula tincidunt, dui lorem dictum ar...', '->crop() zooms in on the most concentrated part of the string in the beginning');

$highlighter = new sfLuceneHighlighterString('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Cras rhoncus fermentum diam. Mauris lobortis. Integer eros. Nulla facilisi. Nulla ultrices, massa eget vehicula tincidunt, dui lorem dictum arcu, et molestie risus sem non odio. Quisque venenatis odio nec orci. Aenean diam nulla, auctor ac, molestie et, venenatis ut, libero. Sed tellus risus, adipiscing ut, sagittis at, feugiat eget, sem. Nullam metus risus, dignissim ac, pellentesque a, euismod in, turpis. Donec auctor elit nec sem.');
$t->is($highlighter->addKeywords($keywords)->crop(50)->export(), '...ehicula tincidunt, dui lorem dictum arcu, et moles...', '->crop() zooms in on the most concentrated part of the string in the middle');
$highlighter = new sfLuceneHighlighterString('Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Cras rhoncus fermentum diam. Mauris lobortis. Integer eros. Nulla facilisi. Nulla ultrices, massa eget vehicula tincidunt, dui lorem dictum arcu, et molestie risus sem non odio. Quisque venenatis odio nec orci. Aenean diam nulla, auctor ac, molestie et, venenatis ut, libero. Sed tellus risus, adipiscing ut, sagittis at, feugiat eget, sem. Nullam metus risus, dignissim ac, pellentesque a, euismod in, turpis. Donec auctor elit nec sem.  Lorem dictum lorem dictum lorem dictum');
$t->is($highlighter->addKeywords($keywords)->crop(100)->export(), '...euismod in, turpis. Donec auctor elit nec sem.  Lorem dictum lorem dictum lorem dictum', '->crop() zooms in on the most concentrated part of the string in the end');