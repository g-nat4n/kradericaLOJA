<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

final class Vesta_Core_Permalinks {
	public static function init(): void {
		add_action('init', [self::class, 'rewrites']);
		add_filter('post_type_link', [self::class, 'product_link'], 10, 2);
		add_filter('term_link', [self::class, 'category_link'], 10, 3);
		add_filter('rewrite_rules_array', [self::class, 'rules']);
	}

	public static function rewrites(): void {
		add_rewrite_rule('^produto/([^/]+)/?$', 'index.php?product=$matches[1]', 'top');
		add_rewrite_rule('^categoria/([^/]+)/?$', 'index.php?product_cat=$matches[1]', 'top');
	}

	/**
	 * @param array<string, string> $rules
	 * @return array<string, string>
	 */
	public static function rules(array $rules): array {
		$extra = [
			'produto/([^/]+)/?$'   => 'index.php?product=$matches[1]',
			'categoria/([^/]+)/?$' => 'index.php?product_cat=$matches[1]',
		];
		return $extra + $rules;
	}

	public static function product_link(string $permalink, WP_Post $post): string {
		if ($post->post_type !== 'product') {
			return $permalink;
		}
		return home_url('/produto/' . $post->post_name . '/');
	}

	public static function category_link(string $url, WP_Term $term, string $taxonomy): string {
		if ($taxonomy !== 'product_cat') {
			return $url;
		}
		// Curadoria com filtro (igual Next: /produtos?category=slug)
		return add_query_arg('category', $term->slug, home_url('/produtos/'));
	}
}
