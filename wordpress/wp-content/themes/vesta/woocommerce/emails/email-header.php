<?php
/**
 * Cabeçalho de e-mail Vesta.
 *
 * @package Vesta
 * @var string $email_heading
 * @var WC_Email|null $email
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html(get_bloginfo('name', 'display')); ?></title>
</head>
<body style="margin:0;padding:0;background:#0b0b0b;color:#f5f2ea;">
	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0b0b0b;padding:24px 12px;">
		<tr>
			<td align="center">
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#111;border:1px solid #2a2a2a;">
					<tr>
						<td style="padding:28px 24px 8px;text-align:center;font-family:Georgia,'Times New Roman',serif;letter-spacing:0.28em;font-size:18px;color:#f5f2ea;">
							VESTA
						</td>
					</tr>
					<tr>
						<td style="padding:0 24px 20px;text-align:center;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:0.2em;text-transform:uppercase;color:#a39e94;">
							<?php echo esc_html(wp_strip_all_tags($email_heading)); ?>
						</td>
					</tr>
					<tr>
						<td style="padding:0 24px;">
							<div style="height:1px;background:#2a2a2a;line-height:1px;font-size:1px;">&nbsp;</div>
						</td>
					</tr>
					<tr>
						<td style="padding:24px;font-family:Georgia,'Times New Roman',serif;font-size:16px;line-height:1.6;color:#f5f2ea;">
