<?php
/**
 * Plugin Name: Vesta Core
 * Description: Campos de peça única, looks, vitrine, best-sellers, Atelier, contato e seed da loja Vesta.
 * Version: 1.1.7
 * Author: Vesta Moda
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Text Domain: vesta-core
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

define('VESTA_CORE_VERSION', '1.1.7');
define('VESTA_CORE_FILE', __FILE__);
define('VESTA_CORE_DIR', plugin_dir_path(__FILE__));
define('VESTA_CORE_URL', plugin_dir_url(__FILE__));

require_once VESTA_CORE_DIR . 'includes/class-product-meta.php';
require_once VESTA_CORE_DIR . 'includes/class-taxonomy-look.php';
require_once VESTA_CORE_DIR . 'includes/class-unique-piece.php';
require_once VESTA_CORE_DIR . 'includes/class-admin-showcase.php';
require_once VESTA_CORE_DIR . 'includes/class-contact.php';
require_once VESTA_CORE_DIR . 'includes/class-seed.php';
require_once VESTA_CORE_DIR . 'includes/class-permalinks.php';
require_once VESTA_CORE_DIR . 'includes/helpers.php';
require_once VESTA_CORE_DIR . 'includes/atelier-helpers.php';
require_once VESTA_CORE_DIR . 'includes/class-atelier.php';
require_once VESTA_CORE_DIR . 'includes/class-hardening.php';

add_action('plugins_loaded', static function (): void {
	Vesta_Core_Taxonomy_Look::init();
	Vesta_Core_Product_Meta::init();
	Vesta_Core_Unique_Piece::init();
	Vesta_Core_Admin_Showcase::init();
	Vesta_Core_Contact::init();
	Vesta_Core_Seed::init();
	Vesta_Core_Permalinks::init();
	Vesta_Core_Atelier::init();
	Vesta_Core_Hardening::init();
});

register_activation_hook(__FILE__, static function (): void {
	Vesta_Core_Permalinks::rewrites();
	Vesta_Core_Atelier::rewrites();
	flush_rewrite_rules();
});
