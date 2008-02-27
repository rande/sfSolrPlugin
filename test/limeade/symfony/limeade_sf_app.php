<?php
/*
 * This file is part of the limeade package
 * (c) 2007 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * limeade_sf provides access to various tools that require an application bootstrap
  *
  * @author Carl Vondrick <carl@carlsoft.net>
  * @package limeade
  * @version SVN: $Id: limeade_sf_app.php 6956 2008-01-05 22:54:18Z Carl.Vondrick $
  */
class limeade_sf_app
{
  public $limeade, $app, $env, $debug, $context;

  public function __construct(limeade_sf $limeade, $app = 'frontend', $env = 'test', $debug = true)
  {
    $this->limeade = $limeade;
    $this->app = $app;
    $this->env = $env;
    $this->debug = $debug;
  }

  public function boot()
  {
    if (!defined('SF_ROOT_DIR'))
    {
      define('SF_ROOT_DIR', $this->limeade->project_root);
    }
    elseif (!defined('SF_APP'))
    {
      define('SF_APP', $this->app);
      define('SF_ENVIRONMENT', $this->env);
      define('SF_DEBUG', $this->debug);

      require_once SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';

      $this->context = sfContext::getInstance();
    }
    else
    {
      throw new limeade_exception('Symfony already configured');
    }

    return $this;
  }

  public function event($listen, $unit_msg, $num = 1)
  {
    return new limeade_sf_event($this->limeade, $this->context->getEventDispatcher(), $listen, $unit_msg, $num);
  }

  public function i18n()
  {
    return new limeade_sf_i18n($this);
  }

  public function cswap($file)
  {
    return new limeade_sf_cswap($this, $file);
  }
}