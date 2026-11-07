<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

final class Vesta_Core_Contact {
	private const RATE_LIMIT = 5;
	private const RATE_WINDOW = HOUR_IN_SECONDS;

	public static function init(): void {
		add_action('init', [self::class, 'register_cpt']);
		add_shortcode('vesta_contact_form', [self::class, 'shortcode']);
		add_action('admin_post_nopriv_vesta_contact', [self::class, 'handle']);
		add_action('admin_post_vesta_contact', [self::class, 'handle']);
	}

	public static function register_cpt(): void {
		register_post_type('vesta_message', [
			'labels' => [
				'name'          => __('Mensagens', 'vesta-core'),
				'singular_name' => __('Mensagem', 'vesta-core'),
				'menu_name'     => __('Mensagens', 'vesta-core'),
			],
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'vesta-atelier',
			'supports'            => ['title', 'editor'],
			'capability_type'     => 'shop_order',
			'map_meta_cap'        => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
		]);
	}

	public static function shortcode(): string {
		ob_start();
		$status = isset($_GET['contato']) ? sanitize_key((string) $_GET['contato']) : '';
		if ($status === 'ok') {
			echo '<p class="woocommerce-message">' . esc_html__('Mensagem enviada. A Kra de Rica retorna em breve.', 'vesta-core') . '</p>';
		} elseif ($status === 'rate') {
			echo '<p class="woocommerce-error">' . esc_html__('Muitas mensagens deste dispositivo. Tente novamente mais tarde.', 'vesta-core') . '</p>';
		} elseif ($status === 'erro') {
			echo '<p class="woocommerce-error">' . esc_html__('Não foi possível enviar. Confira os campos e tente de novo.', 'vesta-core') . '</p>';
		}
		?>
		<form class="contact-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<input type="hidden" name="action" value="vesta_contact">
			<?php wp_nonce_field('vesta_contact', 'vesta_contact_nonce'); ?>
			<?php // Honeypot anti-bot — deve ficar vazio. ?>
			<label class="vesta-hp" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;" for="vesta_website">
				<?php esc_html_e('Não preencha este campo', 'vesta-core'); ?>
			</label>
			<input class="vesta-hp" id="vesta_website" name="website" type="text" tabindex="-1" autocomplete="off" value="">
			<label for="vesta_name"><?php esc_html_e('Nome', 'vesta-core'); ?></label>
			<input id="vesta_name" name="name" type="text" required maxlength="120">
			<label for="vesta_email"><?php esc_html_e('E-mail', 'vesta-core'); ?></label>
			<input id="vesta_email" name="email" type="email" required maxlength="190">
			<label for="vesta_whatsapp"><?php esc_html_e('WhatsApp (opcional)', 'vesta-core'); ?></label>
			<input id="vesta_whatsapp" name="whatsapp" type="text" maxlength="40">
			<label for="vesta_message"><?php esc_html_e('Mensagem', 'vesta-core'); ?></label>
			<textarea id="vesta_message" name="message" rows="5" required maxlength="4000"></textarea>
			<div class="form-actions">
				<button class="btn btn--gold" type="submit"><?php esc_html_e('Enviar mensagem', 'vesta-core'); ?></button>
			</div>
			<p style="color:var(--taupe);font-size:.875rem"><?php esc_html_e('Ao enviar, sua mensagem é salva de forma privada para retorno da Kra de Rica.', 'vesta-core'); ?></p>
		</form>
		<?php
		return (string) ob_get_clean();
	}

	public static function handle(): void {
		$redirect_base = wp_get_referer() ?: home_url('/contato/');

		if (! isset($_POST['vesta_contact_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['vesta_contact_nonce'])), 'vesta_contact')) {
			wp_safe_redirect(add_query_arg('contato', 'erro', $redirect_base));
			exit;
		}

		// Honeypot preenchido = bot.
		$honeypot = sanitize_text_field(wp_unslash((string) ($_POST['website'] ?? '')));
		if ($honeypot !== '') {
			wp_safe_redirect(add_query_arg('contato', 'ok', $redirect_base));
			exit;
		}

		$ip = self::client_ip();
		$rate_key = 'vesta_contact_' . md5($ip);
		$hits = (int) get_transient($rate_key);
		if ($hits >= self::RATE_LIMIT) {
			wp_safe_redirect(add_query_arg('contato', 'rate', $redirect_base));
			exit;
		}

		$name     = sanitize_text_field(wp_unslash((string) ($_POST['name'] ?? '')));
		$email    = sanitize_email(wp_unslash((string) ($_POST['email'] ?? '')));
		$whatsapp = sanitize_text_field(wp_unslash((string) ($_POST['whatsapp'] ?? '')));
		$message  = sanitize_textarea_field(wp_unslash((string) ($_POST['message'] ?? '')));

		$name = substr($name, 0, 120);
		$whatsapp = substr($whatsapp, 0, 40);
		$message = substr($message, 0, 4000);

		if ($name === '' || $email === '' || ! is_email($email) || $message === '') {
			wp_safe_redirect(add_query_arg('contato', 'erro', $redirect_base));
			exit;
		}

		$post_id = wp_insert_post([
			'post_type'    => 'vesta_message',
			'post_status'  => 'private',
			'post_title'   => $name . ' — ' . $email,
			'post_content' => $message . "\n\nWhatsApp: " . $whatsapp,
		], true);

		if (is_wp_error($post_id) || ! $post_id) {
			wp_safe_redirect(add_query_arg('contato', 'erro', $redirect_base));
			exit;
		}

		update_post_meta((int) $post_id, '_vesta_email', $email);
		update_post_meta((int) $post_id, '_vesta_whatsapp', $whatsapp);

		set_transient($rate_key, $hits + 1, self::RATE_WINDOW);

		$to = (string) get_option('vesta_contact_email', '');
		if ($to === '' || ! is_email($to)) {
			$to = (string) get_option('admin_email');
		}
		if (is_email($to)) {
			$subject = sprintf('[Kra de Rica Contato] %s', $name);
			$body    = "Nome: {$name}\nE-mail: {$email}\nWhatsApp: {$whatsapp}\n\n{$message}\n";
			$headers = [
				'Content-Type: text/plain; charset=UTF-8',
				'Reply-To: ' . $name . ' <' . $email . '>',
			];
			wp_mail($to, $subject, $body, $headers);
		}

		wp_safe_redirect(add_query_arg('contato', 'ok', $redirect_base));
		exit;
	}

	private static function client_ip(): string {
		$ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
		return preg_replace('/[^0-9a-fA-F:.]/', '', $ip) ?: '0.0.0.0';
	}
}
