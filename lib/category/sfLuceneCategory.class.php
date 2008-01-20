<?php
/*
 * This file is part of the sfLucenePlugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This manages and represents a category in the index.
 * @package    sfLucenePlugin
 * @subpackage Category
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */

class sfLuceneCategory
{
  protected $holder;
  protected $name;
  protected $count = 0;

  public function __construct($holder, $name, $count = 0)
  {
    if (!($holder instanceof sfLuceneCategories))
    {
      throw new sfLuceneException('Holder must be an instance of sfLuceneCategories');
    }

    $this->holder = $holder;
    $this->name = $name;
    $this->count = $count;
  }

  public function __toString()
  {
    return $this->name;
  }

  public function add($c = 1)
  {
    $this->count += $c;

    $this->holder->flagAsModified();

    return $this;
  }

  public function subtract($c = 1)
  {
    $this->count -= $c;

    $this->holder->flagAsModified();

    return $this;
  }

  public function setCount($c)
  {
    if ($this->count != $c)
    {
      $this->count = $c;

      $this->holder->flagAsModified();
    }

    return $this;
  }

  public function getHolder()
  {
    return $this->holder;
  }

  public function getCount()
  {
    return $this->count;
  }

  public function getName()
  {
    return $this->name;
  }

  public function worthSaving()
  {
    return $this->count > 0;
  }

  public function getPhp()
  {
    $name = str_replace('\'', '\\\'', $this->name);
    $count = (int) $this->count;

    return "\$categories['$name'] = $count;";
  }
}