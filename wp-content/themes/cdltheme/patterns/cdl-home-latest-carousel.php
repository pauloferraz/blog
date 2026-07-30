<?php
/**
 * Title: Home — Últimos posts (carrossel)
 * Slug: cdltheme/cdl-home-latest-carousel
 * Categories: featured, query
 * Description: Seis posts recentes em carrossel; três visíveis no desktop e um no mobile.
 *
 * @package cdltheme
 */

$posts_url = cdltheme_posts_archive_url();
?>
<!-- wp:group {"align":"full","className":"cdl-carousel-section cdl-latest-carousel","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"backgroundColor":"section-beige","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull cdl-carousel-section cdl-latest-carousel has-section-beige-background-color has-background" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:group {"align":"wide","className":"cdl-latest-carousel__header","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"var:preset|spacing|50","margin":{"bottom":"var:preset|spacing|60"}}}} -->
	<div class="wp-block-group alignwide cdl-latest-carousel__header" style="margin-bottom:var(--wp--preset--spacing--60)">
		<!-- wp:heading {"level":2,"fontFamily":"serif","fontSize":"x-large","style":{"typography":{"fontWeight":"700","lineHeight":"1.2"}},"textColor":"gray-text"} -->
		<h2 class="wp-block-heading has-gray-text-color has-text-color has-serif-font-family has-x-large-font-size" style="font-weight:700;line-height:1.2"><?php esc_html_e( 'Últimos posts', 'cdltheme' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:group {"className":"cdl-latest-carousel__controls","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"right","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"var:preset|spacing|40"}}} -->
		<div class="wp-block-group cdl-latest-carousel__controls">
			<!-- wp:paragraph {"className":"cdl-latest-carousel__desc","fontSize":"small","style":{"spacing":{"margin":{"top":"0","bottom":"0"}}},"textColor":"gray-text"} -->
			<p class="cdl-latest-carousel__desc has-gray-text-color has-text-color has-small-font-size" style="margin-top:0;margin-bottom:0"><?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'cdltheme' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:group {"className":"cdl-latest-carousel__actions","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|40"}}} -->
			<div class="wp-block-group cdl-latest-carousel__actions">
				<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"right"},"style":{"spacing":{"blockGap":"var:preset|spacing|30"}}} -->
				<div class="wp-block-buttons">
					<!-- wp:button {"className":"cdl-carousel__cta is-style-outline","style":{"border":{"width":"1px"}}} -->
					<div class="wp-block-button cdl-carousel__cta is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $posts_url ); ?>"><?php esc_html_e( 'Ver todos', 'cdltheme' ); ?></a></div>
					<!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->

				<!-- wp:buttons {"className":"cdl-carousel__arrows","layout":{"type":"flex","justifyContent":"right","flexWrap":"nowrap"},"style":{"spacing":{"blockGap":"8px"}}} -->
				<div class="wp-block-buttons cdl-carousel__arrows" role="group" aria-label="<?php echo esc_attr__( 'Navegação do carrossel', 'cdltheme' ); ?>">
					<!-- wp:button {"className":"cdl-carousel__arrow cdl-carousel__prev is-style-fill","style":{"border":{"radius":"999px"}}} -->
					<div class="wp-block-button cdl-carousel__arrow cdl-carousel__prev is-style-fill"><a class="wp-block-button__link wp-element-button" href="#" aria-label="<?php echo esc_attr__( 'Posts anteriores', 'cdltheme' ); ?>" aria-disabled="true">←</a></div>
					<!-- /wp:button -->
					<!-- wp:button {"className":"cdl-carousel__arrow cdl-carousel__next is-style-fill","style":{"border":{"radius":"999px"}}} -->
					<div class="wp-block-button cdl-carousel__arrow cdl-carousel__next is-style-fill"><a class="wp-block-button__link wp-element-button" href="#" aria-label="<?php echo esc_attr__( 'Próximos posts', 'cdltheme' ); ?>">→</a></div>
					<!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"align":"wide","className":"cdl-carousel","layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide cdl-carousel" data-cdl-carousel data-cdl-visible="3" data-cdl-total="6">
		<!-- wp:group {"className":"cdl-carousel__viewport","layout":{"type":"default"}} -->
		<div class="wp-block-group cdl-carousel__viewport">
			<!-- wp:query {"queryId":11,"query":{"perPage":6,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"align":"wide","className":"cdl-carousel__query","layout":{"type":"default"}} -->
			<div class="wp-block-query alignwide cdl-carousel__query">
				<!-- wp:post-template {"className":"cdl-carousel__track"} -->
					<!-- wp:group {"className":"cdl-carousel__slide","layout":{"type":"default"},"style":{"spacing":{"blockGap":"var:preset|spacing|40"}}} -->
					<div class="wp-block-group cdl-carousel__slide">
						<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3","style":{"border":{"radius":"2px"}}} /-->
						<!-- wp:post-terms {"term":"category","separator":" ","className":"cdl-carousel__categories","fontSize":"small","style":{"typography":{"fontWeight":"700"}}} /-->
						<!-- wp:post-title {"level":3,"isLink":true,"fontFamily":"serif","fontSize":"large","style":{"typography":{"fontWeight":"700","lineHeight":"1.25"}}} /-->
						<!-- wp:post-excerpt {"className":"cdl-carousel__excerpt","fontSize":"small","moreText":"","excerptLength":22} /-->
					</div>
					<!-- /wp:group -->
				<!-- /wp:post-template -->

				<!-- wp:query-no-results -->
				<!-- wp:paragraph -->
				<p><?php esc_html_e( 'Nenhum post publicado ainda.', 'cdltheme' ); ?></p>
				<!-- /wp:paragraph -->
				<!-- /wp:query-no-results -->
			</div>
			<!-- /wp:query -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
