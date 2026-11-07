<?php
/**
 * Página genérica — cart/checkout/conta usam layout full-width sem hero.
 *
 * @package Vesta
 */

declare(strict_types=1);

get_header();

$is_woo_flow  = function_exists('is_cart') && (is_cart() || is_checkout() || is_account_page());
$is_order_pay = function_exists('is_checkout') && is_checkout() && ! empty(get_query_var('order-pay'));
$is_check     = function_exists('is_checkout') && is_checkout() && ! is_order_received_page() && ! $is_order_pay;
$is_thanks    = function_exists('is_order_received_page') && is_order_received_page();
$is_account   = function_exists('is_account_page') && is_account_page();
?>
<main class="site-main<?php echo $is_woo_flow ? ' woo-main' : ''; ?><?php echo $is_check ? ' woo-main--checkout' : ''; ?><?php echo $is_order_pay ? ' woo-main--order-pay' : ''; ?><?php echo $is_thanks ? ' woo-main--thankyou' : ''; ?><?php echo $is_account ? ' woo-main--account' : ''; ?>">
	<?php if ($is_woo_flow) : ?>
		<?php
		while (have_posts()) :
			the_post();
			the_content();
		endwhile;
		?>
	<?php else : ?>
		<div class="page-hero">
			<div class="container-main">
				<p class="eyebrow">Kra de Rica</p>
				<h1 class="display"><?php the_title(); ?></h1>
			</div>
		</div>
		<div class="container-main section content-prose">
			<?php
			while (have_posts()) :
				the_post();
				the_content();
			endwhile;
			?>
		</div>
	<?php endif; ?>
</main>
<?php
get_footer();
