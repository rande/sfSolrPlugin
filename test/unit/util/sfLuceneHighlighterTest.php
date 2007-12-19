<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
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

$t = new lime_test(21, new lime_output_color());

class Foo { }

function inst($content, $blacklist = null)
{
  return new sfLuceneHighlighter($content, $blacklist);
}

$t->diag('testing validators');

try {
  inst('bad >')->addKeyword('bad')->highlight();
  $t->fail('highlighter rejects invalid HTML');
} catch (Exception $e) {
  $t->pass('highlighter rejects invalid HTML');
}

try {
  inst('bad >')->hasBody(false)->addKeyword('bad')->highlight();
  $t->fail('highlighter rejects invalid HTML with no body');
} catch (Exception $e) {
  $t->pass('highlighter rejects invalid HTML with no body');
}

try {
  inst('<body>bad ></body>')->hasBody(true)->addKeyword('bad')->highlight();
  $t->fail('highlighter rejects invalid HTML with a body');
} catch (Exception $e) {
  $t->pass('highlighter rejects invalid HTML with a body');
}

try {
  inst('<body>good</body>', new Foo);
  $t->fail('highlighter rejects invalid blacklist');
} catch (Exception $e) {
  $t->pass('highlighter rejects invalid blacklist');
}

$t->diag('testing highlighting');

$t->is(inst('<body>Hello</body>')->addKeyword('Hello')->addHighlighter('<h>%s</h>')->highlight(), '<body><h>Hello</h></body>', 'highlighter handles simple highlighting');

try {
  $t->is(inst('<body>Hello</body>')->addKeyword('Hello')->highlight(), '<body><strong>Hello</strong></body>', 'highlighter works without defining a highlighter');
} catch (Exception $e) {
  $t->fail('highlighter works without defining a highlighter');
}

$t->is(inst('<body><test>test yahoo</test></body>')->addKeyword('test')->addKeyword('yahoo')->addHighlighter('<h>%s</h>')->addHighlighter('<q>%s</q>')->highlight(), '<body><test><h>test</h> <q>yahoo</q></test></body>', 'highlighter highlights multiple keywords');

$t->is(inst('keyword <body>keywords keyword <keyword /> importantword <textarea>keyword</textarea></body> importantword')->addKeyword('keyword')->addKeyword('importantword')->addHighlighter('<h>%s</h>')->highlight(), 'keyword <body>keywords <h>keyword</h> <keyword /> <h>importantword</h> <textarea>keyword</textarea></body> importantword', 'highlighter highlights complex HTML content');

$t->is(inst('symfony project')->addKeyword('project')->addHighlighter('<h>%s</h>')->hasBody(false)->highlight(), 'symfony <h>project</h>', 'highlighter highlights keywords that appear at the end');

$t->is(inst('symfony project')->addKeyword('symfony')->addHighlighter('<h>%s</h>')->hasBody(false)->highlight(), '<h>symfony</h> project', 'highlighter highlights keywords that appear at the start');

$t->is(inst('i18n économe test')->addKeyword('économe')->addHighlighter('<h>%s</h>')->hasBody(false)->highlight(), 'i18n <h>économe</h> test', 'highlighter highlights strings with UTF8 characters');

$t->diag('testing keyword strings');

$t->is(inst('<body><test>test yahoo</test></body>')->addKeywordSlug('test yahoo')->addHighlighter('<h>%s</h>')->addHighlighter('<q>%s</q>')->highlight(), '<body><test><h>test</h> <q>yahoo</q></test></body>', 'highlighter highlights a keyword slug correctly');

$t->is(inst('<body><test>économe yahoo</test></body>')->addKeywordSlug('économe yahoo')->addHighlighter('<h>%s</h>')->addHighlighter('<q>%s</q>')->highlight(), '<body><test><h>économe</h> <q>yahoo</q></test></body>', 'highlighter highlights a keyword utf8 slug correctly');

$t->is(inst('<body><test>that was fun! fun0</test></body>')->addKeywordSlug('fun')->addHighlighter('<h>%s</h>')->highlight(), '<body><test>that was <h>fun</h>! fun0</test></body>', 'highlighter highlights a keyword surrounded by numbers correctly');

$t->is(inst('<body><test>that was fun! fun0</test></body>')->addKeywordSlug('fun0')->addHighlighter('<h>%s</h>')->highlight(), '<body><test>that was fun! <h>fun0</h></test></body>', 'highlighter highlights a keyword with numbers correctly');

$t->diag('density cropping');

$t->is(inst('This is a keyword test to determine if this algorithm can find the correct density of keywords and then automatically crop those keywords to show where they are most concentrated.') ->addKeywords(array('correct','density','of','keywords','keyword','test'))->densityCrop(50)->getContent(), '...algorithm can find the correct density of keywords...', 'cropper correctly crops string');

$t->is(inst('Keywords appear at the start of the massive string are really cool')->addKeywords(array('keywords','appear'))->densityCrop(28)->getContent(), 'Keywords appear at the start...', 'cropper does not add dots if start of string is present');

$t->is(inst('Keywords appear at the start of the massive string are really cool')->addKeywords(array('really','cool'))->densityCrop(28)->getContent(), '...string are really cool', 'cropper does not add dots if end of string is present');

$t->is(inst('Really short string')->addKeyword('really')->densityCrop(250)->getContent(), 'Really short string', 'cropper handles short strings');

$t->is(inst('This is a keyword test to determine if this algorithm can find the correct <strong>density</strong> of keywords and then automatically crop those keywords to show where they are most concentrated.') ->addKeywords(array('correct','density','of','keywords','keyword','test'))->densityCrop(50)->getContent(), '...algorithm can find the correct density of keywords...', 'cropper correctly handles HTML');

$t->is(inst('<body>This is a keyword test to determine if this algorithm can find the correct <strong>density</strong> of keywords and then automatically crop those keywords to show where they are most concentrated.</body>') ->addKeywords(array('correct','density','of','keywords','keyword','test'))->densityCrop(50)->addHighlighter('<h>%s</h>')->hasBody(false)->highlight(), '...algorithm can find the <h>correct</h> <h>density</h> <h>of</h> <h>keywords</h>...', 'cropper handles HTML and highlighting');