<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 * (c) 2009 - Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Some standard tools for the sfLucene package.
 *
 * @package    sfLucenePlugin
 * @subpackage Utilities
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version SVN: $Id$
 */

class sfLuceneToolkit
{
  /**
   * Returns an instance of Lucene that is supposed to be used for this app.
   */
  static public function getApplicationInstance($culture = null)
  {
    $name = sfConfig::get('app_lucene_index', null);

    if (!$name)
    {
      $possible = sfLucene::getAllNames();

      $name = current($possible);
    }

    if (!$name)
    {
      throw new sfLuceneException('A index to use could not be resolved');
    }

    return sfLucene::getInstance($name, $culture);
  }
}