<?php
/**
 * @package sfLucenePlugin
 * @subpackage Module
 * @author Carl Vondrick <carl@carlsoft.net>
 * @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version SVN: $Id: _publicControls.php 7108 2008-01-20 07:44:42Z Carl.Vondrick $
 */
?>

<?php use_helper('I18N') ?>

<div id="search-public">
  <?php echo form_tag('##MODULE_NAME##/search', 'method=get') ?>
    <?php echo input_tag('query', $query, array('id' => 'query-public')) ?>
    <?php echo submit_tag(__('Search', null, 'sfLucene'), array('name' => null)) ?>
  </form>
</div>