<?php
/**
 * Endereços — área do cliente (padrão Vesta).
 *
 * @package Vesta
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$types = [
	'billing'  => __('Endereço de cobrança', 'vesta'),
	'shipping' => __('Endereço de entrega', 'vesta'),
];
?>
<section class="vesta-account vesta-account--endpoint">
	<p class="vesta-account__back">
		<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">← <?php esc_html_e('Voltar à área do cliente', 'vesta'); ?></a>
	</p>
	<p class="eyebrow"><?php esc_html_e('Área do cliente', 'vesta'); ?></p>
	<h1 class="vesta-account__hello"><?php esc_html_e('Endereços', 'vesta'); ?></h1>
	<p class="vesta-account__hint"><?php esc_html_e('Endereços usados na entrega e na cobrança.', 'vesta'); ?></p>

	<div class="vesta-account__address-grid">
		<?php foreach ($types as $name => $title) :
			$address = wc_get_account_formatted_address($name);
			$edit    = wc_get_endpoint_url('edit-address', $name, wc_get_page_permalink('myaccount'));
			?>
			<article class="vesta-account__address-card">
				<div class="vesta-account__address-head">
					<h2><?php echo esc_html($title); ?></h2>
					<a class="vesta-account__address-edit" href="<?php echo esc_url($edit); ?>">
						<?php echo $address ? esc_html__('Editar', 'vesta') : esc_html__('Adicionar', 'vesta'); ?>
					</a>
				</div>
				<?php if ($address) : ?>
					<address class="vesta-account__address-body"><?php echo wp_kses_post($address); ?></address>
				<?php else : ?>
					<p class="vesta-account__hint"><?php esc_html_e('Nenhum endereço cadastrado ainda.', 'vesta'); ?></p>
				<?php endif; ?>
			</article>
		<?php endforeach; ?>
	</div>
</section>
