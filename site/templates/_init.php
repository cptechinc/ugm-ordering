<?php
/**
 * Initialization file for template files
 *
 * This file is automatically included as a result of $config->prependTemplateFile
 * option specified in your /site/config.php.
 *
 * You can initialize anything you want to here. In the case of this beginner profile,
 * we are using it just to include another file with shared functions.
 *
 */

include_once("./_func.php"); // include our shared functions

// BUILD AND INSTATIATE CLASSES
$page->fullURL = new Purl\Url($page->httpUrl);
$page->fullURL->path = '';
if (!empty($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '/') {
	$page->fullURL->join($_SERVER['REQUEST_URI']);
}

// CHECK DATABASE CONNECTIONS
if ($page->template != 'database-error') {
	if (empty(wire('dplusdata')) || empty(wire('dpluso'))) {
		$modules->get('DplusDatabase')->log_error('At least One database is not connected');
		//$session->redirect($pages->get('template=database-error')->url, $http301 = false);
	}

	$db_modules = array(
		'dplusdata' => array(
			'module'   => 'DplusDatabase',
			'default'  => true
		),
		'dpluso' => array(
			'module'   => 'DplusDatabaseDpluso',
			'default'  => false
		)
	);

	foreach ($db_modules as $key => $connection) {
		$module = $modules->get($connection['module']);
		$module->connect_propel();

		try {
			$propel_name = $module->get_connection_name_db();
			$$propel_name = $module->get_propel_write_connection();
			$$propel_name->useDebug(true);
		} catch (Exception $e) {
			$module->log_error($e->getMessage());
			$session->redirect($pages->get('template=database-error')->url, $http301 = false);
		}
	}

	if ($user->isLoggedInDplus()) {
		$user->setup();
	}

	$templates_nosignin = array('login', 'build', 'validate', 'reset');

	if (!in_array($page->template, $templates_nosignin) && !$user->isLoggedInDplus()) {
		$session->redirect($pages->get('template=login')->url, $http301 = false);
	} elseif ($page->template != 'account' && $user->needs_setup_recovery()) {
		$session->redirect($pages->get('template=account')->url, $http301 = false);
	}
} else {
	try {
		$con    = $modules->get('DplusDatabase')->get_propel_write_connection();
		$dpluso = $modules->get('DplusDatabaseDpluso')->get_propel_write_connection();
	} catch (Exception $e) {
		$page->body = $e->getMessage();
	}
}

// ADD JS AND CSS
$config->styles->append(hash_templatefile('styles/lib/bootstrap-grid.min.css'));
$config->styles->append(hash_templatefile('styles/theme.css'));
$config->styles->append('//fonts.googleapis.com/css?family=Lusitana:400,700|Quattrocento:400,700');
$config->styles->append('https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
$config->styles->append(hash_templatefile('styles/lib/fuelux.css'));
$config->styles->append(hash_templatefile('styles/lib/sweetalert2.css'));
$config->styles->append(hash_templatefile('styles/main.css'));


$config->scripts->append(hash_templatefile('scripts/lib/jquery.js'));
$config->scripts->append(hash_templatefile('scripts/lib/popper.js'));
$config->scripts->append(hash_templatefile('scripts/lib/bootstrap.min.js'));
$config->scripts->append(hash_templatefile('scripts/lib/fuelux.js'));
// $config->scripts->append(hash_templatefile('scripts/lib/sweetalert.js'));
$config->scripts->append(hash_templatefile('scripts/lib/moment.js'));
$config->scripts->append(hash_templatefile('scripts/lib/bootstrap-notify.js'));
$config->scripts->append(hash_templatefile('scripts/lib/uri.js'));
$config->scripts->append(hash_templatefile('scripts/lib/sweetalert2.js'));
$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
$config->scripts->append(hash_templatefile('scripts/classes.js'));
$config->scripts->append(hash_templatefile('scripts/main.js'));


// SET CONFIG PROPERTIES
if ($input->get->modal) {
	$config->modal = true;
}

if ($input->get->json) {
	$config->json = true;
}

$session->sessionid = session_id();

$mtwig = $modules->get('Twig');
$config->twigloader = $mtwig->getLoader();
$config->twig = $mtwig->getTwig();
$config->twig->getExtension(\Twig\Extension\CoreExtension::class)->setNumberFormat(3, '.', '');


$siteconfig = $pages->get('template=config');

$html = $modules->get('HtmlWriter');

$page->show_breadcrumbs = true;

$session->display = 12;
$page->showonpage = $session->display;


$rm = strtolower($input->requestMethod());
$values = $input->$rm;

$modules->get('UgmOrderingPagesItem')->createPagesForNewItems();
include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');

if ($session->getFor('cart', 'add')) {
	$cartCRUD = Dplus\Ecomm\Cart::getInstance();
	$page->js .= $config->twig->render('cart/toast.js.twig', ['response' => $cartCRUD->getResponse()]);
	$session->removeFor('cart', 'add');
}
