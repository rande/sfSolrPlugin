<?php
/**
 * @package sfLucenePlugin
 * @subpackage Module
 * @author Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
?>

<form action="<?php echo url_for('sfLucene/search') ?>" method="get" class="search-controls">

  <label for="query"><?php echo __('What are you looking for?') ?></label>
  <?php echo $form['query'] ?>

  <?php if ($form->hasCategories()): ?>
    <?php echo $form['category'] ?>
  <?php endif ?>

  <input type="submit" name="commit" accesskey="s" value="<?php echo __('Search') ?>" />

  <?php if (sfConfig::get('app_lucene_advanced', true)): ?>
    <input type="submit" name="commit" accesskey="a" value="<?php echo __('Advanced') ?>" />
  <?php endif ?>

</form>