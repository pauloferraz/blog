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

$logo_url     = esc_url( get_theme_file_uri( 'assets/logo-cdl.png' ) );
$nav_icon_url = cdltheme_get_header_image_url( 'cdl-header__nav-icon', $logo_url );
$home_url     = esc_url( home_url( '/' ) );
$u_artigos  = cdltheme_category_link( 'artigos' );
$u_eventos  = cdltheme_category_link( 'eventos' );
$u_infantil = cdltheme_category_link( 'infantil' );
$u_listas   = cdltheme_category_link( 'listas' );
$u_noticias = cdltheme_category_link( 'noticias' );
$u_radio    = cdltheme_category_link( 'radio-companhia' );
$u_cdl      = esc_url( cdltheme_url_companhia() );
$u_edu      = esc_url( cdltheme_url_educacao() );

$nav_items = array(
	array(
		'label'       => _x( 'Artigos', 'category nav', 'cdltheme' ),
		'url'         => $u_artigos,
		'desktop'     => true,
		'mobile_only' => false,
	),
	array(
		'label'       => _x( 'Eventos', 'category nav', 'cdltheme' ),
		'url'         => $u_eventos,
		'desktop'     => true,
		'mobile_only' => false,
	),
	array(
		'label'       => _x( 'Infantil', 'category nav', 'cdltheme' ),
		'url'         => $u_infantil,
		'desktop'     => true,
		'mobile_only' => false,
	),
	array(
		'label'       => _x( 'Listas', 'category nav', 'cdltheme' ),
		'url'         => $u_listas,
		'desktop'     => true,
		'mobile_only' => false,
	),
	array(
		'label'       => _x( 'Notícias', 'category nav', 'cdltheme' ),
		'url'         => $u_noticias,
		'desktop'     => true,
		'mobile_only' => false,
	),
	array(
		'label'       => _x( 'Rádio Companhia', 'category nav', 'cdltheme' ),
		'url'         => $u_radio,
		'desktop'     => true,
		'mobile_only' => false,
	),
	array(
		'label'       => __( 'Companhia das Letras', 'cdltheme' ),
		'url'         => $u_cdl,
		'desktop'     => false,
		'mobile_only' => true,
	),
	array(
		'label'       => __( 'Educação', 'cdltheme' ),
		'url'         => $u_edu,
		'desktop'     => false,
		'mobile_only' => true,
	),
);
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|50","bottom":"0","left":"var:preset|spacing|50"}}},"backgroundColor":"beige","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull has-beige-background-color has-background" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:0;padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:group {"layout":{"type":"constrained"}} -->
	<div class="wp-block-group">
		<!-- wp:group {"align":"wide","className":"cdl-header__brand-row","style":{"spacing":{"padding":{"bottom":"var:preset|spacing|40"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
		<div class="wp-block-group alignwide cdl-header__brand-row" style="padding-bottom:var(--wp--preset--spacing--40)">
			<!-- wp:group {"className":"cdl-header__logo","layout":{"type":"constrained"}} -->
			<div class="wp-block-group cdl-header__logo">
				<!-- wp:image {"sizeSlug":"full","linkDestination":"custom","width":"200px"} -->
				<figure class="wp-block-image size-full is-resized"><a href="<?php echo esc_url( $home_url ); ?>"><img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr__( 'Companhia das Letras', 'cdltheme' ); ?>" style="width:200px" decoding="async" loading="eager" /></a></figure>
				<!-- /wp:image -->
			</div>
			<!-- /wp:group -->

			<!-- wp:search {"label":"","showLabel":false,"placeholder":"Procure por matérias, eventos e livros","buttonText":"","buttonUseIcon":true,"buttonPosition":"button-inside","className":"cdl-header__search cdl-header__search--desktop","backgroundColor":"beige-dark","textColor":"gray-text"} /-->
		</div>
		<!-- /wp:group -->

		<!-- wp:separator {"backgroundColor":"line","className":"cdl-header__rule"} /-->

		<!-- wp:group {"align":"wide","className":"cdl-header__nav-row","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|50"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
		<div class="wp-block-group alignwide cdl-header__nav-row" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50)">
			<!-- wp:group {"className":"cdl-header__toolbar","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left","verticalAlignment":"center"}} -->
			<div class="wp-block-group cdl-header__toolbar">
				<!-- wp:html -->
				<nav class="cdl-header__nav" aria-label="<?php echo esc_attr__( 'Menu principal', 'cdltheme' ); ?>">
					<button type="button" class="cdl-header__menu-toggle" aria-expanded="false" aria-controls="cdl-header-drawer">
						<span class="screen-reader-text"><?php echo esc_html__( 'Abrir menu', 'cdltheme' ); ?></span>
					</button>

					<ul class="cdl-header__nav-list cdl-header__nav-list--desktop">
						<?php foreach ( $nav_items as $item ) : ?>
							<?php if ( ! $item['desktop'] ) { continue; } ?>
							<li class="cdl-header__nav-item">
								<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>

					<div id="cdl-header-drawer" class="cdl-header__drawer" aria-hidden="true">
						<div class="cdl-header__drawer-backdrop" tabindex="-1" aria-hidden="true"></div>
						<aside class="cdl-header__drawer-panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__( 'Menu', 'cdltheme' ); ?>">
							<button type="button" class="cdl-header__drawer-close" aria-label="<?php echo esc_attr__( 'Fechar menu', 'cdltheme' ); ?>">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
									<path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
								</svg>
							</button>
							<ul class="cdl-header__nav-list cdl-header__nav-list--drawer">
								<?php foreach ( $nav_items as $item ) : ?>
									<li class="cdl-header__nav-item<?php echo $item['mobile_only'] ? ' cdl-header__nav-item--mobile-only' : ''; ?>">
										<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						</aside>
					</div>
				</nav>
				<!-- /wp:html -->

				<!-- wp:search {"label":"","showLabel":false,"placeholder":"Procure por matérias, eventos e livros","buttonText":"","buttonUseIcon":true,"buttonPosition":"button-inside","className":"cdl-header__search cdl-header__search--mobile","backgroundColor":"beige-dark","textColor":"gray-text"} /-->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"cdl-header__externals","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"right","verticalAlignment":"center"}} -->
			<div class="wp-block-group cdl-header__externals">
				<!-- wp:group {"className":"cdl-header__external cdl-header__external--cdl","layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
				<div class="wp-block-group cdl-header__external cdl-header__external--cdl">
					<!-- wp:image {"sizeSlug":"full","linkDestination":"none","width":"22px","className":"cdl-header__nav-icon"} -->
					<figure class="wp-block-image size-full is-resized cdl-header__nav-icon"><img src="<?php echo esc_url( $nav_icon_url ); ?>" alt="" decoding="async" /></figure>
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
