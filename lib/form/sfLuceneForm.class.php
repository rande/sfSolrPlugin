<?php
/*
 * This file is part of the sfLucenePLugin package
 * (c) 2007 Carl Vondrick <carlv@carlsoft.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base class for the simple form.  If you wish to overload this, please use
 * sfLuceneSimpleForm instead.
 *
 * This form represents the simple form that is displayed on the standard search
 * interface.
 *
 * @package    sfLucenePlugin
 * @subpackage Form
 * @author     Carl Vondrick <carlv@carlsoft.net>
 * @version SVN: $Id$
 */

abstract class sfLuceneForm extends sfForm
{
  public function setCategories($categories = array())
  {
    if (!is_array($categories))
    {
      throw new sfLuceneException('Array of categories must be just that: an array (' . gettype($categories) . ' given');
    }

    $this->setOption('categories', $categories);

    $this->setup();
    $this->configure();
  }

  public function getCategories()
  {
    return $this->getOption('categories', array());
  }

  public function hasCategories()
  {
    return count($this->getOption('categories', array())) > 0;
  }
}
