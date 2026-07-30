<?php
/**
 * Title: CDL Header
 * Slug: cdltheme/cdl-header
 * Categories: header
 * Block Types: core/template-part/header
 * Description: Logo, busca e menus de categorias e links externos.
 *
 * @package cdltheme
 */

$logo_url   = esc_url( get_theme_file_uri( 'assets/logo-cdl.png' ) );
$home_url   = esc_url( home_url( '/' ) );
$u_listas   = cdltheme_category_link( 'listas' );
$u_entrev   = cdltheme_category_link( 'entrevistas' );
$u_infantil = cdltheme_category_link( 'infantil' );
$u_eventos  = cdltheme_category_link( 'eventos' );
$u_cdl      = esc_url( cdltheme_url_companhia() );
$u_edu      = esc_url( cdltheme_url_educacao() );
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|50","bottom":"0","left":"var:preset|spacing|50"}}},"backgroundColor":"beige","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull has-beige-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:0;padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:group {"layout":{"type":"constrained"}} -->
	<div class="wp-block-group">
		<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
		<div class="wp-block-group alignwide" style="padding-bottom:var(--wp--preset--spacing--40)">
			<!-- wp:group {"className":"cdl-header__logo","layout":{"type":"constrained"}} -->
			<div class="wp-block-group cdl-header__logo">
				<!-- wp:image {"sizeSlug":"full","linkDestination":"custom","width":"200px"} -->
				<figure class="wp-block-image size-full is-resized"><a href="<?php echo esc_url( $home_url ); ?>"><img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr__( 'Companhia das Letras', 'cdltheme' ); ?>" style="width:200px" decoding="async" loading="eager" /></a></figure>
				<!-- /wp:image -->
			</div>
			<!-- /wp:group -->

			<!-- wp:search {"label":"","showLabel":false,"placeholder":"Procure por matérias, eventos e livros","buttonText":"","buttonUseIcon":true,"buttonPosition":"button-inside","className":"cdl-header__search","backgroundColor":"beige-dark","textColor":"gray-text"} /-->
		</div>
		<!-- /wp:group -->

		<!-- wp:separator {"backgroundColor":"line","className":"cdl-header__rule"} /-->

		<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|50"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
		<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50)">
			<!-- wp:navigation {"overlayMenu":"never","className":"cdl-header__nav-cats","layout":{"type":"flex","justifyContent":"left","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"fontSize":"small"} -->
				<!-- wp:navigation-link {"label":"<?php echo esc_attr( _x( 'Listas', 'category nav', 'cdltheme' ) ); ?>","url":"<?php echo esc_url( $u_listas ); ?>","kind":"custom"} /-->
				<!-- wp:navigation-link {"label":"<?php echo esc_attr( _x( 'Entrevistas', 'category nav', 'cdltheme' ) ); ?>","url":"<?php echo esc_url( $u_entrev ); ?>","kind":"custom"} /-->
				<!-- wp:navigation-link {"label":"<?php echo esc_attr( _x( 'Infantil', 'category nav', 'cdltheme' ) ); ?>","url":"<?php echo esc_url( $u_infantil ); ?>","kind":"custom"} /-->
				<!-- wp:navigation-link {"label":"<?php echo esc_attr( _x( 'Eventos', 'category nav', 'cdltheme' ) ); ?>","url":"<?php echo esc_url( $u_eventos ); ?>","kind":"custom"} /-->
			<!-- /wp:navigation -->

			<!-- wp:group {"className":"cdl-header__externals","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"right","verticalAlignment":"center"}} -->
			<div class="wp-block-group cdl-header__externals">
				<!-- wp:group {"className":"cdl-header__external cdl-header__external--cdl","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
				<div class="wp-block-group cdl-header__external cdl-header__external--cdl">
					<!-- wp:image {"sizeSlug":"full","linkDestination":"none","width":"22px","className":"cdl-header__nav-icon"} -->
					<figure class="wp-block-image size-full is-resized cdl-header__nav-icon"><img src="<?php echo esc_url( $logo_url ); ?>" alt="" style="width:22px" decoding="async" /></figure>
					<!-- /wp:image -->
					<!-- wp:paragraph -->
					<p><a href="<?php echo esc_url( $u_cdl ); ?>"><?php echo esc_html__( 'Companhia das Letras', 'cdltheme' ); ?></a></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
				<!-- wp:paragraph {"className":"cdl-header__external"} -->
				<p class="cdl-header__external"><a href="<?php echo esc_url( $u_edu ); ?>"><?php echo esc_html__( 'Educação', 'cdltheme' ); ?></a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
