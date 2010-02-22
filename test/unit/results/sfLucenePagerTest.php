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

$t = new limeade_test(45, limeade_output::get());


class sfMockApacheService
{
  public function search($query, $offset = 0, $limit = 10, $params = array(), $method = 'GET')
  {
    
    $standard_response = '{"responseHeader":{"status":0,"QTime":0},"response":{"numFound":1001,"start":%s,"docs":[%s]}}';
    $results = array();
    
    foreach(range($offset, $offset + $limit - 1) as $num)
    {
      $results[] = sprintf(
        '{"description":"symfony fan boy","id":%d,"name":"rande","sfl_guid":"GUID_%d","skills":["symfony","php","objective-c"]}',
        $num,
        $num
      );
    }
    $standard_response = sprintf($standard_response, $offset, implode(", ", $results));
    
    $response = new sfLuceneResponse($standard_response);
    
    $response->sf_lucene_search = array(
      'query'   => $query,
      'offset'  => $offset,
      'limit'   => $limit,
      'params'  => $params,
      'method'  => $method
    );

    return $response;
  }
}

$lucene = sfLucene::getInstance('index', 'en', $app_configuration);
$lucene->setSearchService(new sfMockApacheService);

$t->diag('testing constructor');

try {
  new sfLucenePager('a', $lucene);
  $t->fail('__construct() rejects a non-array');
} catch (Exception $e) {
  $t->pass('__construct() rejects a non-array');
}

$response = $lucene->find('dummy search');

try {
  $pager = new sfLucenePager(new sfLuceneResults($response, $lucene));
  $t->pass('__construct() accepts sfLuceneResults');
} catch (Exception $e) {
  $t->fail('__construct() accepts sfLuceneResults');
}

$t->diag('testing basic pagination functions');

try {
  $pager->setPage(2);
  $t->pass('->setPage() accepts a integer page');
} catch (Exception $e) {
  $t->fail('->setPage() accepts a integer page');
}

try {
  $pager->setMaxPerPage(10);
  $t->pass('->setMaxPerPage() accepts an integer per page');
} catch (Exception $e) {
  $t->fail('->setMaxPerPage() accepts an integer per page');
}

$t->is($pager->getPage(), 2, '->getPage() returns current page');
$t->is($pager->getMaxPerPage(), 10, '->getMaxPerPage() returns the max per page');
$t->is($pager->getNbResults(), 1001, '->getNbResults() returns the total number of results');
$t->ok($pager->haveToPaginate(), '->haveToPaginate() returns correct value');

$pager->setPage(0);
$t->is($pager->getPage(), 1, '->setPage() to 0 sets the page to 1');

$pager->setPage(100000);
$t->is($pager->getPage(), 101, '->setPage() above to upper bound resets to last page');

$pager->setPage(2);
$t->diag('testing ->getResults()');

$results = $pager->getResults()->toArray();
foreach(range(10, 19) as $id)
{
  $guid = 'GUID_'.$id;
  $t->cmp_ok($results[$id - 10]->sfl_guid, '==', $guid, '->getResults() returns the correct range, sfl_guid:'.$guid);
}

$pager->setPage(3);
$results = $pager->getResults()->toArray();
foreach(range(20, 29) as $id)
{
  $guid = 'GUID_'.$id;
  $t->cmp_ok($results[$id - 20]->sfl_guid, '==', $guid, '->getResults() returns the correct range after page change, sfl_guid:'.$guid);
}

$pager->setMaxPerPage(20);

$t->diag('testing page numbers');

$t->is($pager->getFirstPage(), 1, '->getFirstPage() returns 1 as first page');
$t->is($pager->getLastPage(), 51, '->getLastPage() returns the last page in the range');

$t->is($pager->getNextPage(), 4, '->getNextPage() returns the next page');
$pager->setPage(101);
$t->is($pager->getNextPage(), 51, '->getNextPage() returns last page if at end');
$pager->setPage(4);

$t->is($pager->getPreviousPage(), 3, '->getPreviousPage() returns the previous page');
$pager->setPage(1);
$t->is($pager->getPreviousPage(), 1, '->getPreviousPage() returns the first page if at start');
$pager->setPage(4);

$t->diag('testing page indices');
$pager->setPage(4);
$t->is($pager->getFirstIndice(), 61, '->getFirstIndice() returns correct first indice in results');
$t->is($pager->getLastIndice(), 80, '->getLastIndice() returns correct last indice in result');

$pager->setMaxPerPage(8);
$pager->setPage($pager->getLastPage());

$t->is($pager->getLastIndice(), 1001, '->getLastIndice() returns correct last indice if more can fit on the page');


$t->diag('testing link generator');
$pager->setMaxPerPage(10);
$pager->setPage(4);
$t->is($pager->getLinks(5), range(2, 6), '->getLinks() returns the correct link range');

$pager->setPage(1);
$t->is($pager->getLinks(5), range(1, 5), '->getLinks() returns correct link range when at start of index');

$pager->setPage(101);
$t->is($pager->getLinks(5), range(97, 101), '->getLinks() returns link range when at end of index');

$t->diag('testing mixins');

function callListener($event)
{
  if ($event['method'] == 'goodMethod')
  {
    $args = $event['arguments'];

    $event->setReturnValue($args[0] + 1);

    return true;
  }

  return false;
}

$lucene->getEventDispatcher()->connect('pager.method_not_found', 'callListener');

try {
  $pager->someBadMethod();
  $t->fail('__call() rejects bad methods');
} catch (Exception $e) {
  $t->pass('__call() rejects bad methods');
}

try {
  $return = $pager->goodMethod(2);
  $t->pass('__call() accepts good methods');
  $t->is($return, 3, '__call() passes arguments');
} catch (Exception $e) {
  $t->fail('__call() accepts good methods and passes arguments');

  $e->printStackTrace();

  $t->skip('__call() passes arguments');
}