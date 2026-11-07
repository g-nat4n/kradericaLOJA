<?php
/**
 * Login / cadastro — visual LoginForm do Next.
 *
 * @package Vesta
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

do_action('woocommerce_before_customer_login_form');
?>
<div class="vesta-auth" id="customer_login">
	<div class="vesta-auth__bg" aria-hidden="true"></div>

	<div class="vesta-auth__intro">
		<p class="eyebrow">Kra de Rica</p>
		<h1 class="vesta-auth__headline">Bem-vinda de volta.</h1>
		<p class="vesta-auth__lead">Entre para acompanhar pedidos, favoritos e finalizar compras com mais agilidade.</p>
		<span class="gold-line" aria-hidden="true"></span>
	</div>

	<div class="vesta-auth__card">
		<p class="eyebrow mobile-only">Kra de Rica</p>
		<h2 class="vesta-auth__title">Entrar</h2>
		<p class="vesta-auth__sub">Use o e-mail e a senha da sua conta.</p>

		<form class="woocommerce-form woocommerce-form-login login vesta-auth__form" method="post" action="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">
			<?php do_action('woocommerce_login_form_start'); ?>

			<label class="vesta-field">
				<span>E-mail</span>
				<input type="email" class="woocommerce-Input input-text" name="username" id="username" autocomplete="username" required>
			</label>

			<label class="vesta-field">
				<span>Senha</span>
				<input type="password" class="woocommerce-Input input-text" name="password" id="password" autocomplete="current-password" required>
			</label>

			<?php do_action('woocommerce_login_form'); ?>

			<label class="vesta-auth__remember">
				<input type="checkbox" name="rememberme" value="forever">
				<span><?php esc_html_e('Lembrar-me', 'vesta'); ?></span>
			</label>

			<?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
			<button type="submit" class="btn btn--wine vesta-auth__submit" name="login" value="<?php esc_attr_e('Entrar', 'vesta'); ?>">
				<?php esc_html_e('Entrar', 'vesta'); ?>
			</button>

			<?php do_action('woocommerce_login_form_end'); ?>
		</form>

		<div class="vesta-auth__links">
			<p>
				<a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Esqueci a senha', 'vesta'); ?></a>
			</p>
			<?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>
				<p>
					Ainda não tem conta?
					<a href="#vesta-register"><?php esc_html_e('Criar cadastro', 'vesta'); ?></a>
				</p>
			<?php endif; ?>
			<p class="vesta-auth__footnote">Equipe da loja? Após entrar, você vai direto ao atelier.</p>
		</div>

		<?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>
			<div class="vesta-auth__register" id="vesta-register">
				<h3 class="vesta-auth__title" style="font-size:1.75rem">Criar cadastro</h3>
				<form method="post" class="woocommerce-form woocommerce-form-register register vesta-auth__form" <?php do_action('woocommerce_register_form_tag'); ?>>
					<?php do_action('woocommerce_register_form_start'); ?>
					<label class="vesta-field">
						<span>E-mail</span>
						<input type="email" class="input-text" name="email" id="reg_email" autocomplete="email" required>
					</label>
					<?php if ('no' === get_option('woocommerce_registration_generate_password')) : ?>
						<label class="vesta-field">
							<span>Senha</span>
							<input type="password" class="input-text" name="password" id="reg_password" autocomplete="new-password" required>
						</label>
					<?php endif; ?>
					<?php do_action('woocommerce_register_form'); ?>
					<?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
					<button type="submit" class="btn vesta-auth__submit" name="register" value="<?php esc_attr_e('Cadastrar', 'vesta'); ?>">
						<?php esc_html_e('Cadastrar', 'vesta'); ?>
					</button>
					<?php do_action('woocommerce_register_form_end'); ?>
				</form>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
do_action('woocommerce_after_customer_login_form');
