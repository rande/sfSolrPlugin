<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This manages and represents a bunch of categories.
 * @package    sfLucenePlugin
 * @subpackage Category
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */

class sfLuceneCategories
{
  /**
   * Holds the writer
   */
  protected $writer;

  /**
   * Holds all the instances of the categories
   */
  protected $categories = array();

  /**
   * If true, the list has been modified.
   */
  protected $modified = false;

  /**
   * Singleton constructor
   * @param sfLucene $search The search instance to tie to
   * @param sfLuceneStorage $writer The writer (optional)
   */
  public function __construct($search, $writer = null)
  {
    if (!($search instanceof sfLucene))
    {
      throw new sfLuceneException('Search must be an instance of sfLucene');
    }

    if ($writer == null)
    {
      $writer = new sfLuceneStorageFilesystem(sfConfig::get('sf_cache_dir') . DIRECTORY_SEPARATOR . $search->getParameter('index_location') . DIRECTORY_SEPARATOR . 'categories.php');
    }

    if (!($writer instanceof sfLuceneStorage))
    {
      throw new sfLuceneException('Writer must be an instance of sfLuceneStorage');
    }

    $this->writer = $writer;

    $this->load();
  }

  public function __destruct()
  {
    $this->save();
  }

  /**
   * Returns an instance of the category object.  If it does not exist, it creates
   * an empty one.
   */
  public function getCategory($category)
  {
    if (!isset($this->categories[$category]))
    {
      $this->categories[$category] = new sfLuceneCategory($this, $category);
    }

    return $this->categories[$category];
  }


  public function getAllCategories()
  {
    return $this->categories;
  }

  public function flagAsModified()
  {
    $this->modified = true;
  }

  public function isModified()
  {
    return $this->modified;
  }

  public function load()
  {
    $data = $this->writer->read();

    if ($data)
    {
      eval($data);

      if (!isset($categories) || !is_array($categories))
      {
        throw new sfLuceneException('Categories file found, but it was corrupt.');
      }

      $this->categories = array();

      foreach ($categories as $category => $count)
      {
        $this->categories[$category] = new sfLuceneCategory($this, $category, $count);
      }
    }

    return $this;
  }

  /**
   * Writes the list to disk
   */
  public function save()
  {
    if ($this->modified)
    {
      // build data
      $data = "\$categories = array();";

      foreach ($this->categories as $category)
      {
        if ($category->worthSaving())
        {
          $data .= $category->getPhp();
        }
      }

      $this->writer->write($data);

      $this->modified = false;
    }

    return $this;
  }

  /**
   * Clears the entire list
   */
  public function clear()
  {
    $this->categories = array();

    $this->modified = true;

    return $this;
  }
}