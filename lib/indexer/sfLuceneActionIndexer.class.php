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
 * Module/action indexing engine.
 * @package sfLucenePlugin
 * @subpackage Indexer
 * @author Carl Vondrick
 * @version SVN: $Id$
 */
class sfLuceneActionIndexer extends sfLuceneIndexer
{
  private $module, $action, $properties;

  public function __construct($search, $module, $action)
  {
    parent::__construct($search);

    $this->module = $module;
    $this->action = $action;

    $config = sfConfig::get('sf_app_dir') . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR  . $module . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'search.yml';

    include(sfConfigCache::getInstance()->checkConfig($config));

    if (!isset($actions[$this->getSearch()->getParameter('name')][$action]))
    {
      throw new sfLuceneIndexerException('Specified action is not registered for indexing');
    }

    $this->properties = $actions[$this->getSearch()->getParameter('name')][$action];
  }

  protected function shouldIndex()
  {
    return true;
  }

  /**
  * Deletes the provided action
  */
  public function delete()
  {
    extract($this->getActionProperties());

    if ($this->deleteGuid( $this->getGuid($params)))
    {
      $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'indexer.log', array('Deleted action "%s" of module "%s" from index', $action, $module)));
    }

    return $this;
  }

  /**
  * Inserts the provided action
  */
  public function insert()
  {
    if (!$this->shouldIndex())
    {
      return;
    }
    
    throw new sfException(__CLASS__.' not implemented');

    extract($this->getActionProperties());

    $output = $this->executeAction($params);

    $content = $output->getContent();

    $doc = Zend_Search_Lucene_Document_Html::loadHtml($content);

    $doc->addField('sfl_title', $output->getLastTitle(), 2);
    $doc->addField('sfl_uri', $this->getUri($params));
    $doc->addField('sfl_description', $content);
    $doc->addField('sfl_type', 'action');

    $categories = $this->getActionCategories();

    if (count($categories))
    {
      foreach ($categories as $category)
      {
        $this->addCategory($category);
      }

      $doc->addField('sfl_category', implode(', ', $categories));
    }

    $doc->addField('sfl_categories_cache', serialize($categories));

    $guid = $this->getGuid($params);

    $this->addDocument($doc, $guid, 'action');

    $this->getSearch()->getEventDispatcher()->notify(new sfEvent($this, 'indexer.log', array('Inserted action "%s" of module "%s" to index', $this->getAction(), $this->getModule())));

    return $this;
  }

  protected function getModule()
  {
    return $this->module;
  }

  protected function getAction()
  {
    return $this->action;
  }

  protected function getActionProperties()
  {
    $properties = $this->properties;
    $retval = array();

    $retval['authenticated']    = isset($properties['security']['authenticated'])   ? $properties['security']['authenticated']    : false;
    $retval['credentials']      = isset($properties['security']['credentials'])     ? $properties['security']['credentials']      : array();
    $retval['params']           = isset($properties['params'])                      ? $properties['params']                       : array();
    $retval['layout']           = isset($properties['layout'])                      ? $properties['layout']                       : false;

    return $retval;
  }

  protected function getActionCategories()
  {
    $properties = $this->getActionProperties();

    if (!isset($properties['categories']))
    {
      return array();
    }

    $categories = $properties['categories'];

    if (!is_array($categories))
    {
      $categories = array($categories);
    }

    return $categories;
  }

  /**
  * Returns the URI to the action
  */
  protected function getUri($params)
  {
    $uri = $this->getModule() . '/' . $this->getAction();

    if (count($params))
    {
      $url .= '?' . http_build_query($params);
    }

    return $uri;
  }

  /**
  * Retrives the guid for an action
  */
  protected function getGuid($params)
  {
    return parent::getGuid($this->getUri($params));
  }

  /**
  * Executes an action and returns the response content, given the parameters.
  * @param string $module The module
  * @param string $action The action
  * @param array $request The request parameters
  * @param bool $authenticated If true, the user is authenticated.  If false, the user is not.
  * @param array $credentials The credentials the user has
  * @param bool $layout If true, the response is decorated by the layout.  If false, the response is not.
  */
  protected function executeAction($request = array())
  {
    extract($this->getActionProperties());

    try
    {
      $browser = new sfLuceneBrowser($this->getModule(), $this->getAction());
      $browser->setParameters($request);
      $browser->setAuthentication($authenticated);
      $browser->setCredentials($credentials);
      $browser->setLayout($layout);
      $browser->setMethod('GET');
      $browser->setCulture($this->getSearch()->getParameter('culture'));

      return $browser;
    }
    catch (Exception $e)
    {
      throw new sfLuceneIndexerException(sprintf('Error during action "%s/%s" execution: [%s]', $module, $action, $e->getMessage()));
    }
  }
}
