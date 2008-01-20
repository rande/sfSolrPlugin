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
$luceneade->configure()->clear_sandbox()->load_models();

$config = new sfLuceneProjectConfigHandler();

$response = $config->execute(array($luceneade->data_dir . '/configTest/project.yml'));

file_put_contents(lime_test::get_temp_directory() . '/search.yml.php', $response);
require lime_test::get_temp_directory() . '/search.yml.php';
unlink(lime_test::get_temp_directory() . '/search.yml.php');

$t->ok(isset($config), '->execute() creates a $config variable');

$t->is_deeply($config, array (
  'testLucene' =>
  array (
    'models' =>
    array (
      'FakeModel' =>
      array (
        'fields' =>
        array (
          'id' =>
          array (
            'type' => 'text',
            'boost' => 1,
            'transform' => NULL,
          ),
          'title' =>
          array (
            'type' => 'text',
            'boost' => 1,
            'transform' => NULL,
          ),
          'description' =>
          array (
            'type' => 'text',
            'boost' => 3,
            'transform' => NULL,
          ),
        ),
        'title' => 'title',
        'description' => 'description',
        'categories' =>
        array (
          0 => 'Forum',
        ),
        'route' => 'forum/showForum?id=%id%',
        'validator' => 'isIndexable',
        'rebuild_limit' => 5,
        'peer' => 'FakeForumPeer',
        'partial' => 'forumResult',
        'indexer' => NULL,
      ),
    ),
    'index' =>
    array (
      'encoding' => 'UTF-8',
      'cultures' =>
      array (
        0 => 'en',
        1 => 'fr',
      ),
      'stop_words' =>
      array (
        0 => 'and',
        1 => 'the',
      ),
      'short_words' => 2,
      'analyzer' => 'utf8num',
      'case_sensitive' => false,
      'mb_string' => true,
      'param' =>
      array (
      ),
    ),
    'interface' =>
    array (
      'categories' => true,
      'advanced' => true,
    ),
    'factories' =>
    array (
      'indexers' =>
      array (
      ),
      'results' =>
      array (
      ),
    ),
  ),
  'barLucene' =>
  array (
    'models' =>
    array (
      'FakeModel' =>
      array (
        'fields' =>
        array (
        ),
        'partial' => NULL,
        'indexer' => NULL,
        'title' => NULL,
        'description' => NULL,
        'peer' => 'FakeModelPeer',
        'rebuild_limit' => 250,
        'validator' => 'isIndexable',
      ),
    ),
    'index' =>
    array (
      'encoding' => 'utf-8',
      'cultures' =>
      array (
        0 => NULL,
      ),
      'stop_words' =>
      array (
        0 => 'a',
        1 => 'an',
        2 => 'at',
        3 => ' the',
        4 => 'and',
        5 => 'or',
        6 => 'is',
        7 => 'am',
        8 => 'are',
        9 => 'of',
      ),
      'short_words' => 2,
      'analyzer' => 'textnum',
      'case_sensitive' => false,
      'mb_string' => false,
      'param' =>
      array (
      ),
    ),
    'factories' =>
    array (
      'indexers' =>
      array (
      ),
      'results' =>
      array (
      ),
    ),
  ),
  'fooLucene' =>
  array (
    'models' =>
    array (
    ),
    'index' =>
    array (
      'encoding' => 'utf-8',
      'cultures' =>
      array (
        0 => NULL,
      ),
      'stop_words' =>
      array (
        0 => 'a',
        1 => 'an',
        2 => 'at',
        3 => ' the',
        4 => 'and',
        5 => 'or',
        6 => 'is',
        7 => 'am',
        8 => 'are',
        9 => 'of',
      ),
      'short_words' => 2,
      'analyzer' => 'textnum',
      'case_sensitive' => false,
      'mb_string' => false,
      'param' =>
      array (
      ),
    ),
    'factories' =>
    array (
      'indexers' =>
      array (
      ),
      'results' =>
      array (
      ),
    ),
  ),
), '->execute() writes the correct configuration from the YAML file');