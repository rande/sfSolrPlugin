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

$xml = '<?xml version="1.0"?>
<root>
  <!-- foobar -->
  <grandpa>
    <dad>
      <foobar>Bazfoobar!</foobar>
      <child>Baz</child>
      <child>foobar</child>
      <baz/>
    </dad>
    Check out my foobar.  It\'s pretty cool.
  </grandpa>
</root>
';

$expected = '<?xml version="1.0"?>
<root>
  <!-- foobar -->
  <grandpa>
    <dad>
      <foobar>Bazfoobar!</foobar>
      <child>Baz</child>
      <child><h>foobar</h></child>
      <baz/>
    </dad>
    Check out my <h>foobar</h>.  It\'s pretty cool.
  </grandpa>
</root>
';

$keyword = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<h>%s</h>'), 'foobar');

$highlighter = new sfLuceneHighlighterXML($xml);
$highlighter->addKeywords(array($keyword));
$highlighter->highlight();

$t->is($highlighter->getKeywords(), array($keyword), '->getKeywords() returns the array of keywords');

$t->is($highlighter->export(), $expected, '->highlight() correctly traverses the DOM tree');

$xml = '<?xml version="1.0"?>
<root>
  so foobar foobar foobar be
</root>
';

$expected = '<?xml version="1.0"?>
<root>
  so <h>foobar</h> <h>foobar</h> <h>foobar</h> be
</root>
';

$keyword = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<h>%s</h>'), 'foobar');

$highlighter = new sfLuceneHighlighterXML($xml);
$highlighter->addKeywords(array($keyword));
$highlighter->highlight();

$t->is($highlighter->export(), $expected, '->highlight() handles changing data length');

$xml = '<?xml version="1.0"?>
<root>
  <child>hello baz</child>
  <child>i am foobar</child>
  <child>baz, meet foobar</child>
</root>
';

$expected = '<?xml version="1.0"?>
<root>
  <child>hello <s>baz</s></child>
  <child>i am <h>foobar</h></child>
  <child><s>baz</s>, meet <h>foobar</h></child>
</root>
';

$keyword = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<h>%s</h>'), 'foobar');
$keyword2 = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<s>%s</s>'), 'baz');

$dtd = $_SERVER['SYMFONY'].'/test/w3c/TR/xhtml1/DTD/xhtml1-transitional.dtd';
$highlighter = new sfLuceneHighlighterXML($xml);

$highlighter->addKeywords(array($keyword, $keyword2));
$highlighter->highlight();

$t->is($highlighter->export(), $expected, '->highlight() handles multiple keywords');

$xml = '<?xml version="1.0"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "'.$dtd.'">
<root>
  <child>hello &amp; baz&oacute;</child>
  <child>i&nbsp;am foobar</child>
</root>
';

$expected = '<?xml version="1.0"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "'.$dtd.'">
<root>
  <child>hello &amp; <s>baz</s>&oacute;</child>
  <child>i&nbsp;am <h>foobar</h></child>
</root>
';

$highlighter = new sfLuceneHighlighterXML($xml);
$highlighter->addKeywords(array($keyword, $keyword2));
$highlighter->highlight();

$t->is($highlighter->export(), $expected, '->highlight() handles entities correctly');


$xml = '<?xml version="1.0" encoding="utf-8"?>
<root>
  <child>hellÆ bäz</child>
  <child>i am fööbär</child>
</root>
';

$expected = '<?xml version="1.0" encoding="utf-8"?>
<root>
  <child>hellÆ <s>bäz</s></child>
  <child>i am <h>fööbär</h></child>
</root>
';

$keyword = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<h>%s</h>'), 'fööbär');
$keyword2 = new sfLuceneHighlighterKeywordNamed(new sfLuceneHighlighterMarkerSprint('<s>%s</s>'), 'bäz');


$highlighter = new sfLuceneHighlighterXML($xml);
$highlighter->addKeywords(array($keyword, $keyword2));
$highlighter->highlight();

$t->is($highlighter->export(), $expected, '->highlight() handles UTF8 characters correctly');

try {
  $h = new sfLuceneHighlighterXML('<foo>&ddd;<foo></baz></bar>');
  $h->highlight();
  $t->fail('->highlight() rejects invalid XML');
} catch (Exception $e) {
  $t->pass('->highlight() rejects invalid XML');
}

$highlighter = new sfLuceneHighlighterXML($xml);
$t->is($highlighter->__toString(), $xml, 'highlighter implements __toString()');