<?php
/**
 * Troca fotos de produtos demo pelas imagens do brand kit (PDF).
 * Uso: wp eval-file /scripts/refresh-brand-images.php --allow-root
 */

if (! defined('ABSPATH')) {
	exit(1);
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$theme_img = get_template_directory() . '/assets/img';
$map = [
	'vestido-crepe-ember'  => 'brand/cat-vestidos.png',
	'vestido-aurora'       => 'brand/cat-vestidos.png',
	'blazer-la-harbor'     => 'brand/cat-blazers.png',
	'blazer-siena'         => 'brand/cat-blazers.png',
	'camisa-poplin-marfim' => 'brand/cat-camisas.png',
	'trench-soleil'        => 'brand/cat-casacos.png',
	'bolsa-alba'           => 'brand/cat-acessorios.png',
	'sapato-roma'          => 'brand/cat-sapatos.png',
];

$ok = 0;
foreach ($map as $slug => $rel) {
	$path = $theme_img . '/' . $rel;
	$product = get_page_by_path($slug, OBJECT, 'product');
	if (! $product || ! is_readable($path)) {
		fwrite(STDERR, "skip {$slug}\n");
		continue;
	}

	$tmp = wp_tempnam(basename($path));
	if (! $tmp || ! copy($path, $tmp)) {
		fwrite(STDERR, "copy fail {$slug}\n");
		continue;
	}

	$file = [
		'name'     => basename($path),
		'type'     => 'image/png',
		'tmp_name' => $tmp,
		'error'    => 0,
		'size'     => (int) filesize($tmp),
	];
	$id = media_handle_sideload($file, (int) $product->ID, get_the_title($product));
	if (is_wp_error($id)) {
		@unlink($tmp);
		fwrite(STDERR, 'sideload fail ' . $slug . ': ' . $id->get_error_message() . "\n");
		continue;
	}

	set_post_thumbnail((int) $product->ID, (int) $id);
	$wc = wc_get_product((int) $product->ID);
	if ($wc) {
		$wc->set_image_id((int) $id);
		$wc->save();
	}
	$ok++;
	fwrite(STDOUT, "ok {$slug} -> {$id}\n");
}

fwrite(STDOUT, "Atualizados: {$ok}\n");
