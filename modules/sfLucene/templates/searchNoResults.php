<?php
/**
 * @package sfLucenePlugin
 * @subpackage Module
 * @author Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
?>

<?php use_helper('sfLucene', 'I18N') ?>

<h2><?php echo __('No Results Found') ?></h2>
<p><?php echo __('We could not find any results with the search term you provided.') ?></p>

<?php include_search_controls($form) ?>