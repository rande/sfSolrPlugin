<?php
/**
 * @package sfLucenePlugin
 * @subpackage Module
 * @author Carl Vondrick <carl@carlsoft.net>
 * @version SVN: $Id$
 */
?>

<?php if ($pager->haveToPaginate()): ?>
  <div class="search-page-numbers">
    <?php if ($pager->getPage() != $pager->getPreviousPage()): ?>
      <a href="<?php echo url_for('sfLucene/search') ?>?<?php echo $form->getQueryString($pager->getPreviousPage()) ?>" class="bookend"><?php echo __('Prev') ?></a>
    <?php endif ?>

    <?php foreach ($pager->getLinks($radius) as $page): ?>
      <?php if ($page == $pager->getPage()): ?>
        <strong><?php echo $page ?></strong>
      <?php else: ?>
        <a href="<?php echo url_for('sfLucene/search') ?>?<?php echo $form->getQueryString($page) ?>"><?php echo $page ?></a>
      <?php endif ?>
    <?php endforeach ?>

    <?php if ($pager->getPage() != $pager->getNextPage()): ?>
      <a href="<?php echo url_for('sfLucene/search') ?>?<?php echo $form->getQueryString($pager->getNextPage()) ?>" class="bookend"><?php echo __('Next') ?></a>
    <?php endif ?>
  </div>

<?php endif ?>