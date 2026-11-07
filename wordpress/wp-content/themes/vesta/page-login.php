<?php
/**
 * Template Name: Login
 * Template Post Type: page
 */
declare(strict_types=1);

if (is_user_logged_in()) {
	wp_safe_redirect(wc_get_page_permalink('myaccount'));
	exit;
}

get_header();
$register_url = home_url('/cadastro/');
$lost_url     = home_url('/recuperar-senha/');
?>
<main class="site-main woo-main--auth">
	<div class="vesta-auth">
		<div class="vesta-auth__bg" aria-hidden="true"></div>
		<div class="vesta-auth__intro">
			<p class="eyebrow">Kra de Rica</p>
			<h1 class="vesta-auth__headline">Bem-vinda de volta.</h1>
			<p class="vesta-auth__lead">Entre para acompanhar pedidos, favoritos e finalizar compras com mais agilidade.</p>
			<span class="gold-line" aria-hidden="true"></span>
		</div>
		<div class="vesta-auth__card">
			<p class="eyebrow mobile-only">Kra de Rica</p>
			<h1 class="vesta-auth__title">Entrar</h1>
			<p class="vesta-auth__sub">Use o e-mail e a senha da sua conta.</p>

			<?php wc_print_notices(); ?>

			<form class="vesta-auth__form woocommerce-form woocommerce-form-login" method="post" action="">
				<label class="vesta-field">
					<span>E-mail</span>
					<input type="text" name="username" autocomplete="username" inputmode="email" required class="input-text">
				</label>
				<label class="vesta-field">
					<span>Senha</span>
					<input type="password" name="password" autocomplete="current-password" required class="input-text">
				</label>
				<label class="vesta-auth__remember">
					<input type="checkbox" name="rememberme" value="forever">
					<span>Lembrar-me</span>
				</label>
				<?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
				<?php
				$redirect_to = wc_get_page_permalink('myaccount');
				if (! empty($_GET['redirect_to'])) {
					$candidate = esc_url_raw(wp_unslash((string) $_GET['redirect_to']));
					if ($candidate && wp_validate_redirect($candidate, false)) {
						$redirect_to = $candidate;
					}
				}
				?>
				<input type="hidden" name="redirect" value="<?php echo esc_url($redirect_to); ?>">
				<button type="submit" class="btn btn--wine vesta-auth__submit" name="login" value="Entrar">Entrar</button>
			</form>

			<div class="vesta-auth__links">
				<p><a href="<?php echo esc_url($lost_url); ?>">Esqueci a senha</a></p>
				<p>Ainda não tem conta? <a href="<?php echo esc_url($register_url); ?>">Criar cadastro</a></p>
				<p class="vesta-auth__footnote">Equipe da loja? Após entrar, você vai direto ao atelier.</p>
			</div>
		</div>
	</div>
</main>
<?php
get_footer();
