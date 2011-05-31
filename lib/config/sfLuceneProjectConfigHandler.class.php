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
 * @package    sfLucenePlugin
 * @subpackage Config
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
class sfLuceneProjectConfigHandler extends sfYamlConfigHandler
{
  /**
  * Builds cache file
  */
  public function execute($config)
  {
    return $this->buildConfig( $this->prepareConfig($config) );
  }

  /**
  * Given a well formed array of the config, it builds the cache output.
  */
  protected function buildConfig($config)
  {
    $retval = "<?php\n".
                "// auto-generated by " . __CLASS__ . "\n".
                "// date: %s";
    $retval = sprintf($retval, date('Y/m/d H:i:s'));

    $retval .= "\n\n\$config = array();\n\n";

    foreach ($config as $name => $values)
    {
      $retval .= "\n\n// processing  $name now...\n";

      $retval .= sprintf("\$config['%s'] = %s;\n", $name, var_export($values, true));
    }

    return $retval;
  }

  protected function prepareConfig($config)
  {
    $config = $this->parseYamls($config);
    $retval = array();

    foreach ($config as $name => $values)
    {
      try
      {
        $retval[$name] = $this->prepareOneConfig($values);
      }
      catch (Exception $e)
      {
        throw new sfConfigurationException('Error processing Lucene index "' . $name . '": ' . $e->getMessage());
      }
    }

    return $retval;
  }

  /**
  * Prepares the config files to be well formed.
  */
  protected function prepareOneConfig($config)
  {
    if (isset($config['models']))
    {
      foreach ($config['models'] as $model => &$model_config)
      {
        if (isset($model_config['fields']))
        {
          foreach ($model_config['fields'] as &$field)
          {
            $transform = null;
            $boost = null;

            $type = null;

            if (is_array($field))
            {
              $type         = isset($field['type']) ? $field['type'] : null;
              $stored       = isset($field['stored']) ? $field['stored'] : null;
              $indexed      = isset($field['indexed']) ? $field['indexed'] : true;
              $multi_valued = isset($field['multiValued']) ? $field['multiValued'] : null;
              $required     = isset($field['required']) ? $field['required'] : null;
              $boost        = isset($field['boost']) ? $field['boost'] : null;
              $transform    = isset($field['transform']) ? $field['transform'] : null;
              $default      = isset($field['default']) ? $field['default'] : null;
              $alias        = isset($field['alias']) ? $field['alias'] : null;
              $omitNorms    = isset($field['omitNorms']) ? $field['omitNorms'] : ($boost !== null ? false : null);
            }
            elseif (empty($field))
            {
              $type = null;
            }
            elseif (is_string($field))
            {
              $type = $field;
            }

            $type         = $type ? $type : 'text';
            $boost        = $boost ? $boost : null;
            $transform    = $transform || count($transform) ? $transform : null;
            $multi_valued = $multi_valued ? $multi_valued : false;
            $indexed       = $indexed ? $indexed : false;
            $stored       = $stored ? $stored : false;
            $required     = $required ? $required : false;
            $omitNorms    = $omitNorms === null ? null : $omitNorms;
            
            $field = array(
              'type'      => $type,
              'boost'     => $boost,
              'transform' => $transform,
              'multiValued' => $multi_valued,
              'required'  => $required,
              'indexed'    => $indexed,
              'stored'    => $stored,
              'default'   => $default,
              'alias'     => $alias,
              'omitNorms' => $omitNorms,
            );
          }
        }
        else
        {
          $model_config['fields'] = array();
        }

        if (!isset($model_config['partial']))
        {
          $model_config['partial'] = null;
        }

        if (!isset($model_config['callback']))
        {
          $model_config['callback'] = null;
        }

        if (!isset($model_config['route']))
        {
          $model_config['route'] = null;
        }

        if (!isset($model_config['indexer']))
        {
          $model_config['indexer'] = null;
        }

        if (!isset($model_config['title']))
        {
          $model_config['title'] = null;
        }

        if (!isset($model_config['description']))
        {
          $model_config['description'] = null;
        }

        if (!isset($model_config['peer']))
        {
          $model_config['peer'] = $model . 'Table';
        }

        if (!isset($model_config['rebuild_limit']))
        {
          $model_config['rebuild_limit'] = 50;
        }
        
        if (!isset($model_config['validator']))
        {
          $model_config['validator'] = null;
        }

        if (!isset($model_config['categories']))
        {
          $model_config['categories'] = array();
        }
      }
    }
    else
    {
      $config['models'] = array();
    }

    $encoding = isset($config['index']['encoding']) ? $config['index']['encoding'] : 'utf-8';
    $cultures = isset($config['index']['cultures']) ? $config['index']['cultures'] : array(sfConfig::get('sf_default_culture'));
    $mb_string = isset($config['index']['mb_string']) ? $config['index']['mb_string'] : false;
    $param = isset($config['index']['param']) ? $config['index']['param'] : array();
    
    $host = isset($config['index']['host']) ? $config['index']['host'] : 'localhost';
    $port = isset($config['index']['port']) ? $config['index']['port'] : '8983';
    $base_url = isset($config['index']['base_url']) ? $config['index']['base_url'] : '/solr';

    $localsolr_enabled = isset($config['index']['localsolr']['enabled']) ? $config['index']['localsolr']['enabled'] : false;
    $localsolr_latitude_field = isset($config['index']['localsolr']['latitude_field']) ? $config['index']['localsolr']['latitude_field'] : 'lat';
    $localsolr_longitude_field = isset($config['index']['localsolr']['longitude_field']) ? $config['index']['localsolr']['longitude_field'] : 'lng';

    $config['index'] = array(
      'encoding' => $encoding,
      'cultures' => $cultures,
      'mb_string' => (bool) $mb_string,
      'param' => $param,
      'host' => $host,
      'port' => $port,
      'base_url' => $base_url,
      'localsolr' => array(
        'enabled' => $localsolr_enabled,
        'latitude_field' => $localsolr_latitude_field,
        'longitude_field' => $localsolr_longitude_field
      )
    );

    // process factories...
    if (!isset($config['factories']))
    {
      $config['factories'] = array();
    }
    if (!isset($config['factories']['indexers']))
    {
      $config['factories']['indexers'] = array();
    }
    if (!isset($config['factories']['results']))
    {
      $config['factories']['results'] = array();
    }

    return $config;
  }
}
