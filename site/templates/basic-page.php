<?php include('./_head.php'); ?>
	<div class="jumbotron bg-secondary page-banner rounded-0 mb-3">
		<div class="container">
			<h1 class="display-4 text-light"><?= $page->get('headline|title'); ?></h1>
		</div>
	</div>
	<?php if ($page->show_breadcrumbs) : ?>
		<div class="container">
			<?= $config->twig->render('nav/breadcrumbs.twig', ['page' => $page]); ?>
		</div>
	<?php endif; ?>
	<div class='container page'>
		<?= $page->body; ?>
	</div>
<?php include('./_foot.php'); ?>
