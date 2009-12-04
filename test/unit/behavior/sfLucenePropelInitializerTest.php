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

// NOTE : For now Propel implementation is not tested / supported

require dirname(__FILE__) . '/../../bootstrap/unit.php';
 
$t = new limeade_test(0, limeade_output::get());

// require dirname(__FILE__) . '/../../bootstrap/unit.php';
// 
// $t = new limeade_test(4, limeade_output::get());
// $limeade = new limeade_sf($t);
// $app = $limeade->bootstrap();
// 
// $luceneade = new limeade_lucene($limeade);
// $luceneade->configure()->clear_sandbox()->load_models();
// 
// class FooBehavior extends sfPropelBehavior
// {
//   static public function getBehaviors()
//   {
//     return self::$behaviors;
//   }
// }
// 
// class FooMixer extends sfMixer
// {
//   static public function getMixins()
//   {
//     return self::$mixins;
//   }
// }
// 
// $t->diag('testing ::getInstance()');
// 
// $init = sfLucenePropelInitializer::getInstance();
// 
// $t->isa_ok($init, 'sfLucenePropelInitializer', '::getInstance() returns an instance of sfLucenePropelInitializer');
// $t->is(sfLucenePropelInitializer::getInstance(), $init, '::getInstance() returns the same instance each time (singleton)');
// 
// $t->is_deeply(FooBehavior::getBehaviors(), array (
//   'search' =>
//   array (
//     'methods' =>
//     array (
//       0 =>
//       array (
//         0 => 'sfLucenePropelBehavior',
//         1 => 'saveIndex',
//       ),
//       1 =>
//       array (
//         0 => 'sfLucenePropelBehavior',
//         1 => 'deleteIndex',
//       ),
//       2 =>
//       array (
//         0 => 'sfLucenePropelBehavior',
//         1 => 'insertIndex',
//       ),
//     ),
//     'hooks' =>
//     array (
//       ':save:pre' =>
//       array (
//         0 =>
//         array (
//           0 => 'sfLucenePropelBehavior',
//           1 => 'preSave',
//         ),
//       ),
//       ':save:post' =>
//       array (
//         0 =>
//         array (
//           0 => 'sfLucenePropelBehavior',
//           1 => 'postSave',
//         ),
//       ),
//       ':delete:pre' =>
//       array (
//         0 =>
//         array (
//           0 => 'sfLucenePropelBehavior',
//           1 => 'preDelete',
//         ),
//       ),
//       ':delete:post' =>
//       array (
//         0 =>
//         array (
//           0 => 'sfLucenePropelBehavior',
//           1 => 'postDelete',
//         ),
//       ),
//     ),
//   ),
// ), '__construct() registers the correct methods and hooks');
// 
// $t->diag('testing ->setup()');
// 
// $init->setup('FakeModel');
// 
// $t->is_deeply(FooMixer::getMixins(), array (
//   'BaseFakeForum:save:pre' =>
//   array (
//     0 =>
//     array (
//       0 => 'sfLucenePropelBehavior',
//       1 => 'preSave',
//     ),
//   ),
//   'BaseFakeForum:save:post' =>
//   array (
//     0 =>
//     array (
//       0 => 'sfLucenePropelBehavior',
//       1 => 'postSave',
//     ),
//   ),
//   'BaseFakeForum:delete:pre' =>
//   array (
//     0 =>
//     array (
//       0 => 'sfLucenePropelBehavior',
//       1 => 'preDelete',
//     ),
//   ),
//   'BaseFakeForum:delete:post' =>
//   array (
//     0 =>
//     array (
//       0 => 'sfLucenePropelBehavior',
//       1 => 'postDelete',
//     ),
//   ),
//   'BaseFakeForum:saveIndex' =>
//   array (
//     0 =>
//     array (
//       0 => 'sfLucenePropelBehavior',
//       1 => 'saveIndex',
//     ),
//   ),
//   'BaseFakeForum:deleteIndex' =>
//   array (
//     0 =>
//     array (
//       0 => 'sfLucenePropelBehavior',
//       1 => 'deleteIndex',
//     ),
//   ),
//   'BaseFakeForum:insertIndex' =>
//   array (
//     0 =>
//     array (
//       0 => 'sfLucenePropelBehavior',
//       1 => 'insertIndex',
//     ),
//   ),
//   'BaseFakeModel:save:pre' =>
//   array (
//     0 =>
//     array (
//       0 => 'sfLucenePropelBehavior',
//       1 => 'preSave',
//     ),
//   ),
//   'BaseFakeModel:save:post' =>
//   array (
//     0 =>
//     array (
//       0 => 'sfLucenePropelBehavior',
//       1 => 'postSave',
//     ),
//   ),
//   'BaseFakeModel:delete:pre' =>
//   array (
//     0 =>
//     array (
//       0 => 'sfLucenePropelBehavior',
//       1 => 'preDelete',
//     ),
//   ),
//   'BaseFakeModel:delete:post' =>
//   array (
//     0 =>
//     array (
//       0 => 'sfLucenePropelBehavior',
//       1 => 'postDelete',
//     ),
//   ),
//   'BaseFakeModel:saveIndex' =>
//   array (
//     0 =>
//     array (
//       0 => 'sfLucenePropelBehavior',
//       1 => 'saveIndex',
//     ),
//   ),
//   'BaseFakeModel:deleteIndex' =>
//   array (
//     0 =>
//     array (
//       0 => 'sfLucenePropelBehavior',
//       1 => 'deleteIndex',
//     ),
//   ),
//   'BaseFakeModel:insertIndex' =>
//   array (
//     0 =>
//     array (
//       0 => 'sfLucenePropelBehavior',
//       1 => 'insertIndex',
//     ),
//   ),
// ), '->setup() configures sfMixer correctly');