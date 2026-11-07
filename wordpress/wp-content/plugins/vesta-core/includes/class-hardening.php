<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Endurecimento leve da loja: menos superfície pública óbvia.
 */
final class Vesta_Core_Hardening {
	public static function init(): void {
		add_filter('rest_endpoints', [self::class, 'lock_users_endpoints']);
		add_action('template_redirect', [self::class, 'block_author_archives'], 0);
		add_filter('xmlrpc_enabled', '__return_false');
		add_filter('xmlrpc_methods', [self::class, 'disable_xmlrpc_methods'], PHP_INT_MAX);
		add_action('init', [self::class, 'block_xmlrpc_request'], 0);
		add_action('init', [self::class, 'remove_default_hello_world'], 20);
	}

	/**
	 * Evita enumeração de usuários via REST (/wp-json/wp/v2/users).
	 *
	 * @param array<string, mixed> $endpoints
	 * @return array<string, mixed>
	 */
	public static function lock_users_endpoints(array $endpoints): array {
		if (is_user_logged_in() && current_user_can('list_users')) {
			return $endpoints;
		}

		foreach (array_keys($endpoints) as $route) {
			if (str_starts_with((string) $route, '/wp/v2/users')) {
				unset($endpoints[$route]);
			}
		}

		return $endpoints;
	}

	public static function block_author_archives(): void {
		if (current_user_can('list_users')) {
			return;
		}
		// ?author=1 redireciona para /author/admin/ e vaza o login.
		if (is_author() || isset($_GET['author'])) {
			wp_safe_redirect(home_url('/'), 301);
			exit;
		}
	}

	/**
	 * A loja não publica via XML-RPC. Fecha pingback e métodos remotos.
	 *
	 * @param array<string, mixed> $methods
	 * @return array<string, mixed>
	 */
	public static function disable_xmlrpc_methods(array $methods): array {
		return [];
	}

	public static function block_xmlrpc_request(): void {
		if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
			status_header(403);
			exit;
		}
	}

	/** Remove o post demo "Hello world!" se ainda existir. */
	public static function remove_default_hello_world(): void {
		if (get_option('vesta_hello_world_cleaned')) {
			return;
		}

		$posts = get_posts([
			'name'           => 'hello-world',
			'post_type'      => 'post',
			'post_status'    => ['publish', 'draft', 'pending', 'private'],
			'posts_per_page' => 1,
			'fields'         => 'ids',
		]);

		if ($posts) {
			wp_trash_post((int) $posts[0]);
		}

		update_option('vesta_hello_world_cleaned', 1, false);
	}
}
