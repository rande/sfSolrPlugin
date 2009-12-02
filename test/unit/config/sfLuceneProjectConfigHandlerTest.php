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

$config = new sfLuceneProjectConfigHandler();

$response = $config->execute(array( dirname(__FILE__) . '/../../data/configTest/project.yml'));

file_put_contents(sys_get_temp_dir()  . '/search.yml.php', $response);
require sys_get_temp_dir()  . '/search.yml.php';
unlink(sys_get_temp_dir()  . '/search.yml.php');

$t->ok(isset($config), '->execute() creates a $config variable');

$expected = array (
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
            'multiValued' => false,
            'required' => false,
            'stored'   => false,
            'default' => null
          ),
          'title' =>
          array (
            'type' => 'text',
            'boost' => 1,
            'transform' => NULL,
            'multiValued' => false,
            'required' => false,
            'stored'   => false,
            'default' => null
          ),
          'description' =>
          array (
            'type' => 'text',
            'boost' => 3,
            'transform' => NULL,
            'multiValued' => false,
            'required' => false,
            'stored'   => false,
            'default' => null
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
        'peer' => 'FakeForumTable',
        'partial' => 'forumResult',
        'callback' => NULL,
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
      'host' => 'localhost',
      'port' => '8983',
      'base_url' => '/solr',

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
        'callback' => NULL,
        'route' => NULL,
        'indexer' => NULL,
        'title' => NULL,
        'description' => NULL,
        'peer' => 'FakeModelTable',
        'rebuild_limit' => 250,
        'validator' => NULL,
        'categories' =>
        array (
        ),
      ),
    ),
    'index' =>
    array (
      'encoding' => 'utf-8',
      'cultures' =>
      array (
        0 => 'en',
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
      'host' => 'localhost',
      'port' => '8983',
      'base_url' => '/solr',
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
        0 => 'en',
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
      'host' => 'localhost',
      'port' => '8983',
      'base_url' => '/solr',
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
  )
);

$t->is_deeply($config, $expected, '->execute() writes the correct configuration from the YAML file');