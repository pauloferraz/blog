<?php
/**
 * Title: CDL Footer
 * Slug: cdltheme/cdl-footer
 * Categories: footer
 * Block Types: core/template-part/footer
 *
 * @package cdltheme
 */

$logo_url = esc_url( get_theme_file_uri( 'assets/logo-cdl.png' ) );
$home_url = esc_url( home_url( '/' ) );
$year     = gmdate( 'Y' );

$social_fb = cdltheme_social_href( 'cdltheme_social_facebook' );
$social_ig = cdltheme_social_href( 'cdltheme_social_instagram' );
$social_li = cdltheme_social_href( 'cdltheme_social_linkedin' );
$social_yt = cdltheme_social_href( 'cdltheme_social_youtube' );
?>
<!-- wp:group {"tagName":"footer","align":"full","className":"cdl-footer","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"backgroundColor":"navy","layout":{"type":"constrained"}} -->
<footer class="wp-block-group alignfull cdl-footer has-navy-background-color has-background" style="padding-right:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:group {"align":"wide","className":"cdl-footer__inner","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|50"}}} -->
	<div class="wp-block-group alignwide cdl-footer__inner" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)">
		<!-- wp:group {"className":"cdl-footer__brand","layout":{"type":"constrained"}} -->
		<div class="wp-block-group cdl-footer__brand">
			<!-- wp:image {"sizeSlug":"full","linkDestination":"custom","width":"180px","className":"cdl-footer__logo"} -->
			<figure class="wp-block-image size-full is-resized cdl-footer__logo"><a href="<?php echo esc_url( $home_url ); ?>" aria-label="<?php echo esc_attr__( 'Companhia das Letras', 'cdltheme' ); ?>"><img src="<?php echo esc_url( $logo_url ); ?>" alt="" style="width:180px" decoding="async" loading="lazy" /></a></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:group -->

		<!-- wp:paragraph {"align":"center","className":"cdl-footer__copyright","fontSize":"small"} -->
		<p class="has-text-align-center cdl-footer__copyright has-small-font-size"><?php printf( /* translators: %s: current year */ esc_html__( '© %s Companhia das Letras. Todos os direitos reservados.', 'cdltheme' ), esc_html( $year ) ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:social-links {"iconColor":"white","iconColorValue":"#ffffff","size":"has-normal-icon-size","className":"cdl-footer__social","layout":{"type":"flex","justifyContent":"right"},"style":{"spacing":{"blockGap":{"left":"1.25rem","top":"0"}}}} -->
		<ul class="wp-block-social-links has-normal-icon-size has-icon-color cdl-footer__social">
			<!-- wp:social-link {"url":"<?php echo ( '#' === $social_fb ) ? '#' : esc_url( $social_fb ); ?>","service":"facebook"} /-->
			<!-- wp:social-link {"url":"<?php echo ( '#' === $social_ig ) ? '#' : esc_url( $social_ig ); ?>","service":"instagram"} /-->
			<!-- wp:social-link {"url":"<?php echo ( '#' === $social_li ) ? '#' : esc_url( $social_li ); ?>","service":"linkedin"} /-->
			<!-- wp:social-link {"url":"<?php echo ( '#' === $social_yt ) ? '#' : esc_url( $social_yt ); ?>","service":"youtube"} /-->
		</ul>
		<!-- /wp:social-links -->
	</div>
	<!-- /wp:group -->
</footer>
<!-- /wp:group -->
