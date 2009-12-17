<?php
/**
 * @package sfLucenePlugin
 * @subpackage Module
 * @author Carl Vondrick <carl@carlsoft.net>
 * @author Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version SVN: $Id: _pagerNavigation.php 7108 2008-01-20 07:44:42Z Carl.Vondrick $
 */
?>

<?php if ($pager->haveToPaginate()): ?>
  <div class="search-page-numbers">
    <?php if ($pager->getPage() != $pager->getPreviousPage()): ?>
      <a href="<?php echo url_for($url) ?>?<?php echo $form->getQueryString($pager->getPreviousPage()) ?>" class="bookend"><?php echo __('Prev', null, 'sfLucene') ?></a>
    <?php endif ?>

    <?php foreach ($pager->getLinks($radius) as $page): ?>
      <?php if ($page == $pager->getPage()): ?>
        <strong><?php echo $page ?></strong>
      <?php else: ?>
        <a href="<?php echo url_for($url) ?>?<?php echo $form->getQueryString($page) ?>"><?php echo $page ?></a>
      <?php endif ?>
    <?php endforeach ?>

    <?php if ($pager->getPage() != $pager->getNextPage()): ?>
      <a href="<?php echo url_for($url) ?>?<?php echo $form->getQueryString($pager->getNextPage()) ?>" class="bookend"><?php echo __('Next', null, 'sfLucene') ?></a>
    <?php endif ?>
  </div>
<?php endif ?>