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

$xml = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>foobar</title>
  </head>
  <body>
    <p>I am prety foobar</p>
    <script>
      foobar
    </script>
    <style type="text/stylesheet">
      foobar
    </style>
    <textarea>
      foobar
    </textarea>
    <!-- foobar -->
    <strong class="foobar">bar</strong>
  </body>
</html>
';

$expected = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>foobar</title></head><body><p>I am prety <h>foobar</h></p><script>
      foobar
    </script><style type="text/stylesheet">
      foobar
    </style><textarea>
      foobar
    </textarea><!-- foobar --><strong class="foobar">bar</strong></body></html>
';

$keyword = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<h>%s</h>'), 'foobar');

$highlighter = new sfLuceneHighlighterHTML($xml);
$highlighter->addKeywords(array($keyword));
$highlighter->highlight();

$t->is($highlighter->export(), $expected, '->highlight() highlights only the HTML content and works with the namespace correctly');

// ********************************************
// ********************************************

$xml = '<p>I am pretty foobar</p>';

$expected = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html><body><p>I am pretty <h>foobar</h></p></body></html>
';

$keyword = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<h>%s</h>'), 'foobar');

$highlighter = new sfLuceneHighlighterHTML($xml);
$highlighter->addKeywords(array($keyword));
$highlighter->highlight();

$t->is($highlighter->export(), $expected, '->highlight() transforms and highlights the document if it is invalid HTML');