<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Atelier Vesta — painel front-end espelhando /admin do Next.
 */
final class Vesta_Core_Atelier {
	public const CAP = 'edit_products';

	public static function init(): void {
		add_action('init', [self::class, 'rewrites']);
		add_filter('query_vars', [self::class, 'query_vars']);
		add_action('template_redirect', [self::class, 'render'], 0);
		add_action('admin_post_vesta_atelier_save_product', [self::class, 'handle_save_product']);
		add_action('admin_post_vesta_atelier_product_action', [self::class, 'handle_product_action']);
		add_action('admin_post_vesta_atelier_save_category', [self::class, 'handle_save_category']);
		add_action('admin_post_vesta_atelier_category_action', [self::class, 'handle_category_action']);
		add_action('admin_post_vesta_atelier_toggle_bestseller', [self::class, 'handle_toggle_bestseller']);
		add_action('admin_post_vesta_atelier_order_status', [self::class, 'handle_order_status']);
		add_action('admin_post_vesta_atelier_promote_user', [self::class, 'handle_promote_user']);
		add_action('admin_bar_menu', [self::class, 'admin_bar_link'], 80);
		// Equipe da loja usa o Atelier; wp-admin só com ?wp=1 (config técnica).
		add_action('admin_init', [self::class, 'redirect_wp_admin_to_atelier'], 1);
	}

	/**
	 * Envia a equipe da loja para o Atelier (/admin), não para o painel WordPress.
	 * Escape técnico (Mercado Pago, frete, etc.): /wp-admin/?wp=1
	 */
	public static function redirect_wp_admin_to_atelier(): void {
		if (! self::can_access()) {
			return;
		}
		if (wp_doing_ajax() || (defined('WP_CLI') && WP_CLI)) {
			return;
		}
		if (isset($_GET['wp']) && (string) $_GET['wp'] === '1') {
			return;
		}
		// Retorno MP no wp-admin: só com payment_id validado (nunca só external_reference).
		if (function_exists('vesta_mp_order_from_return_query')) {
			$order = vesta_mp_order_from_return_query();
			if ($order instanceof WC_Order) {
				if (function_exists('vesta_mp_sync_order')) {
					vesta_mp_sync_order($order);
				}
				$thanks = function_exists('vesta_order_received_url_for_visitor')
					? vesta_order_received_url_for_visitor($order)
					: remove_query_arg('key', $order->get_checkout_order_received_url());
				wp_safe_redirect($thanks);
				exit;
			}
		}
		global $pagenow;
		$allowed = ['admin-ajax.php', 'admin-post.php', 'async-upload.php', 'media-new.php', 'media-upload.php'];
		if (is_string($pagenow) && in_array($pagenow, $allowed, true)) {
			return;
		}
		wp_safe_redirect(self::url());
		exit;
	}

	public static function rewrites(): void {
		add_rewrite_rule('^admin/?$', 'index.php?vesta_atelier=home', 'top');
		add_rewrite_rule('^admin/produtos/?$', 'index.php?vesta_atelier=produtos', 'top');
		add_rewrite_rule('^admin/produtos/novo/?$', 'index.php?vesta_atelier=produto-novo', 'top');
		add_rewrite_rule('^admin/produtos/([0-9]+)/?$', 'index.php?vesta_atelier=produto-editar&vesta_atelier_id=$matches[1]', 'top');
		add_rewrite_rule('^admin/best-sellers/?$', 'index.php?vesta_atelier=best-sellers', 'top');
		add_rewrite_rule('^admin/categorias/?$', 'index.php?vesta_atelier=categorias', 'top');
		add_rewrite_rule('^admin/pedidos/?$', 'index.php?vesta_atelier=pedidos', 'top');
		add_rewrite_rule('^admin/pedidos/([0-9]+)/?$', 'index.php?vesta_atelier=pedido&vesta_atelier_id=$matches[1]', 'top');
		add_rewrite_rule('^admin/clientes/?$', 'index.php?vesta_atelier=clientes', 'top');
	}

	/**
	 * @param array<int, string> $vars
	 * @return array<int, string>
	 */
	public static function query_vars(array $vars): array {
		$vars[] = 'vesta_atelier';
		$vars[] = 'vesta_atelier_id';
		return $vars;
	}

	public static function url(string $path = ''): string {
		$path = trim($path, '/');
		return $path === '' ? home_url('/admin/') : home_url('/admin/' . $path . '/');
	}

	public static function can_access(): bool {
		return is_user_logged_in() && current_user_can(self::CAP);
	}

	public static function render(): void {
		$page = get_query_var('vesta_atelier');
		if (! is_string($page) || $page === '') {
			return;
		}

		if (! self::can_access()) {
			$login = wp_login_url(self::url($page === 'home' ? '' : $page));
			// Prefer página de login da loja se existir.
			$custom = get_page_by_path('login');
			if ($custom instanceof WP_Post) {
				$login = add_query_arg('redirect_to', rawurlencode(self::url($page === 'home' ? '' : str_replace('produto-novo', 'produtos/novo', $page))), get_permalink($custom));
			}
			wp_safe_redirect($login);
			exit;
		}

		$id = absint(get_query_var('vesta_atelier_id'));
		$map = [
			'home'           => 'home.php',
			'produtos'       => 'products.php',
			'produto-novo'   => 'product-form.php',
			'produto-editar' => 'product-form.php',
			'best-sellers'   => 'bestsellers.php',
			'categorias'     => 'categories.php',
			'pedidos'        => 'orders.php',
			'pedido'         => 'order.php',
			'clientes'       => 'clients.php',
		];
		if (! isset($map[$page])) {
			status_header(404);
			exit;
		}

		nocache_headers();
		status_header(200);
		add_filter('show_admin_bar', '__return_false', 99);
		show_admin_bar(false);
		// Evita chrome da loja (sacola/drawer) no Atelier.
		remove_all_actions('wp_enqueue_scripts');
		remove_all_actions('wp_footer');
		remove_all_actions('wp_body_open');
		$atelier_page = $page;
		$atelier_id   = $id;
		$template     = VESTA_CORE_DIR . 'templates/atelier/' . $map[$page];
		$shell        = VESTA_CORE_DIR . 'templates/atelier/shell.php';
		require $shell;
		exit;
	}

	public static function enqueue(): void {
		wp_enqueue_style(
			'vesta-atelier-fonts',
			'https://fonts.googleapis.com/css2?family=Arimo:wght@400;600;700&family=Playfair+Display:wght@500;600;700&display=swap',
			[],
			null
		);
		wp_enqueue_style(
			'vesta-atelier',
			VESTA_CORE_URL . 'assets/css/atelier.css',
			['vesta-atelier-fonts'],
			VESTA_CORE_VERSION
		);
		wp_enqueue_script(
			'vesta-atelier',
			VESTA_CORE_URL . 'assets/js/atelier.js',
			[],
			VESTA_CORE_VERSION,
			true
		);
	}

	public static function admin_bar_link(\WP_Admin_Bar $bar): void {
		if (! self::can_access()) {
			return;
		}
		$bar->add_node([
			'id'    => 'vesta-atelier',
			'title' => 'Atelier Kra de Rica',
			'href'  => self::url(),
			'meta'  => ['title' => 'Painel da loja'],
		]);
	}

	public static function require_post(): void {
		if (! self::can_access()) {
			wp_die(esc_html__('Sem permissão.', 'vesta-core'), 403);
		}
		check_admin_referer('vesta_atelier');
	}

	public static function handle_save_product(): void {
		self::require_post();
		$id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;

		if ($id > 0 && get_post_type($id) !== 'product') {
			wp_safe_redirect(add_query_arg('aviso', rawurlencode('Peça inválida.'), self::url('produtos')));
			exit;
		}

		$name = sanitize_text_field(wp_unslash((string) ($_POST['name'] ?? '')));
		if ($name === '') {
			wp_safe_redirect(add_query_arg('aviso', rawurlencode('Informe o nome da peça.'), wp_get_referer() ?: self::url('produtos')));
			exit;
		}

		$allowed_status = array_keys(vesta_atelier_status_labels());
		$status_key = sanitize_text_field(wp_unslash((string) ($_POST['status'] ?? 'AVAILABLE')));
		if (! in_array($status_key, $allowed_status, true)) {
			$status_key = 'AVAILABLE';
		}
		$post_status = match ($status_key) {
			'DRAFT' => 'draft',
			'ARCHIVED' => 'private',
			default => 'publish',
		};

		$postarr = [
			'post_title'   => $name,
			'post_content' => wp_kses_post(wp_unslash((string) ($_POST['description'] ?? ''))),
			'post_status'  => $post_status,
			'post_type'    => 'product',
		];
		if ($id > 0) {
			$postarr['ID'] = $id;
			wp_update_post($postarr);
		} else {
			$id = (int) wp_insert_post($postarr, true);
			if (is_wp_error($id) || $id <= 0) {
				wp_safe_redirect(add_query_arg('aviso', rawurlencode('Não foi possível criar a peça.'), self::url('produtos/novo')));
				exit;
			}
		}

		$product = wc_get_product($id);
		if (! $product instanceof WC_Product) {
			wp_safe_redirect(add_query_arg('aviso', rawurlencode('Não foi possível carregar a peça.'), self::url('produtos')));
			exit;
		}

		$price = (float) str_replace(',', '.', (string) ($_POST['price'] ?? '0'));
		$compare = (float) str_replace(',', '.', (string) ($_POST['compareAt'] ?? '0'));
		$stock = max(0, (int) ($_POST['stock'] ?? 1));

		$product->set_regular_price($compare > $price && $compare > 0 ? (string) $compare : (string) $price);
		if ($compare > $price && $price > 0) {
			$product->set_sale_price((string) $price);
		} else {
			$product->set_sale_price('');
			$product->set_regular_price((string) $price);
		}
		$product->set_manage_stock(true);
		$product->set_stock_quantity($stock);

		if ($status_key === 'SOLD' || $stock <= 0) {
			$product->set_stock_status('outofstock');
			$product->set_stock_quantity(0);
		} elseif ($status_key === 'AVAILABLE' || $status_key === 'RESERVED') {
			$product->set_stock_status('instock');
		}

		$featured = ! empty($_POST['featured']);
		$product->set_featured($featured);
		$product->set_catalog_visibility('visible');
		$product->save();

		update_post_meta($id, '_vesta_status', $status_key);
		update_post_meta($id, '_vesta_brand', sanitize_text_field(wp_unslash((string) ($_POST['brand'] ?? ''))));
		update_post_meta($id, '_vesta_size', sanitize_text_field(wp_unslash((string) ($_POST['size'] ?? ''))));
		update_post_meta($id, '_vesta_color', sanitize_text_field(wp_unslash((string) ($_POST['color'] ?? ''))));
		update_post_meta($id, '_vesta_material', sanitize_text_field(wp_unslash((string) ($_POST['material'] ?? ''))));
		update_post_meta($id, '_vesta_condition', sanitize_text_field(wp_unslash((string) ($_POST['condition'] ?? 'EXCELLENT'))));
		update_post_meta($id, '_vesta_story', sanitize_textarea_field(wp_unslash((string) ($_POST['story'] ?? ''))));
		update_post_meta($id, '_vesta_unique_piece', ! empty($_POST['uniquePiece']) ? 'yes' : 'no');
		update_post_meta($id, '_vesta_bestseller', $featured ? 'yes' : 'no');

		$measures = [];
		foreach (['bust', 'waist', 'hip', 'length', 'shoulder'] as $key) {
			$raw = sanitize_text_field(wp_unslash((string) ($_POST[$key] ?? '')));
			if ($raw !== '') {
				$measures[$key] = $raw;
			}
		}
		update_post_meta($id, '_vesta_measurements', $measures);

		$cat = absint($_POST['categoryId'] ?? 0);
		if ($cat > 0) {
			wp_set_object_terms($id, [$cat], 'product_cat');
		}
		$look = absint($_POST['lookId'] ?? 0);
		if ($look > 0) {
			wp_set_object_terms($id, [$look], 'look');
		} else {
			wp_set_object_terms($id, [], 'look');
		}

		// Uploads.
		if (! empty($_FILES['photos']['name']) && is_array($_FILES['photos']['name'])) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$gallery = $product->get_gallery_image_ids();
			$count   = count($_FILES['photos']['name']);
			for ($i = 0; $i < $count; $i++) {
				if (empty($_FILES['photos']['name'][$i]) || (int) ($_FILES['photos']['error'][$i] ?? 0) !== UPLOAD_ERR_OK) {
					continue;
				}
				$file = [
					'name'     => $_FILES['photos']['name'][$i],
					'type'     => $_FILES['photos']['type'][$i],
					'tmp_name' => $_FILES['photos']['tmp_name'][$i],
					'error'    => $_FILES['photos']['error'][$i],
					'size'     => $_FILES['photos']['size'][$i],
				];
				$_FILES['vesta_photo_single'] = $file;
				$attach_id = media_handle_upload('vesta_photo_single', $id);
				if (! is_wp_error($attach_id)) {
					if (! $product->get_image_id()) {
						$product->set_image_id($attach_id);
					} else {
						$gallery[] = $attach_id;
					}
				}
			}
			$product->set_gallery_image_ids(array_values(array_unique(array_map('absint', $gallery))));
			$product->save();
		}

		// Sync bestsellers option.
		$ids = get_option('vesta_bestsellers', []);
		if (! is_array($ids)) {
			$ids = [];
		}
		$ids = array_map('absint', $ids);
		if ($featured && ! in_array($id, $ids, true)) {
			$ids[] = $id;
		}
		if (! $featured) {
			$ids = array_values(array_diff($ids, [$id]));
		}
		update_option('vesta_bestsellers', $ids);

		wp_safe_redirect(self::url('produtos/' . $id));
		exit;
	}

	public static function handle_product_action(): void {
		self::require_post();
		$id = absint($_POST['product_id'] ?? 0);
		$action = sanitize_text_field(wp_unslash((string) ($_POST['atelier_action'] ?? '')));
		$allowed_actions = ['toggle_featured', 'mark_sold', 'archive', 'restore', 'delete'];
		if (! in_array($action, $allowed_actions, true) || get_post_type($id) !== 'product') {
			wp_safe_redirect(self::url('produtos'));
			exit;
		}
		$product = wc_get_product($id);
		if (! $product instanceof WC_Product) {
			wp_safe_redirect(self::url('produtos'));
			exit;
		}

		switch ($action) {
			case 'toggle_featured':
				$next = ! $product->get_featured();
				$product->set_featured($next);
				$product->save();
				update_post_meta($id, '_vesta_bestseller', $next ? 'yes' : 'no');
				$ids = array_map('absint', (array) get_option('vesta_bestsellers', []));
				if ($next && ! in_array($id, $ids, true)) {
					$ids[] = $id;
				}
				if (! $next) {
					$ids = array_values(array_diff($ids, [$id]));
				}
				update_option('vesta_bestsellers', $ids);
				break;
			case 'mark_sold':
				$product->set_stock_quantity(0);
				$product->set_stock_status('outofstock');
				$product->save();
				update_post_meta($id, '_vesta_status', 'SOLD');
				break;
			case 'archive':
				wp_update_post(['ID' => $id, 'post_status' => 'private']);
				update_post_meta($id, '_vesta_status', 'ARCHIVED');
				break;
			case 'restore':
				wp_update_post(['ID' => $id, 'post_status' => 'publish']);
				$product->set_stock_status('instock');
				if ((int) $product->get_stock_quantity() <= 0) {
					$product->set_stock_quantity(1);
				}
				$product->save();
				update_post_meta($id, '_vesta_status', 'AVAILABLE');
				break;
			case 'delete':
				wp_trash_post($id);
				break;
		}

		wp_safe_redirect(self::url('produtos'));
		exit;
	}

	public static function handle_toggle_bestseller(): void {
		self::require_post();
		$id = absint($_POST['product_id'] ?? 0);
		$add = ! empty($_POST['add']);
		if (get_post_type($id) !== 'product') {
			wp_safe_redirect(self::url('best-sellers'));
			exit;
		}
		$product = wc_get_product($id);
		if ($product instanceof WC_Product) {
			$product->set_featured($add);
			$product->save();
			update_post_meta($id, '_vesta_bestseller', $add ? 'yes' : 'no');
			$ids = array_map('absint', (array) get_option('vesta_bestsellers', []));
			if ($add && ! in_array($id, $ids, true)) {
				$ids[] = $id;
			}
			if (! $add) {
				$ids = array_values(array_diff($ids, [$id]));
			}
			update_option('vesta_bestsellers', $ids);
		}
		wp_safe_redirect(self::url('best-sellers'));
		exit;
	}

	public static function handle_save_category(): void {
		self::require_post();
		$name = sanitize_text_field(wp_unslash((string) ($_POST['name'] ?? '')));
		$desc = sanitize_textarea_field(wp_unslash((string) ($_POST['description'] ?? '')));
		if ($name !== '') {
			wp_insert_term($name, 'product_cat', ['description' => $desc]);
		}
		wp_safe_redirect(self::url('categorias'));
		exit;
	}

	public static function handle_category_action(): void {
		self::require_post();
		$term_id = absint($_POST['term_id'] ?? 0);
		$action = sanitize_text_field(wp_unslash((string) ($_POST['atelier_action'] ?? '')));
		if ($term_id <= 0) {
			wp_safe_redirect(self::url('categorias'));
			exit;
		}
		if ($action === 'delete') {
			$term = get_term($term_id, 'product_cat');
			if ($term instanceof WP_Term && (int) $term->count === 0) {
				wp_delete_term($term_id, 'product_cat');
			}
		}
		wp_safe_redirect(self::url('categorias'));
		exit;
	}

	public static function handle_order_status(): void {
		self::require_post();
		$order_id = absint($_POST['order_id'] ?? 0);
		$status = sanitize_key(wp_unslash((string) ($_POST['status'] ?? '')));
		$allowed = [];
		foreach (array_keys(wc_get_order_statuses()) as $key) {
			$allowed[] = str_replace('wc-', '', (string) $key);
		}
		$order = wc_get_order($order_id);
		if ($order instanceof WC_Order && $status !== '' && in_array($status, $allowed, true)) {
			$order->update_status($status, '', true);
		}
		wp_safe_redirect(self::url('pedidos/' . $order_id));
		exit;
	}

	public static function handle_promote_user(): void {
		self::require_post();
		// Só administrador — evita shop_manager promover outros.
		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('Sem permissão para promover usuários.', 'vesta-core'), 403);
		}
		$user_id = absint($_POST['user_id'] ?? 0);
		$user = get_user_by('id', $user_id);
		if (
			$user instanceof WP_User
			&& (int) $user->ID !== (int) get_current_user_id()
			&& ! user_can($user, 'manage_options')
			&& ! in_array('administrator', (array) $user->roles, true)
		) {
			$user->set_role('shop_manager');
		}
		wp_safe_redirect(self::url('clientes'));
		exit;
	}
}
