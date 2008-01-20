<?php
/**
 * @package sfLucenePlugin
 * @subpackage Module
 * @author Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
?>

<?php use_helper('I18N') ?>

<h2><?php echo __('Advanced Search') ?></h2>

<form action="<?php echo url_for('sfLucene/advanced') ?>" method="get">
  <fieldset>
    <legend><?php echo __('Search Terms') ?></legend>

    <table>
      <?php echo $form ?>
    </table>
  </fieldset>

  <input type="submit" value="<?php echo __('Search') ?>" name="commit" accesskey="s" />
  <input type="submit" value="<?php echo __('Basic') ?>" name="commit" accesskey="b" />
</form>