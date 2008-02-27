<?php
/*
 * This file is part of the limeade package
 * (c) 2007 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
  * limeade_sf_i18n provides a quick and easy way to setup and tear down sfI18N
  *
  * @author Carl Vondrick <carl@carlsoft.net>
  * @package limeade
  * @version SVN: $Id: limeade_sf_i18n.php 6956 2008-01-05 22:54:18Z Carl.Vondrick $
  */
class limeade_sf_i18n
{
  public $app, $culture;

  public function __construct(limeade_sf_app $app)
  {
    $this->app = $app;
  }

  public function setup($culture, $debug = false)
  {
    $this->culture = $culture;

    $this->app->limeade->config()->add(array(
      'sf_i18n_default_culture' => $culture,
      'sf_i18n_source' => 'XLIFF',
      'sf_i18n_debug' => $debug,
      'sf_i18n_untranslated_prefix' => '[T]',
      'sf_i18n_untranslated_suffix' => '[/T]',
    ));

    $this->app->limeade->config()->set('sf_i18n', true);
    $this->app->context->set('i18n', new sfI18N($this->app->context->getEventDispatcher()));

    return $this;
  }

  public function reword($source, $target)
  {
    throw new limeade_exception('Not implemented');
  }

  public function unword($source)
  {
    throw new limeade_exception('Not implemented');
  }

  public function teardown()
  {
    $this->app->limeade->config()->set('sf_i18n', false);
    $this->app->context->set('i18n', null);

    $this->culture = null;

    return $this;
  }

  public function get()
  {
    return $this->app->context->getI18N();
  }
}