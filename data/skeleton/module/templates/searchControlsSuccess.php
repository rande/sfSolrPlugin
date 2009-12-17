<?php
/**
 * @package sfLucenePlugin
 * @subpackage Module
 * @author Carl Vondrick <carl@carlsoft.net>
 * @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version SVN: $Id: searchControls.php 7108 2008-01-20 07:44:42Z Carl.Vondrick $
 */
?>

<?php use_helper('sfLucene', 'I18N') ?>

<h2><?php echo __('Search', null, 'sfLucene') ?></h2>
<p><?php echo __('Use our search engine to pinpoint exactly what you need on our site.', null, 'sfLucene') ?></p>

<?php include_partial('##MODULE_NAME##/controls', array(
  'form' => $form
)) ?>