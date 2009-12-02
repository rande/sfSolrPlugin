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

$t = new limeade_test(4, limeade_output::get());

try {
  $marker = new sfLuceneHighlighterMarkerSprint('[h]%s[/h]');
  $t->pass('__construct() accepts a pattern');
} catch (Exception $e) {
  $t->fail('__construct() accepts a pattern');
}

$t->is($marker->highlight('foobar'), '[h]foobar[/h]', '->highlight() highlights the string');

$t->isa_ok(sfLuceneHighlighterMarkerSprint::generate(array('[h]%s[/h]')), 'sfLuceneHighlighterMarkerHarness', '::generate() returns a highlighter marker harness');

$t->is(sfLuceneHighlighterMarkerSprint::generate(array('[h]%s[/h]'))->getHighlighter()->highlight('foobar'), '[h]foobar[/h]', '::generate() builds correct highlighters');