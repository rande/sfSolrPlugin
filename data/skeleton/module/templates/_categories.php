<?php
/**
 * @package sfLucenePlugin
 * @subpackage Module
 * @author Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id: _categories.php 7108 2008-01-20 07:44:42Z Carl.Vondrick $
 */
?>

<?php if ($show): ?>
  <?php echo select_tag('category', options_for_select($categories, $selected), array('multiple' => $multiple, 'id' => 'sfl_category')) ?>
<?php endif ?>