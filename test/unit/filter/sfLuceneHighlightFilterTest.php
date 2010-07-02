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

$t = new limeade_test(19, limeade_output::get());

$chain = new sfFilterChain();


$context = sf_lucene_get_fake_context($app_configuration);

$context->getRouting()->setCurrentRouteName('current_route');

sfConfig::set('sf_i18n', false);

$highlight = new sfLuceneHighlightFilter($context, array(
  'highlight_qs'              => 'h',
  'notice_tag'                => '~notice~',
  'highlight_strings'         => array(
                                    '<highlighted>%s</highlighted>',
                                    '<highlighted2>%s</highlighted2>'
                                ),
  'notice_referer_string'     => '<from>%from%</from><keywords>%keywords%</keywords><remove>%remove%</remove>',
  'notice_string'             => '<keywords>%keywords%</keywords><remove>%remove%</remove>',
  'remove_string'             => '~remove~',
  'css'                       => 'search.css',
));

$t->diag('testing validation');

function notify($given = null)
{
  
  // $d = debug_backtrace();
  // var_dump($d[0]); die();
  static $event;

  if ($given)
  {
    $event = $given;
  }

  return $event;
}

$request = $context->getRequest();
$response = $context->getResponse();

$request->setParameter('h', 'test');

$context->getEventDispatcher()->connect('application.log', 'notify');

$response->setContent('<html><body>&foobar; </foo></body></html>');
try {
  $highlight->execute($chain);
  $t->pass('highlighter accepts content if it is malformed');
} catch (Exception $e) {
  $t->fail('highlighter accepts content if it is malformed');
}

$t->is($response->getContent(), '<html><body>&foobar; </foo></body></html>', 'highlighter does not touch malformed content');

$t->isa_ok(notify(), 'sfEvent', 'highlighter notifies application log of malformed content');

$response->setContent('<html><body>Hello</body></html>');
try {
  $highlight->execute($chain);
  $t->pass('highlighter accepts content with a complete body tag set');
} catch (sfException $e) {
  $t->fail('highlighter accepts content with a complete body tag set');
}

$response->setContent('<html><body>I am <b>cool</b>!</body></html>');
try {
  $highlight->execute($chain);
  $t->pass('highlighter accepts content with a complete body tag set and other carats');
} catch (sfException $e) {
  $t->fail('highlighter accepts content with a complete body tag set and other carats');
}

$t->diag('testing highlighting');

$response->setContent('<html><body>highlight the keyword</body></html>');
$request->setParameter('h', 'keyword');
$highlight->execute($chain);
$t->is($response->getContent(), "<?xml version=\"1.0\"?>\n<html><body>highlight the <highlighted>keyword</highlighted></body></html>\n", 'highlighter highlights a single keyword');

$response->setContent('<html><body>highlight the keyword yay!</body></html>');
$request->setParameter('h', 'highlight KEYWORD');
$highlight->execute($chain);
$t->is($response->getContent(), "<?xml version=\"1.0\"?>\n<html><body><highlighted>highlight</highlighted> the <highlighted2>keyword</highlighted2> yay!</body></html>\n", 'highlighter highlights multiple keywords');

$response->setContent('<html><body>~notice~ keyword</body></html>');
$request->setParameter('h', 'keyword');
$highlight->execute($chain);
$t->like($response->getContent(), '#<body><keywords><highlighted>keyword</highlighted></keywords><remove>~remove~</remove>#', 'highlighter adds notice string');

$response->setContent('<html><head><title>foobar</title></head><body>keyword</body></html>');
$highlight->execute($chain);
$t->like($response->getContent(), '#<link .*?href=".*?search\.css".*?/>\n</head>#', 'highlighter adds search stylesheet');

$response->setContent('<html><head><title>foobar</title></head><body>~notice~ google search test</body></html>');
$request->getParameterHolder()->remove('h');
$_SERVER['HTTP_REFERER'] = 'http://www.google.com/search?num=50&hl=en&safe=off&q=google&btnG=Search';
$highlight->execute($chain);

$t->like($response->getContent(), '#<highlighted>google</highlighted> search test#', 'highlighter highlights results from Google');
$t->like($response->getContent(), '#<from>Google</from><keywords><highlighted>google</highlighted></keywords><remove>~remove~</remove>#', 'highlighter adds correct notice for results from Google');
$t->like($response->getContent(), '#<link .*?href=".*?search\.css".*?/>\n</head>#', 'highlighter adds search stylesheet for results from Google');

$t->diag('testing conditions when no highlighting occurs');

$request->getParameterHolder()->remove('h');
$_SERVER['HTTP_REFERER'] = null;

$response->setContent('<head></head><body>~notice~ keywords</body>');
$highlight->execute($chain);

$t->unlike($response->getContent(), '#~notice~#', 'highlighter removes notice replacement if there is nothing to do');
$t->unlike($response->getContent(), '#<link .*?href=".*?search\.css".*?/>\n</head>#', 'highlighter does not add the search stylesheet if there is nothing to do');
$t->is($response->getContent(), '<head></head><body> keywords</body>', 'highlighter leaves result untouched except for notice bang if there is nothing to do');

$_SERVER['HTTP_REFERER'] = 'http://www.slashdot.org/';
$response->setContent('<head></head><body>~notice~ google search test</body>');
$request->getParameterHolder()->remove('h');
$highlight->execute($chain);

$t->is($response->getContent(), '<head></head><body> google search test</body>', 'highlighter removes notice if there is a possible but invalid refer and does not touch rest of response');

$t->diag('testing different content types');
$content = '<head></head><body>~notice~ keyword</body>';
$response->setContent($content);
$request->setParameter('h', 'keyword');

$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$highlight->execute($chain);
$t->is($response->getContent(), $content, 'highlighter skips highlighting on ajax requests');
$_SERVER['HTTP_X_REQUESTED_WITH'] = null;

$response->setHeaderOnly(true);
$highlight->execute($chain);
$t->is($response->getContent(), $content, 'highlighter skips highlighting for header only responses');
$response->setHeaderOnly(false);

$response->setContentType('image/jpeg');
$highlight->execute($chain);
$t->is($response->getContent(), $content, 'highlighter skips highlighting for non X/HTML content');
$response->setContentType('text/html');

// don't get what this means

/*
$t->diag('testing i18n');
 
$i18n = $app->i18n()->setup('en_US');

$response->setContent('<html><body>highlight the keyword</body></html>');
$request->setParameter('h', 'keyword');
$highlight->execute($chain);

$t->is($response->getContent(), "<?xml version=\"1.0\"?>\n<html><body>highlight the <highlighted>keyword</highlighted></body></html>\n", 'highlighter highlights a single keyword with i18n');
 
$i18n->teardown();

*/