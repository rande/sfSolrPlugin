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

$t = new limeade_test(2, limeade_output::get());
$limeade = new limeade_sf($t);
$app = $limeade->bootstrap();

$luceneade = new limeade_lucene($limeade);
$luceneade->configure()->clear_sandbox();

$config = new sfLuceneModuleConfigHandler();

$response = $config->execute(array($luceneade->data_dir . '/configTest/module.yml'));

file_put_contents(lime_test::get_temp_directory() . '/search.yml.php', $response);
require lime_test::get_temp_directory() . '/search.yml.php';
unlink(lime_test::get_temp_directory() . '/search.yml.php');

$t->ok(isset($actions), '->execute() creates a $actions variable');

$t->is_deeply($actions,
  array('testLucene' =>
    array (
      'bar' =>
      array (
        'security' =>
        array (
          'authenticated' => false,
          'credentials' =>
          array (
          ),
        ),
        'params' =>
        array (
        ),
        'layout' => false,
      ),
      'foo' =>
      array (
        'security' =>
        array (
          'authenticated' => true,
          'credentials' =>
          array (
            'admin',
          ),
        ),
        'params' =>
        array (
          'foo' => 'bar',
          'baz' => 'foobar',
        ),
        'layout' => true,
      ),
    ),
  ), '->execute() writes the correct configuration from the YAML file');