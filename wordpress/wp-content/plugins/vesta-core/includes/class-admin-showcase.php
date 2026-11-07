<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

final class Vesta_Core_Admin_Showcase {
	public static function init(): void {
		add_action('admin_menu', [self::class, 'menu']);
	}

	public static function menu(): void {
		add_menu_page(
			__('Kra de Rica', 'vesta-core'),
			__('Loja', 'vesta-core'),
			'manage_woocommerce',
			'vesta-atelier',
			[self::class, 'render_atelier_redirect'],
			'dashicons-awards',
			56
		);
		add_submenu_page(
			'vesta-atelier',
			__('Atelier (painel)', 'vesta-core'),
			__('Atelier (painel)', 'vesta-core'),
			'edit_products',
			'vesta-atelier',
			[self::class, 'render_atelier_redirect']
		);
		add_submenu_page(
			'vesta-atelier',
			__('Vitrine', 'vesta-core'),
			__('Vitrine', 'vesta-core'),
			'manage_woocommerce',
			'vesta-showcase',
			[self::class, 'render_vitrine']
		);
		add_submenu_page(
			'vesta-atelier',
			__('Mais vendidos', 'vesta-core'),
			__('Mais vendidos', 'vesta-core'),
			'manage_woocommerce',
			'vesta-bestsellers',
			[self::class, 'render_bestsellers']
		);
	}

	public static function render_atelier_redirect(): void {
		if (! current_user_can('edit_products')) {
			return;
		}
		$url = home_url('/admin/');
		echo '<div class="wrap"><h1>Atelier Kra de Rica</h1>';
		echo '<p>O painel da loja fica no Atelier (não no wp-admin). A equipe cadastra peças, mais vendidos e pedidos por lá.</p>';
		echo '<p><a class="button button-primary button-hero" href="' . esc_url($url) . '">Abrir Atelier</a></p>';
		echo '<p class="description">' . esc_html($url) . '</p>';
		echo '<p class="description">Configuração técnica (Mercado Pago / frete): use <code>/wp-admin/?wp=1</code>.</p></div>';
	}

	public static function render_vitrine(): void {
		if (! current_user_can('manage_woocommerce')) {
			return;
		}
		if (isset($_POST['vesta_vitrine_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['vesta_vitrine_nonce'])), 'vesta_vitrine')) {
			$ids = isset($_POST['featured_ids']) ? array_map('absint', (array) $_POST['featured_ids']) : [];
			self::set_featured($ids);
			echo '<div class="notice notice-success"><p>' . esc_html__('Vitrine atualizada.', 'vesta-core') . '</p></div>';
		}
		$products = wc_get_products(['status' => 'publish', 'limit' => 100, 'orderby' => 'title', 'order' => 'ASC']);
		?>
		<div class="wrap">
			<h1><?php esc_html_e('Vitrine (destaques)', 'vesta-core'); ?></h1>
			<p><?php esc_html_e('Marque os produtos que aparecem como lançamentos/destaques na home.', 'vesta-core'); ?></p>
			<form method="post">
				<?php wp_nonce_field('vesta_vitrine', 'vesta_vitrine_nonce'); ?>
				<table class="widefat striped">
					<thead><tr><th></th><th><?php esc_html_e('Produto', 'vesta-core'); ?></th><th><?php esc_html_e('Preço', 'vesta-core'); ?></th></tr></thead>
					<tbody>
					<?php foreach ($products as $product) : ?>
						<tr>
							<td><input type="checkbox" name="featured_ids[]" value="<?php echo esc_attr((string) $product->get_id()); ?>" <?php checked($product->get_featured()); ?>></td>
							<td><?php echo esc_html($product->get_name()); ?></td>
							<td><?php echo wp_kses_post($product->get_price_html()); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button(__('Salvar vitrine', 'vesta-core')); ?>
			</form>
		</div>
		<?php
	}

	public static function render_bestsellers(): void {
		if (! current_user_can('manage_woocommerce')) {
			return;
		}
		if (isset($_POST['vesta_bs_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['vesta_bs_nonce'])), 'vesta_bs')) {
			$ids = isset($_POST['bestseller_ids']) ? array_map('absint', (array) $_POST['bestseller_ids']) : [];
			update_option('vesta_bestsellers', $ids);
			foreach (wc_get_products(['status' => 'publish', 'limit' => -1]) as $product) {
				update_post_meta($product->get_id(), '_vesta_bestseller', in_array($product->get_id(), $ids, true) ? 'yes' : 'no');
			}
			echo '<div class="notice notice-success"><p>' . esc_html__('Mais vendidos atualizados.', 'vesta-core') . '</p></div>';
		}
		$selected = get_option('vesta_bestsellers', []);
		if (! is_array($selected)) {
			$selected = [];
		}
		$selected = array_map('absint', $selected);
		$products = wc_get_products(['status' => 'publish', 'limit' => 100, 'orderby' => 'title', 'order' => 'ASC']);
		?>
		<div class="wrap">
			<h1><?php esc_html_e('Mais vendidos', 'vesta-core'); ?></h1>
			<form method="post">
				<?php wp_nonce_field('vesta_bs', 'vesta_bs_nonce'); ?>
				<table class="widefat striped">
					<thead><tr><th></th><th><?php esc_html_e('Produto', 'vesta-core'); ?></th></tr></thead>
					<tbody>
					<?php foreach ($products as $product) : ?>
						<tr>
							<td><input type="checkbox" name="bestseller_ids[]" value="<?php echo esc_attr((string) $product->get_id()); ?>" <?php checked(in_array($product->get_id(), $selected, true)); ?>></td>
							<td><?php echo esc_html($product->get_name()); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button(__('Salvar best-sellers', 'vesta-core')); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * @param array<int, int> $ids
	 */
	private static function set_featured(array $ids): void {
		foreach (wc_get_products(['status' => 'publish', 'limit' => -1]) as $product) {
			$product->set_featured(in_array($product->get_id(), $ids, true));
			$product->save();
		}
	}
}
