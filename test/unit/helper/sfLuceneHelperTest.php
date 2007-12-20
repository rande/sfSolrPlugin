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

sfLuceneToolkit::loadZend();

sfLoader::loadHelpers(array('sfLucene'));

// intercepts includes to partials
function include_partial($partial = null, $params = null, $dump = false)
{
  static $sPartial;
  static $sParams;

  if ($dump)
  {
    return array('partial' => $sPartial, 'params' => $sParams);
  }

  $sPartial = $partial;
  $sParams = $params;
}

class Foo
{
  public function getInternalPartial()
  {
    return 'FooPartial';
  }
}
class Bar
{
}

$foo = new Foo;
$bar = new Bar;

$t = new lime_test(16, new lime_output_color());

$t->diag('testing partial dependencies');

include_search_result($foo, 'query');
$values = include_partial(null, null, true);
$t->is($values['partial'], 'FooPartial', 'include_search_result() selects the correct partial');
$t->ok($values['params']['result'] === $foo, 'include_search_result() sends the same result');
$t->is($values['params']['query'], 'query', 'include_search_result() passes the query');

include_search_controls($foo);
$values = include_partial(null, null, true);
$t->is($values['partial'], 'sfLucene/controls', 'include_search_controls() selects the correct partial');
$t->ok($values['params']['form'] === $foo, 'include_search_controls() sends the same form');

include_search_pager($foo, $bar, 8);
$values = include_partial(null, null, true);
$t->is($values['partial'], 'sfLucene/pagerNavigation', 'include_search_pager() selects the correct partial');
$t->ok($values['params']['pager'] === $foo, 'include_search_pager() sends the same pager');
$t->ok($values['params']['form'] === $bar, 'include_search_pager() sends the same form');
$t->is($values['params']['radius'], 8, 'include_search_pager() sends the correct radius');

$t->diag('testing highlighting');

$t->is(highlight_result_text('Hello.  This is a pretty <em class="thing">awesome</em> thing to be talking about.', 'thing talking'), 'Hello.  This is a pretty awesome <strong class="highlight">thing</strong> to be <strong class="highlight">talking</strong> about.', 'highlight_result_text() highlights text and strips out HTML');

$t->is(highlight_result_text('Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. This is a pretty <em class="thing">awesome</em> thing to be talking about.  Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. Foo bar. ', 'thing talking', 50), '...This is a pretty awesome <strong class="highlight">thing</strong> to be <strong class="highlight">talking</strong> about....', 'highlight_result_text() highlights and truncates text');

$t->is(highlight_keywords('Hello.  This is a pretty <em class="thing">awesome</em> thing to be talking about.', 'thing talking'), 'Hello.  This is a pretty <em class="thing">awesome</em> <strong class="highlight">thing</strong> to be <strong class="highlight">talking</strong> about.', 'highlight_kewyords() highlights text');


$t->diag('testing query string manipulation');

$t->is(add_highlight_qs('test/model', 'foo bar'), 'test/model?sf_highlight=foo bar', 'add_highlight_qs() adds a query string correctly');

$t->is(add_highlight_qs('test/model?a=b', 'foo bar'), 'test/model?a=b&sf_highlight=foo bar', 'add_highlight_qs() handles existing query strings');

$t->is(add_highlight_qs('test/model#anchor', 'foo bar'), 'test/model?sf_highlight=foo bar#anchor', 'add_highlight_qs() handles anchors');

$t->is(add_highlight_qs('test/model?a=b#anchor', 'foo bar'), 'test/model?a=b&sf_highlight=foo bar#anchor', 'add_highlight_qs() handles anchors and existing query strings');