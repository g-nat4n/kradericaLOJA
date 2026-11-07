<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

add_action('after_setup_theme', static function (): void {
	load_theme_textdomain('vesta', VESTA_THEME_DIR . '/languages');

	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
	add_theme_support('custom-logo', [
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	]);
	add_theme_support('woocommerce', [
		'thumbnail_image_width' => 600,
		'single_image_width'    => 900,
		'product_grid'          => [
			'default_rows'    => 4,
			'min_rows'        => 1,
			'default_columns' => 3,
			'min_columns'     => 2,
			'max_columns'     => 4,
		],
	]);
	add_theme_support('wc-product-gallery-zoom');
	add_theme_support('wc-product-gallery-lightbox');
	add_theme_support('wc-product-gallery-slider');

	register_nav_menus([
		'primary' => __('Menu principal', 'vesta'),
		'footer'  => __('Menu rodapé', 'vesta'),
	]);
});

add_action('widgets_init', static function (): void {
	register_sidebar([
		'name'          => __('Rodapé', 'vesta'),
		'id'            => 'footer-1',
		'before_widget' => '<div class="footer-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<p class="eyebrow">',
		'after_title'   => '</p>',
	]);
});

add_filter('body_class', static function (array $classes): array {
	$classes[] = 'vesta-theme';
	return $classes;
});
