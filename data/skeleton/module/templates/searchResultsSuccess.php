<?php
/**
 * @package sfLucenePlugin
 * @subpackage Module
 * @author Carl Vondrick <carl@carlsoft.net>
 * @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version SVN: $Id: searchResults.php 7108 2008-01-20 07:44:42Z Carl.Vondrick $
 */
?>

<?php use_helper('sfLucene', 'I18N') ?>

<h2><?php echo __('Search Results', null, 'sfLucene') ?></h2>

<p><?php echo __('The following results matched your query:', null, 'sfLucene') ?></p>

<ol start="<?php echo $pager->getFirstIndice() ?>" class="search-results">
  <?php foreach ($pager->getResults() as $result): ?>
    <li>
      <?php include_partial($result->getInternalPartial(), array(
        'result' => $result,
        'query' => $query
      )); ?>
    </li>
  <?php endforeach ?>
</ol>

<?php include_partial('##MODULE_NAME##/pagerNavigation', array(
  'pager' => $pager,
  'form' => $form,
  'url'  => '##MODULE_NAME##/search',
  'radius' => sfConfig::get('app_lucene_pager_radius', 5)
)); ?>

<?php include_partial('##MODULE_NAME##/controls', array('form' => $form)) ?>