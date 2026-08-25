<?php
/**
 * Title: Mais posts
 * Slug: cdltheme/cdl-more-posts
 * Categories: query
 * Inserter: no
 *
 * @package cdltheme
 */

$posts_url = cdltheme_posts_archive_url();

$related_current_id  = is_singular( 'post' ) ? get_the_ID() : 0;
$related_category_ids = $related_current_id ? wp_get_post_categories( $related_current_id ) : array();
$related_query        = array(
	'perPage'  => 6,
	'pages'    => 0,
	'offset'   => 0,
	'postType' => 'post',
	'order'    => 'desc',
	'orderBy'  => 'date',
	'author'   => '',
	'search'   => '',
	'exclude'  => $related_current_id ? array( $related_current_id ) : array(),
	'sticky'   => '',
	'inherit'  => false,
	'taxQuery' => $related_category_ids ? array( 'category' => array_values( $related_category_ids ) ) : null,
	'parents'  => array(),
);
$related_query_json = wp_json_encode( $related_query );
?>
<?php if ( is_singular( 'post' ) ) : ?>
<!-- wp:group {"align":"full","className":"cdl-carousel-section cdl-more-posts","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"backgroundColor":"section-beige","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull cdl-carousel-section cdl-more-posts has-section-beige-background-color has-background" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:group {"align":"wide","className":"cdl-latest-carousel__header","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"top"},"style":{"spacing":{"blockGap":"var:preset|spacing|50","margin":{"bottom":"var:preset|spacing|60"}}}} -->
	<div class="wp-block-group alignwide cdl-latest-carousel__header" style="margin-bottom:var(--wp--preset--spacing--60)">
		<!-- wp:heading {"level":2,"fontFamily":"serif","fontSize":"x-large","style":{"typography":{"fontWeight":"700","lineHeight":"1.2"}},"textColor":"gray-text"} -->
		<h2 class="wp-block-heading has-gray-text-color has-text-color has-serif-font-family has-x-large-font-size" style="font-weight:700;line-height:1.2"><?php esc_html_e( 'Veja Também', 'cdltheme' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:group {"className":"cdl-latest-carousel__controls","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"right","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|40"}}} -->
		<div class="wp-block-group cdl-latest-carousel__controls">
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
			<!-- wp:query {"queryId":20,"query":<?php echo $related_query_json; ?>,"align":"wide","className":"cdl-carousel__query","layout":{"type":"default"}} -->
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
<?php else : ?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:heading {"align":"wide","className":"cdl-more-posts__heading","fontFamily":"serif"} -->
	<h2 class="wp-block-heading alignwide cdl-more-posts__heading has-serif-font-family"><?php esc_html_e( 'Veja Também', 'cdltheme' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:query {"query":{"perPage":4,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":null,"parents":[]},"align":"wide","layout":{"type":"default"}} -->
	<div class="wp-block-query alignwide">
		<!-- wp:post-template {"align":"full","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
			<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}},"border":{"bottom":{"color":"var:preset|color|line","width":"1px"}}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center","justifyContent":"space-between"}} -->
			<div class="wp-block-group alignfull" style="border-bottom-color:var(--wp--preset--color--line);border-bottom-width:1px;padding-top:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30)">
				<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"large"} /-->
				<!-- wp:post-date {"textAlign":"right","isLink":true} /-->
			</div>
			<!-- /wp:group -->
		<!-- /wp:post-template -->
	</div>
	<!-- /wp:query -->
</div>
<!-- /wp:group -->
<?php endif; ?>
