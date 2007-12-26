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
require dirname(__FILE__) . '/../../bin/AllFakeModels.php';

class FooIndexer extends sfLucenePropelIndexerHandler
{
  public $rebuilds = array();

  public function rebuildModel($name)
  {
    $this->rebuilds[] = $name;
  }
}

$t = new lime_test(3, new lime_output_color());

$search = sfLucene::getInstance('testLucene', 'en');

$t->diag('testing ->rebuild()');
$handler = new FooIndexer($search);

$handler->rebuild();

$t->is($handler->rebuilds, array('FakeForum'), '->rebuild() calls ->rebuildModel() for all models');

$t->diag('testing ->rebuildModel()');
$handler = new sfLucenePropelIndexerHandler($search);

$search->getParameter('models')->get('FakeForum')->set('rebuild_limit', 5);

$models = array();

for ($x = 0; $x < 6;  $x++)
{
  $var = new FakeForum;
  $var->setCulture('en');
  $var->setTitle('foo');
  $var->setDescription('bar');
  $var->setCoolness(3);
  $var->save();
  $var->deleteIndex();

  $models[] = $var;
}

$search->commit();

$t->is($search->numDocs(), 0, 'model setup leaves index empty');

$handler->rebuildModel('FakeForum');
$search->commit();

$t->is($search->numDocs(), count($models), '->rebuildModel() builds all models');

foreach ($models as $model)
{
  $model->delete();
}