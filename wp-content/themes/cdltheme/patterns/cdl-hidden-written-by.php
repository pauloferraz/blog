<?php
/**
 * Title: Meta do post
 * Slug: cdltheme/cdl-hidden-written-by
 * Inserter: no
 *
 * @package cdltheme
 */
?>
<!-- wp:group {"style":{"spacing":{"blockGap":"0.2em","margin":{"bottom":"var:preset|spacing|60"}}},"textColor":"gray-text","fontSize":"small","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group has-gray-text-color has-text-color has-link-color has-small-font-size" style="margin-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:paragraph -->
	<p><?php esc_html_e( 'Por ', 'cdltheme' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:post-author-name {"isLink":true} /-->
	<!-- wp:paragraph -->
	<p><?php esc_html_e( 'em', 'cdltheme' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:post-terms {"term":"category"} /-->
</div>
<!-- /wp:group -->
