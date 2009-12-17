<?php
/**
 * @package sfLucenePlugin
 * @subpackage Module
 * @author Carl Vondrick <carl@carlsoft.net>
 * @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version SVN: $Id: advancedControls.php 7108 2008-01-20 07:44:42Z Carl.Vondrick $
 */

?>

<?php use_helper('I18N') ?>

<h2><?php echo __('Advanced Search', null, 'sfLucene') ?></h2>

<form action="<?php echo url_for('##MODULE_NAME##/advanced') ?>" method="get">
  <fieldset>
    <legend><?php echo __('Search Terms', null, 'sfLucene') ?></legend>

    <table>
      <?php echo $form ?>
    </table>
  </fieldset>

  <input type="submit" value="<?php echo __('Search', null, 'sfLucene') ?>" name="commit" accesskey="s" />
  <input type="submit" value="<?php echo __('Basic', null, 'sfLucene') ?>" name="commit" accesskey="b" />
</form>

<?php if(isset($pager)): ?>

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
    'url'  => '##MODULE_NAME##/advanced',
    'radius' => sfConfig::get('app_lucene_pager_radius', 5)
  )); ?>

<?php endif; ?>