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

$t = new limeade_test(4, limeade_output::get());

$xml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
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

$expected = '<?xml version="1.0"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>foobar</title>
  </head>
  <body>
    <p>I am prety <h>foobar</h></p>
    <script xml:space="preserve">
      foobar
    </script>
    <style type="text/stylesheet" xml:space="preserve">
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

$keyword = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<h>%s</h>'), 'foobar');

$highlighter = new sfLuceneHighlighterXHTML($xml);
$highlighter->addKeywords(array($keyword));
$highlighter->highlight();

$t->is($highlighter->export(), $expected, '->highlight() highlights only the HTML content and works with the namespace correctly');

// ********************************************
// ********************************************

$xml = '
<html>
  <head>
    <title>foobar</title>
  </head>
  <body>
    <p>I am prety foobar</p>
  </body>
</html>
';

$expected = '<?xml version="1.0"?>
<html>
  <head>
    <title>foobar</title>
  </head>
  <body>
    <p>I am prety <h>foobar</h></p>
  </body>
</html>
';

$keyword = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<h>%s</h>'), 'foobar');

$highlighter = new sfLuceneHighlighterXHTML($xml);
$highlighter->addKeywords(array($keyword));
$highlighter->highlight();

$t->is($highlighter->export(), $expected, '->highlight() stills works without a default namespace');

$xml = '<p>I am pretty foobar</p>';

$expected = '<?xml version="1.0"?>
<p>I am pretty foobar</p>
';

$keyword = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<h>%s</h>'), 'foobar');

$highlighter = new sfLuceneHighlighterXHTML($xml);
$highlighter->addKeywords(array($keyword));
$highlighter->highlight();

$t->is($highlighter->export(), $expected, '->highlight() does nothing if the document is invalid XHTML');

// ********************************************
// ********************************************

$xml = '
<html>
  <head>
    <title>foobar</title>
  </head>
  <body>
    <p>foobar - baz, I am prety</p>
  </body>
</html>
';

$expected = '<?xml version="1.0"?>
<html>
  <head>
    <title>foobar</title>
  </head>
  <body>
    <p><h>foobar</h> - <s>baz</s>, I am prety</p>
  </body>
</html>
';

$keyword = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<h>%s</h>'), 'foobar');
$keyword2 = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<s>%s</s>'), 'baz');

$highlighter = new sfLuceneHighlighterXHTML($xml);
$highlighter->addKeywords(array($keyword, $keyword2));
$highlighter->highlight();

$t->is($highlighter->export(), $expected, '->highlight() handles multiple keywords');