<?php
/**
 * Title: Lista de posts
 * Slug: cdltheme/cdl-template-query-loop
 * Categories: query
 * Block Types: core/query
 * Inserter: no
 *
 * @package cdltheme
 */
?>
<!-- wp:query {"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true,"taxQuery":null,"parents":[]},"align":"wide","className":"cdl-archive-query","layout":{"type":"default"}} -->
<div class="wp-block-query alignwide cdl-archive-query">
	<!-- wp:post-template {"className":"cdl-archive-list","layout":{"type":"default"}} -->
		<!-- wp:group {"className":"cdl-archive-item","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}},"border":{"bottom":{"color":"var:preset|color|line","width":"1px"}}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"top"}} -->
		<div class="wp-block-group cdl-archive-item" style="border-bottom-color:var(--wp--preset--color--line);border-bottom-width:1px;padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
			<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3","width":"280px","className":"cdl-archive-item__media"} /-->
			<!-- wp:group {"className":"cdl-archive-item__body","layout":{"type":"constrained","justifyContent":"left"}} -->
			<div class="wp-block-group cdl-archive-item__body">
				<!-- wp:post-title {"isLink":true,"fontSize":"x-large"} /-->
				<!-- wp:post-excerpt {"fontSize":"medium"} /-->
				<!-- wp:post-date {"isLink":true,"fontSize":"small","className":"cdl-archive-item__date"} /-->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	<!-- /wp:post-template -->
	<!-- wp:query-no-results -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'Nenhum resultado encontrado.', 'cdltheme' ); ?></p>
		<!-- /wp:paragraph -->
	<!-- /wp:query-no-results -->
	<!-- wp:query-pagination {"paginationArrow":"arrow","layout":{"type":"flex","justifyContent":"space-between"}} -->
		<!-- wp:query-pagination-previous /-->
		<!-- wp:query-pagination-numbers /-->
		<!-- wp:query-pagination-next /-->
	<!-- /wp:query-pagination -->
</div>
<!-- /wp:query -->
