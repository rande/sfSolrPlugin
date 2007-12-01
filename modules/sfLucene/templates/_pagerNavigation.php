<?php
/**
 * @package sfLucenePlugin
 * @subpackage Module
 * @author Carl Vondrick <carlv@carlsoft.net>
 * @version SVN: $Id$
 */
?>

<?php if ($pager->haveToPaginate()): ?>
  <div class="search-page-numbers">
    <?php if ($pager->getPage() != $pager->getPreviousPage()): ?>
      <?php echo link_to(__('Prev'), 'sfLucene/search?query=' . $query . '&page=' . $pager->getPreviousPage(), 'class=bookend') ?>
    <?php endif ?>

    <?php foreach ($links as $page): ?>
      <?php if ($page == $pager->getPage()): ?>
        <strong><?php echo $page ?></strong>
      <?php else: ?>
        <?php echo link_to($page, 'sfLucene/search?query=' . $query . '&page=' . $page) ?>
      <?php endif ?>
    <?php endforeach ?>

    <?php if ($pager->getPage() != $pager->getNextPage()): ?>
      <?php echo link_to(__('Next'), 'sfLucene/search?query=' . $query . '&page=' . $pager->getNextPage(), 'class=bookend') ?>
    <?php endif ?>
  </div>

<?php endif ?>