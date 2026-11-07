<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Curadoria — filtros por query string iguais ao Next (/produtos?category=&size=...).
 */
add_action('template_redirect', static function (): void {
	if (is_tax('product_cat')) {
		$term = get_queried_object();
		if ($term instanceof WP_Term) {
			wp_safe_redirect(add_query_arg('category', $term->slug, home_url('/produtos/')), 302);
			exit;
		}
	}
	// Alias /categoria/x → /produtos?category=x (rewrite do vesta-core)
	if (get_query_var('product_cat') && ! is_admin() && is_tax('product_cat')) {
		// já tratado acima
	}
});

add_action('pre_get_posts', static function (WP_Query $query): void {
	if (is_admin() || ! $query->is_main_query()) {
		return;
	}
	if (! $query->is_post_type_archive('product') && ! $query->is_page()) {
		// Shop page uses is_post_type_archive or is_shop
	}
	if (! function_exists('is_shop') || ! is_shop()) {
		return;
	}

	$meta_query = [];
	$tax_query  = [];

	$category = isset($_GET['category']) ? sanitize_title(wp_unslash((string) $_GET['category'])) : '';
	if ($category !== '') {
		$tax_query[] = [
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => [$category],
		];
	}

	$size = isset($_GET['size']) ? sanitize_text_field(wp_unslash((string) $_GET['size'])) : '';
	if ($size !== '') {
		$meta_query[] = [
			'key'   => '_vesta_size',
			'value' => $size,
		];
	}

	$brand = isset($_GET['brand']) ? sanitize_text_field(wp_unslash((string) $_GET['brand'])) : '';
	if ($brand !== '') {
		$meta_query[] = [
			'key'   => '_vesta_brand',
			'value' => $brand,
		];
	}

	$condition = isset($_GET['condition']) ? sanitize_text_field(wp_unslash((string) $_GET['condition'])) : '';
	if ($condition !== '') {
		$meta_query[] = [
			'key'   => '_vesta_condition',
			'value' => $condition,
		];
	}

	$availability = isset($_GET['availability']) ? sanitize_key(wp_unslash((string) $_GET['availability'])) : 'available';
	if ($availability === 'available') {
		$meta_query[] = [
			'key'   => '_stock_status',
			'value' => 'instock',
		];
	} elseif ($availability === 'sold') {
		$meta_query[] = [
			'key'   => '_stock_status',
			'value' => 'outofstock',
		];
	}

	$q = isset($_GET['q']) ? sanitize_text_field(wp_unslash((string) $_GET['q'])) : '';
	if ($q === '' && isset($_GET['s'])) {
		$q = sanitize_text_field(wp_unslash((string) $_GET['s']));
	}
	if ($q !== '') {
		$query->set('s', $q);
	}

	$sort = isset($_GET['sort']) ? sanitize_key(wp_unslash((string) $_GET['sort'])) : 'recent';
	switch ($sort) {
		case 'price-asc':
			$query->set('orderby', 'meta_value_num');
			$query->set('meta_key', '_price');
			$query->set('order', 'ASC');
			break;
		case 'price-desc':
			$query->set('orderby', 'meta_value_num');
			$query->set('meta_key', '_price');
			$query->set('order', 'DESC');
			break;
		case 'name':
			$query->set('orderby', 'title');
			$query->set('order', 'ASC');
			break;
		default:
			$query->set('orderby', 'date');
			$query->set('order', 'DESC');
			break;
	}

	if ($meta_query) {
		$meta_query['relation'] = 'AND';
		$query->set('meta_query', $meta_query);
	}
	if ($tax_query) {
		$query->set('tax_query', $tax_query);
	}

	$query->set('posts_per_page', 24);
});

/**
 * @return array{brands: string[], sizes: string[], categories: array<int, array{name:string,slug:string}>}
 */
function vesta_catalog_facets(): array {
	global $wpdb;

	$brands = $wpdb->get_col(
		"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		WHERE pm.meta_key = '_vesta_brand' AND pm.meta_value <> '' AND p.post_type = 'product' AND p.post_status = 'publish'
		ORDER BY meta_value ASC"
	);

	$sizes = $wpdb->get_col(
		"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		WHERE pm.meta_key = '_vesta_size' AND pm.meta_value <> '' AND p.post_type = 'product' AND p.post_status = 'publish'
		ORDER BY meta_value ASC"
	);

	$terms = get_terms([
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'exclude'    => [get_option('default_product_cat')],
	]);

	$categories = [];
	if (! is_wp_error($terms)) {
		foreach ($terms as $term) {
			$categories[] = ['name' => $term->name, 'slug' => $term->slug];
		}
	}

	return [
		'brands'     => array_values(array_filter(array_map('strval', $brands ?: []))),
		'sizes'      => array_values(array_filter(array_map('strval', $sizes ?: []))),
		'categories' => $categories,
	];
}

/**
 * @return array<string, string>
 */
function vesta_catalog_current_filters(): array {
	return [
		'q'            => isset($_GET['q']) ? sanitize_text_field(wp_unslash((string) $_GET['q'])) : (isset($_GET['s']) ? sanitize_text_field(wp_unslash((string) $_GET['s'])) : ''),
		'category'     => isset($_GET['category']) ? sanitize_title(wp_unslash((string) $_GET['category'])) : '',
		'size'         => isset($_GET['size']) ? sanitize_text_field(wp_unslash((string) $_GET['size'])) : '',
		'brand'        => isset($_GET['brand']) ? sanitize_text_field(wp_unslash((string) $_GET['brand'])) : '',
		'condition'    => isset($_GET['condition']) ? sanitize_text_field(wp_unslash((string) $_GET['condition'])) : '',
		'availability' => isset($_GET['availability']) ? sanitize_key(wp_unslash((string) $_GET['availability'])) : 'available',
		'sort'         => isset($_GET['sort']) ? sanitize_key(wp_unslash((string) $_GET['sort'])) : 'recent',
	];
}

function vesta_catalog_url(array $overrides = []): string {
	$current = array_merge(vesta_catalog_current_filters(), $overrides);
	$args    = [];
	foreach ($current as $key => $value) {
		if ($value === '' || ($key === 'availability' && $value === 'available') || ($key === 'sort' && $value === 'recent')) {
			continue;
		}
		$args[$key] = $value;
	}
	return add_query_arg($args, home_url('/produtos/'));
}
