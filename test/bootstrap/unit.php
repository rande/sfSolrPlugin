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

error_reporting(E_ALL);

define('SF_ROOT_DIR', realpath(dirname(__FILE__) . '/../../../..'));

require_once SF_ROOT_DIR . '/config/config.php';
require_once $sf_symfony_lib_dir . '/vendor/lime/lime.php';

require_once dirname(__FILE__) . '/../limeade/limeade_loader.php';
require_once dirname(__FILE__) . '/../bin/limeade_lucene.php';

limeade_loader::all();


