<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
* sfLucene bridges symfony and Solr together to instantly
* add a search engine to your application. Please see the README file for more.
*
* This class represents a Lucene index.  It is responsible for managing all the
* configurations for the index.  This is the primary means of communicating with
* the Sorl search engine.
*
* @author Carl Vondrick <carl@carlsoft.net>
* @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
* @package sfLucenePlugin
* @version SVN: $Id$
*/
class sfLucene
{
  const VERSION = '0.2-DEV';

  /**
   * Holds the internal dispatcher for this Lucene instance.
   */
  protected $dispatcher = null;

  /**
   * Holds the Lucene instance
   */
  protected $lucene = null;

  /**
   * Holds the indexer factory singleton
   */
  protected $indexerFactory = null;

  /**
   * Holds the categories singleton
   */
  protected $categoriesHarness = null;

  /**
   * Holds parameters for this lucene instance
   */
  protected $parameters = null;

  /**
  * Holder for the instances
  */
  static protected $instances = array();

  /**
  * Constructor, but seriously use getInstance as the constructor
  * because that maintains singletons
  * @param string $name The name of the index
  * @param string $culture The culture of the index
  * @param bool $rebuild If true, the index is erased before opening it.
  */
  protected function __construct($name, $culture)
  {
    $this->parameters = new sfParameterHolder();

    $this->setParameter('name', $name);
    $this->setParameter('culture', $culture);
    $this->setParameter('index_location', $name.'_'.$culture);

    $this->dispatcher = new sfEventDispatcher;

    $this->initialize();

    $this->setAutomaticMode();
    $this->configure();
  }

  /**
  * Public constructor.  This returns an instance of sfLucene configured to the specifications
  * of the search.yml files.
  *
  * @param string $name The name of the index
  * @param string $culture The culture of the index
  * 
  * @return sfLucene
  */
  static public function getInstance($name, $culture = null)
  {
    // attempt to guess the culture
    if (is_null($culture))
    {
      $culture = sfContext::getInstance()->getUser()->getCulture();
    }

    if (!isset(self::$instances[$name][$culture]))
    {
      if (!isset(self::$instances[$name]))
      {
        self::$instances[$name] = array();
      }

      self::$instances[$name][$culture] = new self($name, $culture);
    }

    return self::$instances[$name][$culture];
  }

  /**
   * Returns all the instances
   */
  static public function getAllInstances()
  {
    static $instances;

    if (!$instances)
    {
      $config = self::getConfig();

      $instances = array();

      foreach ($config as $name => $item)
      {
        foreach ($item['index']['cultures'] as $culture)
        {
          $instances[] = self::getInstance($name, $culture);
        }
      }
    }

    return $instances;
  }

  /**
  * Returns the name of every registered index.
  */
  static public function getAllNames()
  {
    return array_keys(self::getConfig());
  }

  /**
  * Returns all of the config.
  */
  static public function getConfig()
  {
    $context = sfContext::getInstance();
    
    require($context->getConfiguration()->getConfigCache()->checkConfig('config/search.yml'));

    if (!isset($config))
    {
      throw new sfLuceneException('Error loading configuration');
    }

    return $config;
  }

  public function getPublicName()
  {
    
    return $this->getParameter('name').' ('.$this->getParameter('culture').')'; 
  }
  
  /**
  * Loads the config for the search engine.
  */
  protected function initialize()
  {
    $config = self::getConfig();

    $holder = $this->getParameterHolder();

    if (!isset($config[$holder->get('name')]))
    {
      throw new sfLuceneException('The name of this index is invalid : '. $holder->get('name'));
    }

    $config = $config[$holder->get('name')];

    foreach (array('encoding', 'cultures' => 'enabled_cultures', 'stop_words', 'short_words', 'analyzer', 'case_sensitive', 'mb_string', 'host', 'port', 'base_url') as $key => $param)
    {
      
      if (is_int($key))
      {
        $holder->set($param, $config['index'][$param]);
      }
      else
      {
        
        $holder->set($param, $config['index'][$key]);
      }
    }
    
    $models = new sfParameterHolder();

    foreach ($config['models'] as $name => $model)
    {
      $fields = new sfParameterHolder();

      foreach ($model['fields'] as $field => $fieldProperties)
      {
        $fieldsData = new sfParameterHolder();
        $fieldsData->add($fieldProperties);

        $fields->set($field, $fieldsData);
      }

      $data = new sfParameterHolder();
      $data->set('fields', $fields);
      $data->set('partial', $model['partial']);
      $data->set('indexer', $model['indexer']);
      $data->set('title', $model['title']);
      $data->set('description', $model['description']);
      $data->set('peer', $model['peer']);
      $data->set('rebuild_limit', $model['rebuild_limit']);
      $data->set('validator', $model['validator']);
      $data->set('categories', $model['categories']);
      $data->set('route', $model['route']);

      $models->set($name, $data);
    }

    $holder->set('models', $models);

    $factories = new sfParameterHolder();
    $factories->add($config['factories']);
    $holder->set('factories', $factories);

    if (!in_array($holder->get('culture'), $holder->get('enabled_cultures')))
    {
      throw new sfLuceneException(sprintf('Culture "%s" is not enabled.', $holder->get('culture')));
    }
  }

  public function setParameter($key, $value)
  {
    $this->parameters->set($key, $value);
  }

  public function getParameter($key, $default = null)
  {
    return $this->parameters->get($key, $default);
  }

  public function getParameterHolder()
  {
    return $this->parameters;
  }

  /**
  * Returns the categories for this index.
  */
  public function getCategoriesHarness()
  {
    if ($this->categoriesHarness == null)
    {
      $this->categoriesHarness = new sfLuceneCategories($this);
    }

    return $this->categoriesHarness;
  }
  
  /**
  * Returns the lucene object
  * @return Zend_Search_Lucene
  */
  public function getLucene()
  {

    if ($this->lucene == null)
    {
      $solr = new Apache_Solr_Service(
        $this->getParameter('host'),
        $this->getParameter('port'),
        $this->getParameter('base_url').'/'.$this->getParameter('index_location')
      );
 
      if(!$solr->ping())
      {
        //throw new Exception('Search is not available right now.');
      }
      
      $this->lucene =  $solr;
    }

    return $this->lucene;
  }

  /**
  * Gets the specified indexer from the factory.
  * @return mixed An instance of the indexer factory.
  */
  public function getIndexerFactory()
  {
    if ($this->indexerFactory == null)
    {
      $this->indexerFactory = new sfLuceneIndexerFactory($this);
    }

    return $this->indexerFactory;
  }

  /**
   * Gets the sfLucene specific event dispatcher
   */
  public function getEventDispatcher()
  {
    return $this->dispatcher;
  }

  /**
  * Gets the context.  Right now, this exists for forward-compatability.
  * TODO: Remove singleton (depends on sfConfiguration)
  */
  public function getContext()
  {
    return sfContext::getInstance();
  }


  /**
   * Zend Search Lucene makes it awfully hard to have multiple Lucene indexes
   * open at the same time. This method combats that by configuring all the
   * static variables for this instance.
   */
  public function configure()
  {
    
  }

/**
  * Rebuilds the entire index.  This will be quite slow, so only run from the command line.
  */
  public function rebuildIndex()
  {
    $this->setBatchMode();

    $timer = sfTimerManager::getTimer('Solr Search Lucene Rebuild');

    $this->getEventDispatcher()->notify(new sfEvent($this, 'lucene.log', array('Rebuilding index...')));

    $this->getCategoriesHarness()->clear();

    $original = $this->getParameter('delete_lock', false);
    $this->setParameter('delete_lock', true); // tells the indexers not to bother deleting

    foreach ($this->getIndexerFactory()->getHandlers() as $handler)
    {
      $handler->rebuild();
    }

    $this->setParameter('delete_lock', $original);

    $this->getEventDispatcher()->notify(new sfEvent($this, 'lucene.log', array('Index rebuilt.')));

    $timer->addTime();

    return $this;
  }
  
  /**
  * Update only the index for one model
  *
  * if $offset and $limit are numeric then only the portion between
  * the offset and the limit are updated
  */
  public function rebuildIndexModel($model, $offset = null, $limit = null)
  {
    $this->setBatchMode();

    $timer = sfTimerManager::getTimer('Solr Search Lucene Rebuild');

    $this->getEventDispatcher()->notify(new sfEvent($this, 'lucene.log', array('Rebuilding index...')));

    foreach ($this->getIndexerFactory()->getHandlers() as $handler)
    {
      
      if(!$handler instanceof sfLuceneModelIndexerHandler)
      {
        
        continue;
      }
      
      $handler->rebuildModel($model, $offset, $limit);
    }

    $this->getEventDispatcher()->notify(new sfEvent($this, 'lucene.log', array('Index rebuilt.')));

    $timer->addTime();

    return $this;
  }

  /**
  * Determines the best mode to use
  */
  public function setAutomaticMode()
  {
    if ($this->getContext()->getController()->inCLI())
    {
      $this->setBatchMode();
    }
    else
    {
      $this->setInteractiveMode();
    }

    return $this;
  }

  /**
  * Puts the engine into batch mode, which makes it index much faster, but searching is
  * not as good.  Use this for large updates.
  */
  public function setBatchMode()
  {
    //$this->getLucene()->setMaxBufferedDocs(500);
    //$this->getLucene()->setMaxMergeDocs(PHP_INT_MAX);
    //$this->getLucene()->setMergeFactor(50);

    return $this;
  }

  /**
  * Puts the engine into interactive mode, which makes it search faster.  Use this for
  * normal circumstances.
  */
  public function setInteractiveMode()
  {
    //$this->getLucene()->setMaxBufferedDocs(10);
    //$this->getLucene()->setMaxMergeDocs(PHP_INT_MAX);
    //$this->getLucene()->setMergeFactor(10);

    return $this;
  }

  /**
  * Wrapper to optimize the index.
  */
  public function optimize()
  {
    $this->configure();

    $timer = sfTimerManager::getTimer('Solr Search Lucene Optimize');

    $this->getEventDispatcher()->notify(new sfEvent($this, 'lucene.log', array('Optimizing index...')));

    $this->getLucene()->optimize();

    $this->getEventDispatcher()->notify(new sfEvent($this, 'lucene.log', array('Index optimized.')));

    $timer->addTime();
  }

  /**
  * Wrapper for Lucene's count()
  */
  public function count()
  {
    return $this->getLucene()->count();
  }

  /**
  * Wrapper for Lucene's numDocs()
  *
  * @TODO : get the num of documents
  */
  public function numDocs()
  {
    
    return 'TODO';
    //return $this->getLucene()->numDocs();
  }

  /**
  * Wrapper for Lucene's commit()
  */
  public function commit()
  {
    $this->configure();

    $timer = sfTimerManager::getTimer('Solr Search Lucene Commit');

    $this->getEventDispatcher()->notify(new sfEvent($this, 'lucene.log', array('Committing changes...')));

    $this->getLucene()->commit();

    $this->getEventDispatcher()->notify(new sfEvent($this, 'lucene.log', array('Changes committed.')));

    $timer->addTime();
  }

  /**
  * Returns the size of the index, in bytes.
  */
  public function byteSize()
  {
    
    throw new sfException('not implemented');
  }

  /**
  * Returns the number of segments that the index is in.
  */
  public function segmentCount()
  {
    
    
    throw new sfException('not implemented');
  }

  /**
  * Wrapper for Lucene's find()
  * @param mixed $query The query
  * @return array The array of results
  */
  public function find($query)
  {
    $this->configure();

    $timer = sfTimerManager::getTimer('Solr Search Lucene Find');

    $sort = array();
    $scoring = null;

    if ($query instanceof sfLuceneCriteria)
    {
      foreach ($query->getSorts() as $sortable)
      {
        $sort[] = $sortable['field'];
        $sort[] = $sortable['type'];
        $sort[] = $sortable['order'];
      }
    }
    elseif (is_string($query))
    {
      $query = sfLuceneCriteria::newInstance($this)->addString($query)->getQuery();
    }

    try
    {
      $results = $this->getLucene()->search($query->getQuery(), $query->getOffset(), $query->getLimit());
    }
    catch (Exception $e)
    {
      $timer->addTime();

      throw $e;
    }

    $timer->addTime();

    return $results;
  }

  /**
  * Searches the index for the query and returns them with a symfony friendly interface.
  * @param mixed $query The query
  * @return sfLuceneResults The symfony friendly results.
  */
  public function friendlyFind($query)
  {
    return new sfLuceneResults($this->find($query), $this);
  }

  /**
   * Hook for sfMixer
   */
  public function __call($method, $arguments)
  {
    $event = $this->getEventDispatcher()->notifyUntil(new sfEvent($this, 'lucene.method_not_found', array('method' => $method, 'arguments' => $arguments)));

    if (!$event->isProcessed())
    {
      throw new sfException(sprintf('Call to undefined method %s::%s.', __CLASS__, $method));
    }

    return $event->getReturnValue();
  }

  /**
   * Removes this instance from the singleton.  Do not ever use except for
   * unit testing.
   */
  public function unlatch()
  {
    unset(self::$instances[$this->getParameter('name')][$this->getParameter('culture')]);
  }

  /**
   * Force the index to use a Lucene instance.  Do not ever use except for unit
   * testing.
   */
  public function forceLucene($lucene)
  {
    $this->lucene = $lucene;
  }

  /**
   * Force the index to use a indexer factory.  Do not ever use except for unit
   * testing.
   */
  public function forceIndexerFactory($factory)
  {
    $this->indexerFactory = $factory;
  }
}
