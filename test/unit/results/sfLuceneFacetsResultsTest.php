<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2009 Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * @package sfLucenePlugin
  * @subpackage Test
  * @author Thomas Rabaix
  * @version SVN: $Id: sfLuceneResultsTest.php 24784 2009-12-02 09:58:03Z rande $
  */

require dirname(__FILE__) . '/../../bootstrap/unit.php';

$t = new limeade_test(2, limeade_output::get());


class MockResult extends Apache_Solr_Document
{
  public $name;

  public function __construct($a)
  {
    $this->name = $a;
  }
}

// create fake response
$standard_response = '{"responseHeader":{"status":0,"QTime":0},"response":{"numFound":3,"start":%s,"docs":[%s]}, %s}';
$expected_objects = array();
$results = array();

foreach(range(1, 3) as $num)
{
  $results[] = sprintf(
    '{"description":"symfony fan boy","id":%d,"name":"rande","sfl_guid":"GUID_%d","skills":["symfony","php","objective-c"]}',
    $num,
    $num
  );
}

// faceting stuff
$facets = '"facet_counts":{"facet_queries":{"name:[a TO z]":3},"facet_fields":{"sfl_model":{"User":1, "Group": 2}},"facet_dates":{}}';

$standard_response = sprintf($standard_response, 3, implode(", ", $results), $facets);

$response = new Apache_Solr_Response($standard_response);

$search = sfLucene::getInstance('index', 'en', $app_configuration);

$results = new sfLuceneFacetsResults($response, $search);

$expected_queries = array(
  "name:[a TO z]" => 3,
);
$t->is_deeply($results->getFacetQueries(), $expected_queries, '->getFacetQueries() returns the expected array');

$expected_fields = array(
  'sfl_model' => array(
    "User" => 1,
    "Group" => 2,
  )
);
$t->is_deeply($results->getFacetFields(), $expected_fields, '->getFacetFields() returns the expected array');
