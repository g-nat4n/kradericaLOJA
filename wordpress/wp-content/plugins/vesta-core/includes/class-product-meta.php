<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

final class Vesta_Core_Product_Meta {
	public static function init(): void {
		add_action('woocommerce_product_options_general_product_data', [self::class, 'render_fields']);
		add_action('woocommerce_process_product_meta', [self::class, 'save_fields']);
	}

	public static function render_fields(): void {
		echo '<div class="options_group">';
		woocommerce_wp_text_input([
			'id'    => '_vesta_brand',
			'label' => __('Marca', 'vesta-core'),
		]);
		woocommerce_wp_text_input([
			'id'    => '_vesta_size',
			'label' => __('Tamanho', 'vesta-core'),
		]);
		woocommerce_wp_text_input([
			'id'    => '_vesta_color',
			'label' => __('Cor', 'vesta-core'),
		]);
		woocommerce_wp_text_input([
			'id'    => '_vesta_material',
			'label' => __('Material', 'vesta-core'),
		]);
		woocommerce_wp_select([
			'id'      => '_vesta_condition',
			'label'   => __('Condição', 'vesta-core'),
			'options' => array_merge(['' => __('Selecionar', 'vesta-core')], vesta_core_conditions()),
		]);
		woocommerce_wp_checkbox([
			'id'          => '_vesta_unique_piece',
			'label'       => __('Peça única', 'vesta-core'),
			'description' => __('Estoque padrão 1; ao vender, fica esgotada.', 'vesta-core'),
			'value'       => get_post_meta(get_the_ID(), '_vesta_unique_piece', true) ?: 'yes',
			'cbvalue'     => 'yes',
		]);
		woocommerce_wp_checkbox([
			'id'    => '_vesta_bestseller',
			'label' => __('Mais vendido', 'vesta-core'),
		]);
		woocommerce_wp_textarea_input([
			'id'    => '_vesta_story',
			'label' => __('História da peça', 'vesta-core'),
		]);

		$measures = get_post_meta(get_the_ID(), '_vesta_measurements', true);
		if (! is_array($measures)) {
			$measures = [];
		}
		foreach (['bust' => 'Busto', 'waist' => 'Cintura', 'hip' => 'Quadril', 'length' => 'Comprimento', 'shoulder' => 'Ombro'] as $key => $label) {
			woocommerce_wp_text_input([
				'id'                => '_vesta_measure_' . $key,
				'label'             => $label,
				'value'             => $measures[$key] ?? '',
				'custom_attributes' => ['data-measure' => $key],
			]);
		}
		echo '</div>';
	}

	public static function save_fields(int $product_id): void {
		$fields = ['_vesta_brand', '_vesta_size', '_vesta_color', '_vesta_material', '_vesta_condition', '_vesta_story'];
		foreach ($fields as $field) {
			if (isset($_POST[$field])) {
				update_post_meta($product_id, $field, sanitize_text_field(wp_unslash((string) $_POST[$field])));
			}
		}

		$unique = isset($_POST['_vesta_unique_piece']) ? 'yes' : 'no';
		update_post_meta($product_id, '_vesta_unique_piece', $unique);
		update_post_meta($product_id, '_vesta_bestseller', isset($_POST['_vesta_bestseller']) ? 'yes' : 'no');

		$measures = [];
		foreach (['bust', 'waist', 'hip', 'length', 'shoulder'] as $key) {
			$raw = isset($_POST['_vesta_measure_' . $key]) ? sanitize_text_field(wp_unslash((string) $_POST['_vesta_measure_' . $key])) : '';
			if ($raw !== '') {
				$measures[$key] = $raw;
			}
		}
		update_post_meta($product_id, '_vesta_measurements', $measures);

		if ($unique === 'yes') {
			update_post_meta($product_id, '_manage_stock', 'yes');
			$stock = get_post_meta($product_id, '_stock', true);
			if ($stock === '' || $stock === false) {
				update_post_meta($product_id, '_stock', '1');
			}
		}

		self::sync_bestseller_option($product_id);
	}

	private static function sync_bestseller_option(int $product_id): void {
		$ids = get_option('vesta_bestsellers', []);
		if (! is_array($ids)) {
			$ids = [];
		}
		$ids = array_map('absint', $ids);
		$flag = get_post_meta($product_id, '_vesta_bestseller', true) === 'yes';
		if ($flag && ! in_array($product_id, $ids, true)) {
			$ids[] = $product_id;
		}
		if (! $flag) {
			$ids = array_values(array_diff($ids, [$product_id]));
		}
		update_option('vesta_bestsellers', $ids);
	}
}
