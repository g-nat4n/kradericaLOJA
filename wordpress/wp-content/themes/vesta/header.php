<?php
declare(strict_types=1);
if (! defined('ABSPATH')) {
	exit;
}

$nav = [
	['Início', home_url('/')],
	['Peças', home_url('/produtos/')],
	['Categorias', home_url('/#categorias')],
	['Contato', home_url('/contato/')],
];

$account_url = is_user_logged_in()
	? (function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/minha-conta/'))
	: home_url('/login/');
$cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/carrinho/');
$shop_url = home_url('/produtos/');
$logo_url = VESTA_THEME_URI . '/assets/img/logo.jpg?v=' . VESTA_THEME_VERSION;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header" id="topo" data-site-header>
	<div class="container-main site-header__inner">
		<a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>">
			<img class="site-brand__logo" src="<?php echo esc_url($logo_url); ?>" alt="Brechó Kra de Rica" width="48" height="48">
			<span class="site-brand__text">Kra de Rica</span>
		</a>

		<nav class="nav-primary" aria-label="<?php esc_attr_e('Navegação principal', 'vesta'); ?>">
			<?php foreach ($nav as [$label, $url]) : ?>
				<a class="nav-link" href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
			<?php endforeach; ?>
		</nav>

		<div class="header-actions">
			<button type="button" class="icon-btn" data-search-toggle aria-label="<?php esc_attr_e('Abrir busca', 'vesta'); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
			</button>
			<a class="icon-btn" href="<?php echo esc_url($account_url); ?>" aria-label="<?php esc_attr_e('Conta', 'vesta'); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
			</a>
			<a class="icon-btn cart-link" href="<?php echo esc_url($cart_url); ?>" data-open-cart aria-label="<?php esc_attr_e('Abrir sacola', 'vesta'); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 7h12l-1 13H7L6 7z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>
				<?php vesta_cart_count_badge(); ?>
			</a>
			<button type="button" class="icon-btn menu-toggle" data-menu-toggle aria-expanded="false" aria-label="<?php esc_attr_e('Abrir menu', 'vesta'); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
			</button>
		</div>
	</div>

	<div class="header-search" data-header-search hidden>
		<form class="container-main" action="<?php echo esc_url($shop_url); ?>" method="get" role="search">
			<label>
				<span class="sr-only"><?php esc_html_e('Buscar na curadoria', 'vesta'); ?></span>
				<input type="search" name="s" placeholder="<?php esc_attr_e('Buscar peça ou estilo', 'vesta'); ?>" value="<?php echo esc_attr(get_search_query()); ?>">
				<input type="hidden" name="post_type" value="product">
			</label>
		</form>
	</div>

	<nav class="container-main nav-mobile" data-nav-mobile aria-label="<?php esc_attr_e('Menu mobile', 'vesta'); ?>">
		<?php foreach ($nav as [$label, $url]) : ?>
			<a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
		<?php endforeach; ?>
		<a href="<?php echo esc_url($account_url); ?>"><?php esc_html_e('Minha conta', 'vesta'); ?></a>
	</nav>
</header>
