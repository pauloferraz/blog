<?php
/**
 * Title: Post Meta
 * Slug: ciadasletras/post-meta
 * Categories: query
 * Keywords: post meta
 * Block Types: core/template-part/post-meta
 * Description: Autor, data e categoria do post, logo abaixo do título.
 */
?>
<!-- wp:group {"className":"cdl-post-meta","fontSize":"small","style":{"spacing":{"blockGap":"0.35rem","margin":{"top":"0","bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group cdl-post-meta has-small-font-size" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--40)">
	<!-- wp:group {"style":{"spacing":{"blockGap":"0.35ch"}},"layout":{"type":"flex"}} -->
	<div class="wp-block-group">
		<!-- wp:paragraph -->
		<p><?php echo esc_html_x( 'Por', 'Preposition before the author name', 'ciadasletras' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:post-author {"showAvatar":false} /-->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"style":{"spacing":{"blockGap":"0.35ch"}},"layout":{"type":"flex"}} -->
	<div class="wp-block-group">
		<!-- wp:post-date /-->

		<!-- wp:paragraph -->
		<p><?php echo esc_html_x( 'em', 'Preposition before the category name', 'ciadasletras' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:post-terms {"term":"category"} /-->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
