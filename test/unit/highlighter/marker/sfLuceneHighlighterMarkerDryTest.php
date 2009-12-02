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

$t = new limeade_test(3, limeade_output::get());

$marker = new sfLuceneHighlighterMarkerDry;

$t->is($marker->highlight('foobar'), 'foobar', '->highlight() does nothing');

$t->isa_ok(sfLuceneHighlighterMarkerDry::generate(), 'sfLuceneHighlighterMarkerHarness', '::generate() returns a highlighter marker harness');

$t->is(sfLuceneHighlighterMarkerDry::generate()->getHighlighter()->highlight('foobar'), 'foobar', '::generate() builds correct highlighters');