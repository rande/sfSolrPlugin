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

$t = new limeade_test(12, limeade_output::get());

$t->diag('testing constructor');

try {
  $form = new sfLuceneSimpleForm();
  $t->pass('__construct() with no arguments does not throw an exception');
} catch (Exception $e) {
  $t->fail('__construct() with no arguments does not throw an exception');
}

$t->diag('testing initialization');

$t->isa_ok($form->getWidgetSchema(), 'sfWidgetFormSchema', 'widget schema is appropriate type');
$t->isa_ok($form->getValidatorSchema(), 'sfValidatorSchema', 'validator schema is appropriate type');

$t->isa_ok($form->getWidgetSchema()->getFormFormatter(), 'sfLuceneWidgetFormatterSimple', 'formatter is appropriate type');

$t->diag('testing categories');

$categories = array('foo','bar','baz','foobar');

$form->setCategories($categories);

$t->ok($form->getWidgetSchema()->offsetExists('category'), '->setCategories() adds "category" key to widget schema');
$t->is_deeply($form->getWidgetSchema()->offsetGet('category')->getOption('choices'), $categories, '->setCategories() configures widget with correct choices');

$t->ok($form->getValidatorSchema()->offsetExists('category'), '->setCategories() adds "category" key to validator schema');
$t->is_deeply($form->getValidatorSchema()->offsetGet('category')->getOption('choices'), $categories, '->setCategories() configures validator with correct choices');

$form->setCategories(array());

$t->ok(!$form->getWidgetSchema()->offsetExists('category'), '->setCategories() removes "category" key from widget schema');
$t->ok(!$form->getValidatorSchema()->offsetExists('category'), '->setCategories() removes "category" key from validator schema');

$t->diag('testing url string generation');

$form = new sfLuceneSimpleForm;
$form->setCategories(array('baz'));
$form->bind(array('query' => 'foobar', 'category' => 'baz', 'page' => 2));

$t->is($form->getQueryString(), 'form%5Bquery%5D=foobar&amp;form%5Bcategory%5D=baz&amp;form%5Bpage%5D=2', '->getQueryString() returns querystring');
$t->is($form->getQueryString(5), 'form%5Bquery%5D=foobar&amp;form%5Bcategory%5D=baz&amp;form%5Bpage%5D=5', '->getQueryString() returns querystring with altered page');
