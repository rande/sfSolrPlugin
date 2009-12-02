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

$t = new limeade_test(9, limeade_output::get());

class DummyForm extends sfLuceneForm
{
  public $setupCount = 0, $configureCount = 0;

  public function configure()
  {
    $this->configureCount++;
  }

  public function setup()
  {
    $this->setupCount++;
  }
}

$t->diag('testing constructor');

try {
  $form = new DummyForm();
  $t->pass('__construct() with no arguments does not throw an exception');
} catch (Exception $e) {
  $t->fail('__construct() with no arguments does not throw an exception');
}

$t->diag('testing categories');

try {
  $form->setCategories('foo');
  $t->fail('->setCategories() rejects invalid inputs');
} catch (Exception $e) {
  $t->pass('->setCategories() rejects invalid inputs');
}

$formFreeze = clone $form;

try {
  $form->setCategories(array('foo', 'bar', 'baz'));
  $t->pass('->setCategories() accepts valid inputs');
} catch (Exception $e) {
  $t->fail('->setCategories() accepts valid inputs');
}

$t->is($form->hasCategories(), true, '->hasCategories() returns true when categories are set');

$t->is($form->setupCount, $formFreeze->setupCount + 1, '->setCategories() runs ->setup()');
$t->is($form->configureCount, $formFreeze->configureCount + 1, '->setCategories() runs ->configure()');

$t->is_deeply($form->getCategories(), array('foo', 'bar', 'baz'), '->getCategories() returns the categories');

try {
  $form->setCategories(array());
  $t->pass('->setCategories() accepts an empty array');
} catch (Exception $e) {
  $t->fail('->setCategories() accepts valid inputs');
}

$t->is($form->hasCategories(), false, '->hasCategories() returns true when categories are set');