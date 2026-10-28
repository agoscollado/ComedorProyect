<?php
/** @var \CodeIgniter\Pager\PagerRenderer $pager */
$pager->setSurroundCount($pager->getPageCount());
?>
<?php if ($pager->getPageCount() > 1): ?>
<nav class="paginacion" aria-label="Paginación">
    <?php if ($pager->hasPreviousPage()): ?>
        <a class="paginacion-flecha" href="<?= $pager->getPreviousPage() ?>" aria-label="Página anterior">&lsaquo;</a>
    <?php else: ?>
        <span class="paginacion-flecha paginacion-flecha-off" aria-hidden="true">&lsaquo;</span>
    <?php endif ?>

    <?php foreach ($pager->links() as $link): ?>
        <a class="paginacion-num<?= $link['active'] ? ' activa' : '' ?>" href="<?= $link['uri'] ?>"<?= $link['active'] ? ' aria-current="page"' : '' ?>><?= $link['title'] ?></a>
    <?php endforeach ?>

    <?php if ($pager->hasNextPage()): ?>
        <a class="paginacion-flecha" href="<?= $pager->getNextPage() ?>" aria-label="Página siguiente">&rsaquo;</a>
    <?php else: ?>
        <span class="paginacion-flecha paginacion-flecha-off" aria-hidden="true">&rsaquo;</span>
    <?php endif ?>
</nav>
<?php endif ?>