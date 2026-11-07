<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

final class Vesta_Core_Taxonomy_Look {
	public static function init(): void {
		add_action('init', [self::class, 'register']);
	}

	public static function register(): void {
		register_taxonomy(
			'look',
			['product'],
			[
				'labels' => [
					'name'          => __('Estilos', 'vesta-core'),
					'singular_name' => __('Estilo', 'vesta-core'),
					'search_items'  => __('Buscar looks', 'vesta-core'),
					'all_items'     => __('Todos os looks', 'vesta-core'),
					'edit_item'     => __('Editar look', 'vesta-core'),
					'update_item'   => __('Atualizar look', 'vesta-core'),
					'add_new_item'  => __('Adicionar look', 'vesta-core'),
					'new_item_name' => __('Novo look', 'vesta-core'),
					'menu_name'     => __('Estilos', 'vesta-core'),
				],
				'hierarchical'      => false,
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'rewrite'           => ['slug' => 'look'],
				'show_in_rest'      => true,
			]
		);
	}
}
