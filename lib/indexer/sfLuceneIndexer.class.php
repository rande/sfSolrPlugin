<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
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
    if ($this->search->getParameter('delete_lock'))
    {
      // index has told us not to delete, so abort
      return 0;
    }

    $term = $this->getLuceneField('index term', 'sfl_guid', $guid);
    $query = new Zend_Search_Lucene_Search_Query_Term($term);

    $hits = $this->getSearch()->find($query);

    // loop through each document that has this guid
    foreach ($hits as $hit)
    {
      // build categories that this document has
      $categories = unserialize($hit->sfl_categories_cache);

      // delete each category that this document references
      foreach ($categories as $category)
      {
        $this->removeCategory($category);
      }

      // delete item from index
      $this->getSearch()->getLucene()->delete($hit->id);
    }

    // commit changes
    $this->getSearch()->commit();

    return count($hits);
  }

  /**
  * Adds a document to the index while attaching a GUID
  */
  protected function addDocument(Zend_Search_Lucene_Document $document, $guid)
  {
    $document->addField($this->getLuceneField('keyword', 'sfl_guid', $guid));

    $timer = sfTimerManager::getTimer('Zend Search Lucene');
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
    $this->getSearch()->getCategories()->getCategory($category)->add($c)->getHolder()->save();
  }

  /**
   * Removes a category from the cache
   * @param string $category The category name
   * @param int $c How many references (defaults to 1)
   */
  protected function removeCategory($category, $c = 1)
  {
    $this->getSearch()->getCategories()->getCategory($category)->subtract($c)->getHolder()->save();
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
    switch (strtolower($field))
    {
      case 'keyword':
        return Zend_Search_Lucene_Field::Keyword($name, $contents, $this->getSearch()->getParameter('encoding'));
      case 'unindexed':
        return Zend_Search_Lucene_Field::UnIndexed($name, $contents, $this->getSearch()->getParameter('encoding'));
      case 'binary':
        return Zend_Search_Lucene_Field::Binary($name, $contents);
      case 'text':
        return Zend_Search_Lucene_Field::Text($name, $contents, $this->getSearch()->getParameter('encoding'));
      case 'unstored':
        return Zend_Search_Lucene_Field::UnStored($name, $contents, $this->getSearch()->getParameter('encoding'));
      case 'index term':
        return new Zend_Search_Lucene_Index_Term($contents, $name);
      default:
        throw new sfLuceneIndexerException(sprintf('Unknown field "%s" in factory', $field));
    }
  }
}
