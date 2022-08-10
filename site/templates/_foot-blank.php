		<?= $config->twig->render('util/ajax-modal.twig'); ?>
		<?= $config->twig->render('util/loading-modal.twig'); ?>

		<?php foreach($config->scripts->unique() as $script) : ?>
			<script src="<?= $script; ?>"></script>
		<?php endforeach; ?>

		<script>
			moment().format();
		</script>
		
		<?php if ($page->js) : ?>
			<script>
				<?= $page->js; ?>
			</script>
		<?php endif; ?>
	</body>
</html>
