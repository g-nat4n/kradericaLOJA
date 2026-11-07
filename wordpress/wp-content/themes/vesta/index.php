<?php
declare(strict_types=1);
get_header();
?>
<main class="site-main">
	<div class="page-hero">
		<div class="container-main">
			<p class="eyebrow">Kra de Rica</p>
			<h1 class="display"><?php esc_html_e('Conteúdo', 'vesta'); ?></h1>
		</div>
	</div>
	<div class="container-main section">
		<?php if (have_posts()) : ?>
			<div class="journal-grid">
				<?php while (have_posts()) : the_post(); ?>
					<a class="journal-card" href="<?php the_permalink(); ?>" data-reveal>
						<article>
							<?php if (has_post_thumbnail()) : ?>
								<?php the_post_thumbnail('large'); ?>
							<?php endif; ?>
							<p class="eyebrow"><?php echo esc_html(get_the_date()); ?></p>
							<h3><?php the_title(); ?></h3>
							<p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 22)); ?></p>
						</article>
					</a>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e('Nenhum conteúdo encontrado.', 'vesta'); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
