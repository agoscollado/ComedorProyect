<?php
/** @var \CodeIgniter\Pager\PagerRenderer $pager */
$totalPaginas = $pager->getPageCount();
$pager->setSurroundCount($totalPaginas);
$urlAnterior = $pager->hasPreviousPage() ? $pager->getPreviousPage() : null;
$urlSiguiente = $pager->hasNextPage() ? $pager->getNextPage() : null;
$urlPrimera = $pager->getFirst();
$urlUltima = $pager->getLast();
$pager->setSurroundCount(2);
$links = $pager->links();
$paginaInicial = $pager->getFirstPageNumber();
$paginaFinal = $pager->getLastPageNumber();
?>
<?php if ($totalPaginas > 1): ?>
<nav class="paginacion" aria-label="Paginación">
    <?php if ($urlAnterior !== null): ?>
        <a class="paginacion-flecha" href="<?= esc($urlAnterior) ?>" aria-label="Página anterior">&lsaquo;</a>
    <?php else: ?>
        <span class="paginacion-flecha paginacion-flecha-off" aria-hidden="true">&lsaquo;</span>
    <?php endif ?>

    <?php if ($paginaInicial > 1): ?>
        <a class="paginacion-num" href="<?= esc($urlPrimera) ?>">1</a>
        <?php if ($paginaInicial > 2): ?>
            <span class="paginacion-ellipsis" aria-hidden="true">…</span>
        <?php endif ?>
    <?php endif ?>

    <?php foreach ($links as $link): ?>
        <a class="paginacion-num<?= $link['active'] ? ' activa' : '' ?>" href="<?= esc($link['uri']) ?>"<?= $link['active'] ? ' aria-current="page"' : '' ?>><?= esc((string) $link['title']) ?></a>
    <?php endforeach ?>

    <?php if ($paginaFinal < $totalPaginas): ?>
        <?php if ($paginaFinal < $totalPaginas - 1): ?>
            <span class="paginacion-ellipsis" aria-hidden="true">…</span>
        <?php endif ?>
        <a class="paginacion-num" href="<?= esc($urlUltima) ?>"><?= esc((string) $totalPaginas) ?></a>
    <?php endif ?>

    <?php if ($urlSiguiente !== null): ?>
        <a class="paginacion-flecha" href="<?= esc($urlSiguiente) ?>" aria-label="Página siguiente">&rsaquo;</a>
    <?php else: ?>
        <span class="paginacion-flecha paginacion-flecha-off" aria-hidden="true">&rsaquo;</span>
    <?php endif ?>
</nav>
<?php endif ?>