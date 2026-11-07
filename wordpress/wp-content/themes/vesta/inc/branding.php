<?php
/**
 * Favicon / ícone do site (aba + Google).
 *
 * @package Vesta
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/** Links de ícone no <head> (aba do navegador). */
add_action('wp_head', static function (): void {
	$base = VESTA_THEME_URI . '/assets/img';
	$ver  = VESTA_THEME_VERSION;
	echo '<link rel="icon" href="' . esc_url($base . '/favicon-32.png?v=' . $ver) . '" sizes="32x32" type="image/png">' . "\n";
	echo '<link rel="icon" href="' . esc_url($base . '/icon-192.png?v=' . $ver) . '" sizes="192x192" type="image/png">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url($base . '/apple-touch-icon.png?v=' . $ver) . '" sizes="180x180">' . "\n";
	echo '<link rel="manifest" href="' . esc_url(home_url('/?vesta_manifest=1')) . '">' . "\n";
	echo '<meta name="theme-color" content="#332621">' . "\n";
}, 2);

/** Web app manifest (ajuda o Google a achar o ícone). */
add_action('template_redirect', static function (): void {
	if (! isset($_GET['vesta_manifest'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$base = VESTA_THEME_URI . '/assets/img';
	$manifest = [
		'name'             => wp_strip_all_tags(get_bloginfo('name')),
		'short_name'       => 'Kra de Rica',
		'description'      => wp_strip_all_tags(get_bloginfo('description')),
		'start_url'        => home_url('/'),
		'display'          => 'standalone',
		'background_color' => '#fdf8f4',
		'theme_color'      => '#332621',
		'icons'            => [
			[
				'src'   => $base . '/icon-192.png',
				'sizes' => '192x192',
				'type'  => 'image/png',
			],
			[
				'src'   => $base . '/icon-512.png',
				'sizes' => '512x512',
				'type'  => 'image/png',
			],
		],
	];
	nocache_headers();
	header('Content-Type: application/manifest+json; charset=utf-8');
	echo wp_json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	exit;
}, 0);

/**
 * Se ainda não houver site_icon no WP, usa o PNG do tema
 * (Customizer / Google Search).
 */
add_action('init', static function (): void {
	if ((int) get_option('site_icon') > 0) {
		return;
	}
	if (get_option('_vesta_site_icon_bootstrapped') === '1') {
		return;
	}
	$path = VESTA_THEME_DIR . '/assets/img/icon-512.png';
	if (! is_readable($path) || ! function_exists('media_handle_sideload')) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam(basename($path));
	if (! $tmp || ! copy($path, $tmp)) {
		return;
	}
	$file = [
		'name'     => 'vesta-site-icon.png',
		'type'     => 'image/png',
		'tmp_name' => $tmp,
		'error'    => 0,
		'size'     => (int) filesize($tmp),
	];
	$id = media_handle_sideload($file, 0, 'Kra de Rica ícone');
	if (is_wp_error($id)) {
		@unlink($tmp);
		return;
	}
	update_option('site_icon', (int) $id);
	update_option('_vesta_site_icon_bootstrapped', '1');
}, 30);
