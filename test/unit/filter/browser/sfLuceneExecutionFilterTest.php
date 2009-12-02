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

require dirname(__FILE__) . '/../../../bootstrap/unit.php';

$t = new limeade_test(5, limeade_output::get());

class FooFilter extends sfLuceneExecutionFilter
{
  // public interface to executeView
  public function ev($moduleName, $actionName, $viewName, $viewAttributes)
  {
    return $this->executeView($moduleName, $actionName, $viewName, $viewAttributes);
  }
}

$moduleName = 'test';
$actionName = 'foo';

$context = sfContext::createInstance($app_configuration);

$actionInstance = $context->getController()->getAction($moduleName, $actionName);

$context->getController()->getActionStack()->addEntry($moduleName, $actionName, $actionInstance);

$filter = new FooFilter($context);
$chain = new sfFilterChain;

try {
  $ex = $t->no_exception('->execute() runs without exception');
  $filter->execute($chain);
  $ex->no();
} catch (Exception $e) {
  $ex->caught($e);
}

$t->todo('->executeView() sets decorator to false');
$t->todo('->executeView() handles RENDER_NONE mode');
$t->todo('->executeView() handles RENDER_CLIENT mode');
$t->todo('->executeView() handles RENDER_VAR mode');