<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title><?= $page->get('headline|title'); ?></title>
		<meta name="description" content="<?= $page->summary; ?>" />
		<link rel="icon" href="<?= $siteconfig->icon->url; ?>" type="image/x-icon" sizes="32x32">
		<link rel="apple-touch-icon" href="<?= $siteconfig->icon->url; ?>">

		<?php foreach($config->styles->unique() as $css) : ?>
			<link rel="stylesheet" type="text/css" href="<?= $css; ?>" />
		<?php endforeach; ?>
	</head>

	<body class="fuelux">
