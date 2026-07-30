<?php
/**
 * Title: Home — Hero
 * Slug: cdltheme/cdl-home-hero
 * Categories: featured, banner
 * Description: Título em serif e subtítulo em sans, alinhados à esquerda.
 *
 * @package cdltheme
 */
?>
<!-- wp:group {"align":"full","className":"cdl-home-hero","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"backgroundColor":"beige","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull cdl-home-hero has-beige-background-color has-background" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:group {"align":"wide","className":"cdl-home-hero__content","layout":{"type":"constrained"}} -->
	<div class="wp-block-group alignwide cdl-home-hero__content">
		<!-- wp:heading {"level":1,"fontFamily":"serif","fontSize":"display","style":{"typography":{"fontWeight":"700","lineHeight":"1.15"}},"textColor":"gray-text"} -->
		<h1 class="wp-block-heading has-gray-text-color has-text-color has-serif-font-family has-display-font-size" style="font-weight:700;line-height:1.15"><?php esc_html_e( 'Lorem ipsum dolor sit amet consectetur', 'cdltheme' ); ?></h1>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"cdl-home-hero__subtitle","fontSize":"medium","style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}},"textColor":"gray-text"} -->
		<p class="cdl-home-hero__subtitle has-gray-text-color has-text-color has-medium-font-size" style="margin-top:var(--wp--preset--spacing--50)"><?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.', 'cdltheme' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
