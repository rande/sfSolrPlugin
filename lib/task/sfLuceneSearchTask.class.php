<?php

/**
 * Performs a research in the Solr index
 *
 * @package sfSolrPlugin
 * @subpackage task
 * @author Gordon Franke <info@nevalon.de>
 */
class sfLuceneSearchTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    // add your own arguments here
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('query', sfCommandArgument::REQUIRED, 'The query string'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', null),
      new sfCommandOption('index', null, sfCommandOption::PARAMETER_REQUIRED, 'The index name', null),
      new sfCommandOption('culture', null, sfCommandOption::PARAMETER_REQUIRED, 'The culture string', null),
      new sfCommandOption('start', null, sfCommandOption::PARAMETER_REQUIRED, 'The search offset', 0),
      new sfCommandOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'The search limit', 10),
    ));

    $this->namespace        = 'lucene';
    $this->name             = 'search';
    $this->briefDescription = 'search in solr';
    $this->detailedDescription = <<<EOF
The [search|INFO] task executes a Solr search and displays the result
Call it with:

  [php symfony lucene:search application query|INFO]

You can also add additional parameters:

  [php symfony lucene:search application query --index="MyIndex" --culture="de" --start=5 --limit=5|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $config = sfLucene::getConfig();
    $query  = $arguments['query'];

    // check and reduce indexes
    if ($options['index'] !== null)
    {
      if (!in_array($options['index'], array_keys($config)))
      {
        throw new Exception('Index %s not exist', $options['index']);
      }
      $config = array($config[$options['index']]);
    }

    // build the lucene criteria
    $criteria = sfLuceneCriteria::newInstance()
      ->addPhrase($query)
      ->setOffset($options['start'])
      ->setLimit($options['limit']);

    // walk over the indexes
    foreach($config as $index => $indexConfig)
    {
      // check culture
      $cultures = $indexConfig['index']['cultures'];
      if ($options['culture'] !== null)
      {
        if (!in_array($options['culture'], $cultures))
        {
          //TODO: change to log error
          throw new Exception(sprintf('Culture %s is not configurate for index %s', $options['culture'], $index));
        }
        $cultures = array($options['culture']);
      }

      $this->log(sprintf('search for `%s` from %u to %u', $query, $options['start'], $options['limit']));

      // walk over the cultures
      foreach($cultures as $culture)
      {
        // get lucene instance and retrieve the results
        $results = sfLucene::getInstance($index, $culture)
          ->friendlyFind($criteria);

        $this->log(sprintf('found %u results in index `%s` with culture `%s`', count($results), $index, $culture));

        foreach ($results as $result)
        {
          $this->logSection('result ', sprintf('%s %s (%u%%)', $result->getInternalModel(), $result->getInternalTitle(), $result->getScore()));
        }
      }
    }
  }
}
