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
  * @version SVN: $Id: unit.php 6686 2007-12-23 21:13:28Z Carl.Vondrick $
  */

class limeade_lucene
{
  public $limeade;

  public $sandbox_dir, $data_dir, $config_dir;

  public function __construct(limeade_sf $limeade)
  {
    $this->limeade = $limeade;
  }

  public function configure()
  {
    $this->sandbox_dir = dirname(dirname(__FILE__)) . '/sandbox';
    $this->data_dir = dirname(dirname(__FILE__)) . '/data';
    $this->config_dir = dirname(__FILE__) . '/../data/config';

    $this->limeade->config()->set('sf_config_dir_name', $this->config_dir);
    $this->limeade->config()->set('sf_data_dir', $this->sandbox_dir . '/data');

    return $this;
  }

  public function clear_sandbox()
  {
    sfToolkit::clearDirectory($this->sandbox_dir);

    return $this;
  }

  public function load_models()
  {
    $modelDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'model';

    $files = sfFinder::type('file')->name('*.php')->ignore_version_control()->in($modelDir);

    foreach ($files as $file)
    {
      require_once $file;
    }

    return $this;
  }
}