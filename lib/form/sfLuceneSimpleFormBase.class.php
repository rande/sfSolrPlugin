<?php
/*
 * This file is part of the sfLucenePLugin package
 * (c) 2007 - 2008 Carl Vondrick <carl@carlsoft.net>
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
 * @author     Carl Vondrick <carl@carlsoft.net>
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version SVN: $Id$
 */

abstract class sfLuceneSimpleFormBase extends sfLuceneForm
{
  /**
   * This overriden constructor looks useless, but it is important: it specifies
   * not to use a CRSF Secret by default!
   */
  public function __construct($defaults = array(), $options = array(), $CSRFSecret = false)
  {
    parent::__construct($defaults, $options, $CSRFSecret);
  }

  /**
   * Setup the form.  To overload, you should use ->configure()
   */
  public function setup()
  {
    $widgetSchema = new sfWidgetFormSchema(
    array( // fields
      'query' => new sfWidgetFormInput()
    ),
    array( // options
    ),
    array( // attributes
    ),
    array( //labels
    ),
    array( // helps
    )
    );

    $widgetSchema->addFormFormatter('sfLuceneSimple', new sfLuceneWidgetFormatterSimple($widgetSchema));
    $widgetSchema->setFormFormatterName('sfLuceneSimple');
    $widgetSchema->setNameFormat('form[%s]');

    $validatorSchema = new sfValidatorSchema(
    array( // fields
      'query' => new sfValidatorString(array('required' => true)),
      'page' => new sfValidatorInteger(array('required' => false, 'empty_value' => 1))
    ),
    array( //options
    ),
    array( // messages
    )
    );

    if ($this->hasCategories())
    {
      $widgetSchema['category'] = new sfWidgetFormSelect(array('choices' => $this->getCategories(), 'multiple' => false));

      $validatorSchema['category'] = new sfValidatorChoice(array('required' => false, 'choices' => $this->getCategories()));
    }

    $this->setWidgetSchema($widgetSchema);

    $this->setValidatorSchema($validatorSchema);
  }
}
