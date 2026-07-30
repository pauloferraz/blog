<?php
/**
 * Title: Home — Mais lidas
 * Slug: cdltheme/cdl-home-most-read
 * Categories: featured, query
 * Keywords: mais lidas, popular, posts
 * Description: Os cinco posts mais lidos, por contagem de visualizações.
 *
 * @package cdltheme
 */

$most_read_ids = function_exists( 'cdltheme_get_most_read_post_ids' )
	? cdltheme_get_most_read_post_ids( 5 )
	: array();

$posts = array();
if ( $most_read_ids ) {
	$posts = get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'post__in'            => $most_read_ids,
			'orderby'             => 'post__in',
			'posts_per_page'      => 5,
			'ignore_sticky_posts' => true,
		)
	);
}
?>
<!-- wp:group {"align":"full","className":"cdl-most-read","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"backgroundColor":"section-beige","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull cdl-most-read has-section-beige-background-color has-background" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:group {"align":"wide","className":"cdl-most-read__inner","layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide cdl-most-read__inner">
		<div class="cdl-most-read__header">
			<h2 class="cdl-most-read__heading"><?php esc_html_e( 'Mais lidas', 'cdltheme' ); ?></h2>
			<div class="cdl-most-read__intro">
				<p><?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'cdltheme' ); ?></p>
				<p><?php esc_html_e( 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 'cdltheme' ); ?></p>
			</div>
		</div>

		<?php if ( $posts ) : ?>
			<div class="cdl-most-read__list">
				<?php
				foreach ( $posts as $index => $post_obj ) :
					setup_postdata( $post_obj );
					$num    = str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT );
					$title  = get_the_title( $post_obj );
					$link   = get_permalink( $post_obj );
					$raw_ex = get_the_excerpt( $post_obj );
					if ( '' === trim( $raw_ex ) ) {
						$raw_ex = wp_trim_words( wp_strip_all_tags( $post_obj->post_content ), 28, '…' );
					}
					$excerpt = wp_trim_words( $raw_ex, 32, '…' );
					?>
					<article class="cdl-most-read__item">
						<div class="cdl-most-read__row">
							<span class="cdl-most-read__num" aria-hidden="true"><?php echo esc_html( $num ); ?></span>
							<h3 class="cdl-most-read__title">
								<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
							</h3>
						</div>
						<p class="cdl-most-read__excerpt"><?php echo esc_html( $excerpt ); ?></p>
					</article>
					<?php
				endforeach;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<p class="cdl-most-read__empty"><?php esc_html_e( 'Nenhum post publicado ainda.', 'cdltheme' ); ?></p>
		<?php endif; ?>
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
