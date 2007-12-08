<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Some standard tools for the sfLucene package.
 *
 * @package    sfLucenePlugin
 * @subpackage Utilities
 * @author     Carl Vondrick <carlv@carlsoft.net>
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

  /**
   * Returns an array of the index paths to be removed by the garbage cleanup routine.
   */
  static public function getDirtyIndexRemains()
  {
    $location = sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . 'index';
    $length = strlen($location) + 1;

    $config = sfLucene::getConfig();

    $remove = array();
    $namesRemoved = array();

    foreach (sfFinder::type('dir')->mindepth(0)->maxdepth(0)->in($location) as $directory)
    {
      $name = substr($directory, $length);

      if (!isset($config[$name]))
      {
        $namesRemoved[] = $name;
        $remove[] = $directory;
      }
    }

    foreach (sfFinder::type('dir')->mindepth(1)->maxdepth(1)->in($location) as $directory)
    {
      $interested = substr($directory, $length);

      list($name, $culture) = explode('/', $interested);

      if (!in_array($name, $namesRemoved) && !in_array($culture, $config[$name]['index']['cultures']))
      {
        $remove[] = $directory;
      }
    }

    return $remove;
  }

  /**
    * Loads the Zend libraries. This method *must* be called before
    * you use a Zend library, otherwise the autoloader will not be able to find it!
  */
  static public function loadZend()
  {
    static $setup;

    if ($setup !== true)
    {
      $vendor = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor';
      $vendor = sfConfig::get('app_lucene_zend_location', $vendor);

      $lucene = $vendor . DIRECTORY_SEPARATOR . 'Zend' . DIRECTORY_SEPARATOR . 'Search' . DIRECTORY_SEPARATOR . 'Lucene.php';

      if (!is_readable($lucene))
      {
        $error = 'Could not open Zend_Search_Lucene for inclusion. ';

        if (file_exists($lucene))
        {
          $error .= 'Check permissions on "' . $lucene . '"';
        }
        else
        {
          $error .= 'Make sure a Zend folder containing the Zend framework is in "' . $vendor . '"';
        }

        throw new sfLuceneException($error);
      }

      // let PHP find the Zend libraries.
      set_include_path(get_include_path() . PATH_SEPARATOR . $vendor);

      require_once $lucene;

      $setup = true;
    }
  }
}