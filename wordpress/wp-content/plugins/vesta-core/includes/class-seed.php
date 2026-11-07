<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

final class Vesta_Core_Seed {
	public static function init(): void {
		if (defined('WP_CLI') && WP_CLI) {
			WP_CLI::add_command('vesta seed', [self::class, 'cli_seed']);
		}
	}

	/**
	 * @param array<int, string> $args
	 * @param array<string, string> $assoc
	 */
	public static function cli_seed(array $args = [], array $assoc = []): void {
		if (! class_exists('WooCommerce')) {
			if (defined('WP_CLI') && WP_CLI) {
				WP_CLI::error('WooCommerce precisa estar ativo.');
			}
			return;
		}

		$categories = [
			['Blazers', 'blazers', 'Ombro, estrutura e presença.'],
			['Casacos', 'casacos', 'Camadas com peso e caimento.'],
			['Camisas', 'camisas', 'Trama limpa, colarinho e caimento.'],
			['Vestidos', 'vestidos', 'Silhuetas com presença, do cotidiano à noite.'],
			['Sapatos', 'sapatos', 'Social, casual e o detalhe que apoia o look.'],
			['Bolsas', 'bolsas', 'Couro, estrutura e o detalhe que fecha o look.'],
		];

		$cat_ids = [];
		foreach ($categories as $i => $cat) {
			$existing = get_term_by('slug', $cat[1], 'product_cat');
			if ($existing) {
				$cat_ids[$cat[1]] = (int) $existing->term_id;
				continue;
			}
			$result = wp_insert_term($cat[0], 'product_cat', [
				'slug'        => $cat[1],
				'description' => $cat[2],
			]);
			if (! is_wp_error($result)) {
				$cat_ids[$cat[1]] = (int) $result['term_id'];
				update_term_meta($cat_ids[$cat[1]], 'order', $i + 1);
			}
		}

		$look = get_term_by('slug', 'noite-quente', 'look');
		if (! $look) {
			$look_result = wp_insert_term('Noite quente', 'look', [
				'slug'        => 'noite-quente',
				'description' => 'Um vestido, um casaco leve e um acessório de couro.',
			]);
			$look_id = is_wp_error($look_result) ? 0 : (int) $look_result['term_id'];
		} else {
			$look_id = (int) $look->term_id;
		}

		$products = [
			[
				'name' => 'Vestido Crepe Ember', 'slug' => 'vestido-crepe-ember', 'cat' => 'vestidos',
				'price' => '420.00', 'compare' => '480.00', 'brand' => 'Arquivo', 'size' => 'M', 'color' => 'Terracota',
				'condition' => 'EXCELLENT', 'material' => 'Crepe', 'featured' => true, 'bestseller' => true,
				'desc' => 'Crepe fluido, alças finas e comprimento midi.',
				'story' => 'Entrou na Vesta com etiqueta de boutique paulistana dos anos 2000 ainda costurada por dentro.',
				'measures' => ['bust' => '90 cm', 'waist' => '74 cm', 'length' => '118 cm'],
				'image' => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?auto=format&fit=crop&w=1200&q=80',
				'gallery' => [
					'https://images.unsplash.com/photo-1566174053879-31528523f8ae?auto=format&fit=crop&w=1200&q=80',
					'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=1200&q=80',
				],
				'look' => true,
			],
			[
				'name' => 'Blazer Lã Harbor', 'slug' => 'blazer-la-harbor', 'cat' => 'blazers',
				'price' => '560.00', 'compare' => '620.00', 'brand' => 'Harbor Atelier', 'size' => 'M', 'color' => 'Camelo',
				'condition' => 'VERY_GOOD', 'material' => 'Lã mista', 'featured' => true, 'bestseller' => true,
				'desc' => 'Ombro marcado, dois botões e lining intacto.',
				'story' => '',
				'measures' => ['shoulder' => '41 cm', 'bust' => '96 cm', 'length' => '72 cm'],
				'image' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?auto=format&fit=crop&w=1200&q=80',
				'gallery' => [
					'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=1200&q=80',
				],
			],
			[
				'name' => 'Camisa Poplin Marfim', 'slug' => 'camisa-poplin-marfim', 'cat' => 'camisas',
				'price' => '220.00', 'compare' => '260.00', 'brand' => 'Maison Linen', 'size' => 'M', 'color' => 'Marfim',
				'condition' => 'NEW_WITH_TAG', 'material' => 'Algodão poplin', 'featured' => true, 'bestseller' => true,
				'desc' => 'Algodão poplin, colarinho clássico e punho com botão.',
				'story' => '',
				'measures' => ['bust' => '98 cm', 'length' => '70 cm'],
				'image' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&w=1200&q=80',
			],
			[
				'name' => 'Trench Soleil', 'slug' => 'trench-soleil', 'cat' => 'casacos',
				'price' => '419.90', 'compare' => '479.90', 'brand' => 'Soleil', 'size' => 'M', 'color' => 'Khaki',
				'condition' => 'EXCELLENT', 'material' => 'Algodão', 'featured' => true, 'bestseller' => true,
				'desc' => 'Trench coat urbano, corte com memória de rua.',
				'story' => '',
				'measures' => ['bust' => '100 cm', 'length' => '98 cm'],
				'image' => 'https://images.unsplash.com/photo-1539533018447-63fcce2678e3?auto=format&fit=crop&w=1200&q=80',
			],
			[
				'name' => 'Vestido Aurora', 'slug' => 'vestido-aurora', 'cat' => 'vestidos',
				'price' => '419.90', 'compare' => '479.90', 'brand' => 'Aurora', 'size' => 'P', 'color' => 'Vinho',
				'condition' => 'EXCELLENT', 'material' => 'Viscose', 'featured' => false, 'bestseller' => true,
				'desc' => 'Vestido de festa com manga bufante e presença dramática.',
				'story' => '',
				'measures' => ['bust' => '86 cm', 'waist' => '70 cm', 'length' => '120 cm'],
				'image' => 'https://images.pexels.com/photos/985635/pexels-photo-985635.jpeg?auto=compress&cs=tinysrgb&w=1200',
				'gallery' => [
					'https://images.pexels.com/photos/985635/pexels-photo-985635.jpeg?auto=compress&cs=tinysrgb&w=1200',
					'https://images.unsplash.com/photo-1566174053879-31528523f8ae?auto=format&fit=crop&w=1200&q=80',
				],
				'look' => true,
			],
			[
				'name' => 'Bolsa Alba', 'slug' => 'bolsa-alba', 'cat' => 'bolsas',
				'price' => '279.90', 'compare' => '299.90', 'brand' => 'Alba', 'size' => 'Único', 'color' => 'Caramelo',
				'condition' => 'EXCELLENT', 'material' => 'Couro', 'featured' => true, 'bestseller' => true,
				'desc' => 'Bolsa de couro texturizado, alça curta e presença casual.',
				'story' => '',
				'measures' => [],
				'image' => 'https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg?auto=compress&cs=tinysrgb&w=1200',
			],
			[
				'name' => 'Sapato Roma', 'slug' => 'sapato-roma', 'cat' => 'sapatos',
				'price' => '289.90', 'compare' => '319.90', 'brand' => 'Roma', 'size' => '40', 'color' => 'Caramelo',
				'condition' => 'VINTAGE', 'material' => 'Couro', 'featured' => false, 'bestseller' => true,
				'desc' => 'Sapato social de couro vintage, palmilha original e sola honesta.',
				'story' => '',
				'measures' => [],
				'image' => 'https://images.unsplash.com/photo-1449505278894-297fdb3edbc1?auto=format&fit=crop&w=1200&q=80',
				'gallery' => [
					'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=1200&q=80',
					'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&w=1200&q=80',
				],
			],
			[
				'name' => 'Blazer Siena', 'slug' => 'blazer-siena', 'cat' => 'blazers',
				'price' => '369.90', 'compare' => '399.90', 'brand' => 'Siena', 'size' => 'M', 'color' => 'Camelo',
				'condition' => 'VERY_GOOD', 'material' => 'Lã mista', 'featured' => true, 'bestseller' => true,
				'desc' => 'Blazer marrom elegante, ombro marcado e caimento de boutique.',
				'story' => '',
				'measures' => ['shoulder' => '40 cm', 'bust' => '94 cm', 'length' => '70 cm'],
				'image' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?auto=format&fit=crop&w=1200&q=80',
				'gallery' => [
					'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=1200&q=80',
				],
			],
		];

		$bestseller_ids = [];
		foreach ($products as $data) {
			$existing = get_page_by_path($data['slug'], OBJECT, 'product');
			if ($existing) {
				$product_id = (int) $existing->ID;
			} else {
				$product = new WC_Product_Simple();
				$product->set_name($data['name']);
				$product->set_slug($data['slug']);
				$product->set_status('publish');
				$product->set_catalog_visibility('visible');
				$product->set_description($data['desc']);
				$product->set_short_description($data['desc']);
				$product->set_regular_price($data['compare']);
				$product->set_sale_price($data['price']);
				$product->set_price($data['price']);
				$product->set_manage_stock(true);
				$product->set_stock_quantity(1);
				$product->set_stock_status('instock');
				$product->set_sold_individually(true);
				$product->set_featured((bool) $data['featured']);
				if (! empty($cat_ids[$data['cat']])) {
					$product->set_category_ids([$cat_ids[$data['cat']]]);
				}
				$product_id = $product->save();
			}

			update_post_meta($product_id, '_vesta_brand', $data['brand']);
			update_post_meta($product_id, '_vesta_size', $data['size']);
			update_post_meta($product_id, '_vesta_color', $data['color']);
			update_post_meta($product_id, '_vesta_material', $data['material']);
			update_post_meta($product_id, '_vesta_condition', $data['condition']);
			update_post_meta($product_id, '_vesta_unique_piece', 'yes');
			update_post_meta($product_id, '_vesta_story', $data['story']);
			update_post_meta($product_id, '_vesta_measurements', $data['measures']);
			update_post_meta($product_id, '_vesta_bestseller', ! empty($data['bestseller']) ? 'yes' : 'no');
			update_post_meta($product_id, '_vesta_status', 'AVAILABLE');

			if (! empty($data['look']) && $look_id) {
				wp_set_object_terms($product_id, [$look_id], 'look');
			}
			if (! empty($data['bestseller'])) {
				$bestseller_ids[] = $product_id;
			}

			if (! has_post_thumbnail($product_id) && ! empty($data['image'])) {
				self::sideload_image($data['image'], $product_id);
			}

			// Galeria extra (miniaturas na PDP).
			if (! empty($data['gallery']) && is_array($data['gallery'])) {
				$product_obj = wc_get_product($product_id);
				$existing_gal = $product_obj ? $product_obj->get_gallery_image_ids() : [];
				if ($product_obj && count($existing_gal) < 1) {
					$gallery_ids = [];
					foreach ($data['gallery'] as $gal_url) {
						$att = self::sideload_image_id((string) $gal_url, $product_id);
						if ($att) {
							$gallery_ids[] = $att;
						}
					}
					if ($gallery_ids) {
						$product_obj->set_gallery_image_ids($gallery_ids);
						$product_obj->save();
					}
				}
			}
		}

		update_option('vesta_bestsellers', $bestseller_ids);

		// Posts do Diário
		$articles = [
			['Menos peças, mais histórias', 'Um olhar sobre escolhas que resistem ao tempo.', 'https://images.pexels.com/photos/5709661/pexels-photo-5709661.jpeg?auto=compress&cs=tinysrgb&w=800'],
			['O tempo das peças com presença', 'Texturas, cortes e detalhes com afeto.', 'https://images.pexels.com/photos/3755706/pexels-photo-3755706.jpeg?auto=compress&cs=tinysrgb&w=800'],
			['Vestir a própria linguagem', 'Moda como linguagem cotidiana.', 'https://images.pexels.com/photos/7679720/pexels-photo-7679720.jpeg?auto=compress&cs=tinysrgb&w=800'],
		];
		foreach ($articles as $article) {
			$found = get_posts([
				'post_type'      => 'post',
				'title'          => $article[0],
				'posts_per_page' => 1,
				'post_status'    => 'any',
			]);
			if ($found) {
				continue;
			}
			$post_id = wp_insert_post([
				'post_title'   => $article[0],
				'post_content' => $article[1],
				'post_status'  => 'publish',
				'post_type'    => 'post',
			]);
			if ($post_id && ! is_wp_error($post_id)) {
				self::sideload_image($article[2], (int) $post_id);
			}
		}

		// Página templates
		foreach (['a-loja' => 'page-a-loja.php', 'a-vesta' => 'page-a-loja.php', 'contato' => 'page-contato.php', 'diario' => 'page-diario.php'] as $slug => $template) {
			$page = get_page_by_path($slug);
			if ($page) {
				update_post_meta($page->ID, '_wp_page_template', $template);
			}
		}

		if (defined('WP_CLI') && WP_CLI) {
			WP_CLI::success('Seed Vesta concluído (' . count($products) . ' produtos).');
		}
	}

	private static function sideload_image(string $url, int $post_id): void {
		$att_id = self::sideload_image_id($url, $post_id);
		if ($att_id) {
			set_post_thumbnail($post_id, $att_id);
		}
	}

	private static function sideload_image_id(string $url, int $post_id): int {
		if (! function_exists('media_sideload_image')) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
		$att_id = media_sideload_image($url, $post_id, null, 'id');
		return (! is_wp_error($att_id) && $att_id) ? (int) $att_id : 0;
	}
}
