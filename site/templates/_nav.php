<nav class="navbar navbar-expand-lg bg-light">
	<div class="container-fluid">
		<a href="<?= $pages->get('/')->url; ?>" class="navbar-brand"  aria-label="homepage link">
			<img src="<?= $siteconfig->logo->width(100)->url; ?>" alt="">
		</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#nav-menu" aria-controls="nav-menu" aria-expanded="false" aria-label="Toggle navigation">
			<i class="fa fa-bars" aria-hidden="true"></i>
		</button>
		<div class="collapse navbar-collapse" id="nav-menu">
			<ul class="navbar-nav mr-auto">
				<li class="nav-item active">
					<a class="nav-link" href="<?= $pages->get('/')->url; ?>">Home <span class="sr-only">(current)</span></a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="<?= $pages->itemgroups->url; ?>"><?= $pages->itemgroups->title; ?></a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="<?= $pages->get('template=cart')->url; ?>">
						Cart (<?= $modules->get('Cart')->count_items(); ?>) <i class="fa fa-shopping-cart" aria-hidden="true"></i>
					</a>
				</li>
			</ul>
			<ul class="navbar-nav">
				<li class="nav-item dropdown">
					<a class="nav-link text-dark dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<?= $user->user; ?>
					</a>
					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
						<a class="dropdown-item" href="<?= $pages->get('template=orders')->url; ?>">Orders</a>
						<a class="dropdown-item" href="<?= $pages->get('template=invoices')->url; ?>">Invoices</a>
						<a class="dropdown-item" href="<?= $pages->get('template=account')->url; ?>">Account</a>
						<div class="dropdown-divider"></div>
						<a class="dropdown-item bg-danger text-white" href="<?= $pages->get('template=login')->url; ?>?action=logout">
							<i class="fa fa-sign-out" aria-hidden="true"></i> Logout
						</a>
					</div>
				</li>
			</ul>
		</div>
	</div>
</nav>
