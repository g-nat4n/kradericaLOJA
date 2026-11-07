<?php
/**
 * Template Name: Contato
 */
declare(strict_types=1);
get_header();

$instagram = vesta_instagram_url();
$whatsapp  = vesta_whatsapp_url('Olá! Vim pelo site da Kra de Rica.');
$maps      = vesta_maps_url();
$maps_embed = vesta_maps_embed_url();
?>
<main class="site-main">
	<section class="contact-band contact-band--page">
		<div class="container-main contact-band__grid">
			<div data-reveal>
				<p class="eyebrow">Vamos conversar</p>
				<h1 class="display">A sua história também inspira o Kra de Rica.</h1>
				<p class="lead">Conte o que você procura. Atendimento no Centro de Curitiba, com a mesma atenção da curadoria.</p>
				<p class="lead contact-band__address">
					<a href="<?php echo esc_url($maps); ?>" target="_blank" rel="noopener noreferrer">
						Rua Voluntários da Pátria, 233, Loja 05, Centro, Curitiba.
					</a>
				</p>
				<div class="contact-links">
					<a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener noreferrer">
						<span class="contact-links__icon" aria-hidden="true"><?php echo vesta_social_icon_svg('instagram'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						Instagram
					</a>
					<a href="<?php echo esc_url($whatsapp); ?>" target="_blank" rel="noopener noreferrer">
						<span class="contact-links__icon" aria-hidden="true"><?php echo vesta_social_icon_svg('whatsapp'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						WhatsApp
					</a>
				</div>
			</div>
			<div data-reveal class="contact-map">
				<iframe
					class="contact-map__frame"
					title="<?php esc_attr_e('Localização da Kra de Rica no Google Maps', 'vesta'); ?>"
					src="<?php echo esc_url($maps_embed); ?>"
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"
					allowfullscreen
				></iframe>
				<p class="contact-map__link">
					<a href="<?php echo esc_url($maps); ?>" target="_blank" rel="noopener noreferrer">Abrir no Google Maps</a>
				</p>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
