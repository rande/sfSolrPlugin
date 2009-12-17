<?php
/**
 * @package sfLucenePlugin
 * @subpackage Module
 * @author Carl Vondrick <carl@carlsoft.net>
 * @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version SVN: $Id: _controls.php 7108 2008-01-20 07:44:42Z Carl.Vondrick $
 */
?>

<form action="<?php echo url_for('##MODULE_NAME##/search') ?>" method="get" class="search-controls">

  <label for="query"><?php echo __('What are you looking for?', null, 'sfLucene') ?></label>
  <?php echo $form['query'] ?>

  <?php if ($form->hasCategories()): ?>
    <?php echo $form['category'] ?>
  <?php endif ?>

  <input type="submit" name="commit" accesskey="s" value="<?php echo __('Search', null, 'sfLucene') ?>" />

  <?php if (sfConfig::get('app_lucene_advanced', true)): ?>
    <input type="submit" name="commit" accesskey="a" value="<?php echo __('Advanced', null, 'sfLucene') ?>" />
  <?php endif ?>

</form>