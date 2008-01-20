<?php
/**
 * @package sfLucenePlugin
 * @subpackage Module
 * @author Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
?>

<?php use_helper('sfLucene', 'I18N') ?>

<h2><?php echo __('Search') ?></h2>
<p><?php echo __('Use our search engine to pinpoint exactly what you need on our site.') ?></p>

<?php include_search_controls($form) ?>