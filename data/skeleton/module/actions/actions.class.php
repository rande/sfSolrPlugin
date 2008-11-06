<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once  sfConfig::get('sf_plugins_dir'). '/sfLucenePlugin/modules/sfLucene/lib/BasesfLuceneActions.class.php';

/**
 * @package    sfLucenePlugin
 * @subpackage Module
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id: actions.class.php 6247 2007-12-01 03:25:13Z Carl.Vondrick $
 */
class ##MODULE_NAME##Actions extends BasesfLuceneActions
{
  /**
   * Returns an instance of sfLucene configured for this environment.
   */
  protected function getLuceneInstance()
  {
    return ##CALLABLE##;
  }
}
