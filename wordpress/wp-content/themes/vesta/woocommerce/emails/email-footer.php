<?php
/**
 * Rodapé de e-mail Vesta.
 *
 * @package Vesta
 * @var WC_Email|null $email
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}
?>
						</td>
					</tr>
					<tr>
						<td style="padding:8px 24px 28px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.5;color:#8a857c;text-align:center;">
							VESTA MODA · <?php echo esc_html(wp_strip_all_tags(get_bloginfo('name', 'display'))); ?><br>
							<a href="<?php echo esc_url(home_url('/')); ?>" style="color:#c4a574;text-decoration:underline;"><?php esc_html_e('Continuar na loja', 'vesta'); ?></a>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
