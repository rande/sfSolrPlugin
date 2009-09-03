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
 * Base class for all indexing engines.
 * @package sfLucenePlugin
 * @subpackage Indexer
 * @author Carl Vondrick
 * @version SVN: $Id$
 */
abstract class sfLuceneIndexer
{
  private $search = null;


  public function __construct($search)
  {
    if (!($search instanceof sfLucene))
    {
      throw new sfLuceneIndexerException('Search must be an instance of sfLucene');
    }

    $this->search = $search;

    $search->configure();
  }

  /**
   * Inserts the record into the index.
   */
  abstract public function insert();

  /**
   * Deletes the record from the index
   */
  abstract public function delete();

  /**
   * Verifies if this record should be indexed.
   * If returns true, indexing proceeds.  If false, indexing is skipped.
   */
  abstract protected function shouldIndex();

  /**
   * Saves the record.
   */
  public function save()
  {
    $this->delete();
    $this->insert();

    return $this;
  }

  /**
  * Gets the search instance.
  */
  protected function getSearch()
  {
    return $this->search;
  }

  /**
   * Return the context that the search is bound to
   */
  protected function getContext()
  {
    return $this->search->getContext();
  }

  /**
  * Searches the index for anything with that guid and will delete it, while
  * taking care to update categories cache.
  *
  * @param string $guid The guid to search for
  * @return int The number of documents deleted
  */
  protected function deleteGuid($guid)
  {

    $this->getSearch()->getLucene()->deleteById($guid);

    // commit changes
    $this->getSearch()->commit();

    // TODO  : return the correct number of hits
    return 1;
  }

  /**
  * Adds a document to the index while attaching a GUID
  */
  protected function addDocument(Apache_Solr_Document $document, $guid)
  {
    $document->addField('sfl_guid', $guid);

    $timer = sfTimerManager::getTimer('Solr Search Lucene');
    $this->getSearch()->getLucene()->addDocument($document);
    $timer->addTime();
  }

  /**
   * Adds a category to the cache
   * @param string $category The category name
   * @param int $c How many references (defaults to 1)
   */
  protected function addCategory($category, $c = 1)
  {
    $this->getSearch()->getCategoriesHarness()->getCategory($category)->add($c);
  }

  /**
   * Removes a category from the cache
   * @param string $category The category name
   * @param int $c How many references (defaults to 1)
   */
  protected function removeCategory($category, $c = 1)
  {
    $this->getSearch()->getCategoriesHarness()->getCategory($category)->subtract($c);
  }

  /**
   * Action to retrieve the GUID for the input
   */
  protected function getGuid($input)
  {
    return md5($input) . sha1($input);
  }

  /**
   * Factory to obtain the search fields.
   * @param string $field The type of field
   * @param string $name To name to use
   * @param string $contents The contents for the field to have.
   * @return mixed The requested type.
   */
  protected function getLuceneField($field, $name, $contents)
  {
    
    throw new sfException('sfLuceneIndexer::getLuceneField : not available anymore');
  }
}
