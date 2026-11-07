<?php
declare(strict_types=1);
get_header();

$is_catalog = function_exists('is_shop') && (is_shop() || is_product_taxonomy());
$is_product = function_exists('is_product') && is_product();
$is_auth    = function_exists('is_account_page') && is_account_page() && ! is_user_logged_in();
$is_order_pay = function_exists('is_checkout') && is_checkout() && ! empty(get_query_var('order-pay'));
$is_check     = function_exists('is_checkout') && is_checkout() && ! is_order_received_page() && ! $is_order_pay;
$is_thanks    = function_exists('is_order_received_page') && is_order_received_page();
$is_account   = function_exists('is_account_page') && is_account_page() && is_user_logged_in();
$wrap_class   = 'site-main woo-main';
if ($is_catalog) {
	$wrap_class .= ' woo-main--catalog';
}
if ($is_product) {
	$wrap_class .= ' woo-main--pdp';
}
if ($is_auth) {
	$wrap_class .= ' woo-main--auth';
}
if ($is_check) {
	$wrap_class .= ' woo-main--checkout';
}
if ($is_order_pay) {
	$wrap_class .= ' woo-main--order-pay';
}
if ($is_thanks) {
	$wrap_class .= ' woo-main--thankyou';
}
if ($is_account) {
	$wrap_class .= ' woo-main--account';
}
?>
<main class="<?php echo esc_attr($wrap_class); ?>">
	<?php if ($is_catalog) : ?>
		<?php
		/**
		 * Woo 9.8+ não chama mais archive-product.php em woocommerce_content().
		 * Carregamos o template do tema diretamente.
		 */
		wc_get_template('archive-product.php');
		?>
	<?php elseif ($is_product || $is_auth || $is_check || $is_order_pay || $is_thanks || $is_account) : ?>
		<?php woocommerce_content(); ?>
	<?php else : ?>
		<div class="container-main">
			<?php woocommerce_content(); ?>
		</div>
	<?php endif; ?>
</main>
<?php
get_footer();
