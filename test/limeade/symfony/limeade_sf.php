<?php
/*
 * This file is part of the limeade package
 * (c) 2007 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * limeade_sf provides tools to access symfony for unit testing.
  *
  * @author Carl Vondrick <carl@carlsoft.net>
  * @package limeade
  * @version SVN: $Id: limeade_sf.php 6959 2008-01-06 03:42:17Z Carl.Vondrick $
  */
class limeade_sf extends limeade
{
  public $project_root, $sf_lib_dir, $sf_data_dir, $app;

  public $autoload = null;

  public function __construct(limeade_test $lime, $project_root = null)
  {
    if (!$project_root)
    {
      if (!defined('SF_ROOT_DIR'))
      {
        throw new limeade_exception('Please provide limeade_sf with a project root');
      }

      $project_root = SF_ROOT_DIR;
    }

    $this->project_root = $project_root;

    parent::__construct($lime);

    $this->loadConfig();
  }

  public function loadConfig()
  {
    $config = $this->project_root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

    if (!is_readable($config))
    {
      throw new limeade_exception('Cannot load symfony configuration file: ' . $config);
    }

    require $config;

    $this->sf_lib_dir = $sf_symfony_lib_dir;
    $this->sf_data_dir = $sf_symfony_data_dir;
  }

  /**
    * Bootstraps a symfony application
    * Note: This is not compatible with ->bootstrap()
    */
  public function bootstrap($app = 'frontend', $env = 'test', $debug = true)
  {
    if (!$this->app)
    {
      $this->app = new limeade_sf_app($this, $app, $env, $debug);
      $this->app->boot();

      $this->autoload = 'bootstrap';
    }

    return $this->app;
  }

  /**
    * Autoloads symfony
    * Note: This is not compatible with ->bootstrap()
    * Code adopted from command/sfSymfonyCommandApplication.class.php
    */
  public function autoload()
  {
    if ($this->autoload)
    {
      return;
    }

    $this->autoload = 'simple';

    require_once($this->sf_lib_dir . '/util/sfCore.class.php');
    require_once($this->sf_lib_dir . '/config/sfConfig.class.php');
    require_once($this->sf_lib_dir . '/util/sfSimpleAutoload.class.php');
    require_once($this->sf_lib_dir . '/util/sfToolkit.class.php');
    require_once($this->sf_lib_dir . '/util/sfFinder.class.php');

    sfConfig::add(array(
      'sf_symfony_lib_dir'  => $this->sf_lib_dir,
      'sf_symfony_data_dir' => $this->sf_lib_dir,
    ));

    // directory layout
    sfCore::initDirectoryLayout($this->sf_lib_dir);

    // include path
    set_include_path(
      sfConfig::get('sf_lib_dir').PATH_SEPARATOR.
      sfConfig::get('sf_app_lib_dir').PATH_SEPARATOR.
      sfConfig::get('sf_model_dir').PATH_SEPARATOR.
      get_include_path()
    );

    $cache = sfToolkit::getTmpDir().DIRECTORY_SEPARATOR.sprintf('limeade_autoload_%s.data', md5(__FILE__));

    $autoloader = sfSimpleAutoload::getInstance($cache);
    $autoloader->register();

    $finder = sfFinder::type('file')->ignore_version_control()->prune('test')->prune('vendor')->name('*.php');
    $autoloader->addFiles($finder->in(sfConfig::get('sf_symfony_lib_dir')));
    $autoloader->addFiles($finder->in($this->project_root));
    $autoloader->addDirectory(sfConfig::get('sf_root_dir').'/plugins');

    return $this;
  }

  /**
    * Clears the symfony cache
    */
  public function cc()
  {
    $cache = $this->project_root . '/cache/';

    if (is_dir($cache))
    {
      $cache = realpath($cache);

      sfToolkit::clearDirectory($cache);
    }
    else
    {
      throw new limeade_exception('Cannot find cache directory');
    }

    return $this;
  }

  public function config()
  {
    return new limeade_sf_config($this);
  }
}