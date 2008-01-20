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

$t = new limeade_test(2, limeade_output::get());
$limeade = new limeade_sf($t);
$app = $limeade->bootstrap();

$given = '<p>This is part of a document, dedicated to foobar.</p><p>Look, a foobar</p>';
$expected = '<p>This is part of a document, dedicated to <h>foobar</h>.</p>
<p>Look, a <h>foobar</h></p>
';

$keyword = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<h>%s</h>'), 'foobar');

$highlighter = new sfLuceneHighlighterHTMLPart($given);
$highlighter->addKeywords(array($keyword));
$highlighter->highlight();

$t->is($highlighter->export(), $expected, '->highlight() highlights a part of the document and returns just that part');

$given = '<p>This is p&agrave;rt of a document, dedicated to foobar.</p>';
$expected = '<p>This is p&agrave;rt of a document, dedicated to <h>foobar</h>.</p>
';

$keyword = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<h>%s</h>'), 'foobar');

$highlighter = new sfLuceneHighlighterHTMLPart($given);
$highlighter->addKeywords(array($keyword));
$highlighter->highlight();

$t->is($highlighter->export(), $expected, '->highlight() handles entities correctly');