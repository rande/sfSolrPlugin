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
  * @version SVN: $Id: sfLuceneCriteriaTest.php 24784 2009-12-02 09:58:03Z rande $
  */

require dirname(__FILE__) . '/../../bootstrap/unit.php';

$t = new limeade_test(5, limeade_output::get());

class Foo { }

function inst($app_configuration)
{
  return sfLuceneCriteria::newInstance(sfLucene::getInstance('index','en', $app_configuration));
}

$t->diag('testing constructors');
try {
  $criteria = new sfLuceneFacetsCriteria(sfLucene::getInstance('index','en', $app_configuration));
  $t->pass('__construct() takes a sfLucene instance');
} catch (Exception $e) {
  $t->fail('__construct() takes a sfLuce instance');
}
$t->isa_ok(sfLuceneFacetsCriteria::newInstance(sfLucene::getInstance('index','en', $app_configuration)), 'sfLuceneFacetsCriteria', '::newInstance() returns an sfLuceneFacetsCriteria object');

$t->diag('testing ->getQuery()');
$t->ok(is_string($criteria->getQuery()), '->getQuery() returns an instance a string');


$criteria->addField('language');
$criteria->addField('task');

$criteria->addQuery('price:[0 TO 100]');
$criteria->addQuery('price:[100 TO 200]');

$expected = array (
  'facet' => array (
    0 => 'true',
  ),
  'facet.field' => array (
    0 => 'language',
    1 => 'task',
  ),
  'facet.query' => array (
    0 => 'price:[0 TO 100]',
    1 => 'price:[100 TO 200]',
  ),
);

$t->is_deeply($criteria->getParams(), $expected, '->getParams() return the parameters array');

$t->diag('testing ->addField() and ->addQuery() reset');
$criteria->addField('another_field', true);
$criteria->addQuery('the_price:[0 TO 100]', true);

$expected = array (
  'facet' => array (
    0 => 'true',
  ),
  'facet.field' => array (
    0 => 'another_field',
  ),
  'facet.query' => array (
    0 => 'the_price:[0 TO 100]',
  ),
);

$t->is_deeply($criteria->getParams(), $expected, '->getParams() return the parameters array with reseted values');


