<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * @return array<string, string>
 */
function vesta_core_conditions(): array {
	return [
		'NEW_WITH_TAG' => __('Nova com etiqueta', 'vesta-core'),
		'EXCELLENT'    => __('Excelente', 'vesta-core'),
		'VERY_GOOD'    => __('Muito boa', 'vesta-core'),
		'GOOD'         => __('Boa', 'vesta-core'),
		'VINTAGE'      => __('Retrô', 'vesta-core'),
	];
}

/**
 * @return array<string, string>
 */
function vesta_core_image_kinds(): array {
	return [
		'MAIN'   => __('Principal', 'vesta-core'),
		'FRONT'  => __('Frente', 'vesta-core'),
		'BACK'   => __('Costas', 'vesta-core'),
		'LABEL'  => __('Etiqueta', 'vesta-core'),
		'DETAIL' => __('Detalhe', 'vesta-core'),
		'FLAW'   => __('Defeito', 'vesta-core'),
	];
}

function vesta_core_render_product_meta_block(): void {
	global $product;
	if (! $product instanceof WC_Product) {
		return;
	}
	$id        = $product->get_id();
	$condition = (string) get_post_meta($id, '_vesta_condition', true);
	$brand     = (string) get_post_meta($id, '_vesta_brand', true);
	$size      = (string) get_post_meta($id, '_vesta_size', true);
	$color     = (string) get_post_meta($id, '_vesta_color', true);
	$material  = (string) get_post_meta($id, '_vesta_material', true);
	$unique    = get_post_meta($id, '_vesta_unique_piece', true);
	$measures  = get_post_meta($id, '_vesta_measurements', true);
	if (! is_array($measures)) {
		$measures = [];
	}

	$conditions = vesta_core_conditions();
	?>
	<div class="vesta-meta-block">
		<?php if ($brand) : ?>
			<dt><?php esc_html_e('Marca', 'vesta-core'); ?></dt>
			<dd><?php echo esc_html($brand); ?></dd>
		<?php endif; ?>
		<?php if ($condition && isset($conditions[$condition])) : ?>
			<dt><?php esc_html_e('Condição', 'vesta-core'); ?></dt>
			<dd><?php echo esc_html($conditions[$condition]); ?></dd>
		<?php endif; ?>
		<?php if ($size) : ?>
			<dt><?php esc_html_e('Tamanho', 'vesta-core'); ?></dt>
			<dd><?php echo esc_html($size); ?></dd>
		<?php endif; ?>
		<?php if ($color) : ?>
			<dt><?php esc_html_e('Cor', 'vesta-core'); ?></dt>
			<dd><?php echo esc_html($color); ?></dd>
		<?php endif; ?>
		<?php if ($material) : ?>
			<dt><?php esc_html_e('Material', 'vesta-core'); ?></dt>
			<dd><?php echo esc_html($material); ?></dd>
		<?php endif; ?>
		<?php if ($unique === 'yes' || $unique === '1' || $unique === '') : ?>
			<dt><?php esc_html_e('Disponibilidade', 'vesta-core'); ?></dt>
			<dd><?php esc_html_e('Peça única', 'vesta-core'); ?></dd>
		<?php endif; ?>
		<?php if ($measures) : ?>
			<dt><?php esc_html_e('Medidas', 'vesta-core'); ?></dt>
			<dd>
				<?php
				$parts = [];
				$map   = [
					'bust'     => 'Busto',
					'waist'    => 'Cintura',
					'hip'      => 'Quadril',
					'length'   => 'Comprimento',
					'shoulder' => 'Ombro',
				];
				foreach ($map as $key => $label) {
					if (! empty($measures[$key])) {
						$parts[] = $label . ': ' . $measures[$key];
					}
				}
				echo esc_html(implode(' · ', $parts));
				?>
			</dd>
		<?php endif; ?>
	</div>
	<?php
}
