<?php
/**
 * Tema Vesta — bootstrap.
 *
 * @package Vesta
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

define('VESTA_THEME_VERSION', '1.7.5');
define('VESTA_THEME_DIR', get_template_directory());
define('VESTA_THEME_URI', get_template_directory_uri());

require_once VESTA_THEME_DIR . '/inc/setup.php';
require_once VESTA_THEME_DIR . '/inc/assets.php';
require_once VESTA_THEME_DIR . '/inc/branding.php';
require_once VESTA_THEME_DIR . '/inc/woocommerce.php';
require_once VESTA_THEME_DIR . '/inc/template-tags.php';
require_once VESTA_THEME_DIR . '/inc/cart.php';
require_once VESTA_THEME_DIR . '/inc/checkout.php';
require_once VESTA_THEME_DIR . '/inc/mail.php';
require_once VESTA_THEME_DIR . '/inc/payments.php';
require_once VESTA_THEME_DIR . '/inc/catalog.php';
require_once VESTA_THEME_DIR . '/inc/auth-pages.php';
