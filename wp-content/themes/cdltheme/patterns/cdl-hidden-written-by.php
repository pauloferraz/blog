<?php
/**
 * Title: Meta do post
 * Slug: cdltheme/cdl-hidden-written-by
 * Inserter: no
 *
 * @package cdltheme
 */
?>
<!-- wp:group {"style":{"spacing":{"blockGap":"0.2em","margin":{"bottom":"var:preset|spacing|60"}}},"textColor":"gray-text","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group has-gray-text-color has-text-color has-link-color" style="margin-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:paragraph -->
	<p><?php esc_html_e( 'Por ', 'cdltheme' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:post-author-name {"isLink":true} /-->
	<!-- wp:html -->
	<span class="cdl-meta__icon" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
	<!-- /wp:html -->
	<!-- wp:post-date {"format":"d/m/Y"} /-->
</div>
<!-- /wp:group -->
