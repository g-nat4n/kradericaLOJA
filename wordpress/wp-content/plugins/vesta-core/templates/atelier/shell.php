<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

Vesta_Core_Atelier::enqueue();
$nav = vesta_atelier_nav();
$current = $atelier_page ?? 'home';
$active_key = match ($current) {
	'produto-novo', 'produto-editar' => 'produtos',
	'pedido' => 'pedidos',
	'home' => '',
	default => $current,
};
$css_fonts = 'https://fonts.googleapis.com/css2?family=Arimo:wght@400;600;700&family=Playfair+Display:wght@500;600;700&display=swap';
$css_main  = VESTA_CORE_URL . 'assets/css/atelier.css?ver=' . rawurlencode(VESTA_CORE_VERSION);
$js_main   = VESTA_CORE_URL . 'assets/js/atelier.js?ver=' . rawurlencode(VESTA_CORE_VERSION);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex,nofollow">
	<title>Atelier Kra de Rica</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="stylesheet" href="<?php echo esc_url($css_fonts); ?>">
	<link rel="stylesheet" href="<?php echo esc_url($css_main); ?>">
</head>
<body class="atelier">
<aside class="atelier-aside">
	<a class="atelier-brand" href="<?php echo esc_url(home_url('/')); ?>">KRA DE RICA</a>
	<p class="atelier-brand-sub">Atelier</p>
	<nav class="atelier-nav" aria-label="Atelier">
		<?php foreach ($nav as $path => $label) : ?>
			<a href="<?php echo esc_url(Vesta_Core_Atelier::url($path)); ?>" class="<?php echo $active_key === $path ? 'is-active' : ''; ?>">
				<?php echo esc_html($label); ?>
			</a>
		<?php endforeach; ?>
	</nav>
	<div class="atelier-aside__foot">
		<a class="atelier-aside__store" href="<?php echo esc_url(home_url('/')); ?>">Voltar para a loja</a>
		<a class="atelier-aside__logout" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Sair</a>
	</div>
</aside>

<div class="atelier-main">
	<header class="atelier-mobile">
		<a class="atelier-mobile__brand" href="<?php echo esc_url(Vesta_Core_Atelier::url()); ?>">KRA DE RICA · Atelier</a>
		<button type="button" class="atelier-mobile__toggle" data-atelier-menu aria-expanded="false">Abrir menu</button>
	</header>
	<nav class="atelier-mobile-nav" data-atelier-mobile-nav>
		<?php foreach ($nav as $path => $label) : ?>
			<a href="<?php echo esc_url(Vesta_Core_Atelier::url($path)); ?>"><?php echo esc_html($label); ?></a>
		<?php endforeach; ?>
		<a href="<?php echo esc_url(home_url('/')); ?>">Loja</a>
	</nav>
	<div class="atelier-content">
		<?php require $template; ?>
	</div>
</div>
<script src="<?php echo esc_url($js_main); ?>" defer></script>
</body>
</html>
