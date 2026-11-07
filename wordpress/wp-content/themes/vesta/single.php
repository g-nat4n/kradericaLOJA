<?php
declare(strict_types=1);
get_header();
?>
<main class="site-main">
	<?php while (have_posts()) : the_post(); ?>
		<div class="page-hero">
			<div class="container-main">
				<p class="eyebrow"><?php echo esc_html(get_the_date()); ?></p>
				<h1 class="display"><?php the_title(); ?></h1>
			</div>
		</div>
		<article class="container-main section content-prose">
			<?php if (has_post_thumbnail()) : ?>
				<div style="margin-bottom:2rem"><?php the_post_thumbnail('large'); ?></div>
			<?php endif; ?>
			<?php the_content(); ?>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
