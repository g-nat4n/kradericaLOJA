<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

add_action('wp_enqueue_scripts', static function (): void {
	wp_enqueue_style(
		'vesta-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Manrope:wght@400;500;600;700&display=swap',
		[],
		null
	);

	wp_enqueue_style(
		'vesta-main',
		VESTA_THEME_URI . '/assets/css/main.css',
		['vesta-fonts'],
		VESTA_THEME_VERSION
	);

	wp_enqueue_script(
		'vesta-main',
		VESTA_THEME_URI . '/assets/js/main.js',
		[],
		VESTA_THEME_VERSION,
		true
	);

	wp_localize_script('vesta-main', 'vestaTheme', [
		'ajaxUrl' => admin_url('admin-ajax.php'),
		'homeUrl' => home_url('/'),
	]);
});
