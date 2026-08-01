<?php
/**
 * CDL Theme — funções e suporte.
 *
 * @package cdltheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URLs externas do cabeçalho (filtros para ajuste sem editar o pattern).
 */
function cdltheme_url_companhia(): string {
	return (string) apply_filters( 'cdltheme_url_companhia', 'https://www.companhiadasletras.com.br/' );
}

function cdltheme_url_educacao(): string {
	return (string) apply_filters( 'cdltheme_url_educacao', 'https://www.companhiadasletras.com.br/educacao/' );
}

/**
 * URL de arquivo de categoria.
 *
 * @param string $slug Slug da categoria.
 */
function cdltheme_category_link( string $slug ): string {
	$term = get_term_by( 'slug', $slug, 'category' );
	if ( $term && ! is_wp_error( $term ) ) {
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			return esc_url( $link );
		}
	}

	return esc_url( home_url( user_trailingslashit( 'category/' . $slug ) ) );
}

/**
 * URL para ícone social do rodapé (aceita '#' quando ainda não configurado).
 *
 * @param string $filter_name Nome do filtro, ex.: cdltheme_social_facebook.
 */
function cdltheme_social_href( string $filter_name ): string {
	$url = (string) apply_filters( $filter_name, '' );
	$url = trim( $url );
	if ( '' === $url || '#' === $url ) {
		return '#';
	}
	$out = esc_url( $url );

	return '' !== $out ? $out : '#';
}

/**
 * Slug do tipo de conteúdo "Seleções" (equivalente a companySelections; o core só aceita slugs em minúsculas).
 * REST: /wp/v2/company_selections
 */
function cdltheme_post_type_company_selections(): string {
	return 'company_selections';
}

/**
 * Slug do tipo de conteúdo "Rádio" (equivalente a companyRadio).
 * REST: /wp/v2/company_radio
 */
function cdltheme_post_type_company_radio(): string {
	return 'company_radio';
}

/**
 * Meta: URL Spotify (apenas company_radio).
 */
function cdltheme_radio_meta_spotify(): string {
	return 'cdl_spotify_url';
}

/**
 * Meta: URL YouTube (apenas company_radio).
 */
function cdltheme_radio_meta_youtube(): string {
	return 'cdl_youtube_url';
}

/**
 * URL do arquivo de um tipo de conteúdo publicável.
 */
function cdltheme_post_type_archive_url( string $post_type ): string {
	$link = get_post_type_archive_link( $post_type );
	return is_string( $link ) && $link !== '' ? esc_url( $link ) : esc_url( home_url( '/' ) );
}

/**
 * URL da listagem de posts (página de artigos ou arquivo).
 */
function cdltheme_posts_archive_url(): string {
	$posts_page = (int) get_option( 'page_for_posts' );
	if ( $posts_page > 0 ) {
		return esc_url( get_permalink( $posts_page ) );
	}

	if ( 'posts' === get_option( 'show_on_front' ) ) {
		return esc_url( home_url( '/' ) );
	}

	$archive = get_post_type_archive_link( 'post' );
	if ( is_string( $archive ) && $archive !== '' ) {
		return esc_url( $archive );
	}

	return esc_url( home_url( '/' ) );
}

/**
 * Meta key usada para ordenar posts mais lidos.
 */
function cdltheme_post_views_meta_key(): string {
	return 'cdltheme_post_views';
}

/**
 * Incrementa visualizações do post (apenas singular de post, sem preview).
 */
function cdltheme_track_post_views(): void {
	if ( ! is_singular( 'post' ) || is_preview() ) {
		return;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	$post_id = (int) get_queried_object_id();
	if ( $post_id <= 0 ) {
		return;
	}

	$key   = cdltheme_post_views_meta_key();
	$count = (int) get_post_meta( $post_id, $key, true );
	update_post_meta( $post_id, $key, $count + 1 );
}

add_action( 'template_redirect', 'cdltheme_track_post_views' );

/**
 * Verifica se um post pode aparecer em "Mais lidas".
 *
 * @param int $post_id ID do post.
 */
function cdltheme_is_valid_most_read_post( int $post_id ): bool {
	if ( $post_id <= 0 ) {
		return false;
	}

	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type || 'publish' !== $post->post_status ) {
		return false;
	}

	return '' !== trim( get_the_title( $post ) );
}

/**
 * Remove contagem de visualizações quando o post é excluído permanentemente.
 *
 * @param int $post_id ID do post.
 */
function cdltheme_cleanup_post_views_meta( int $post_id ): void {
	if ( 'post' !== get_post_type( $post_id ) ) {
		return;
	}

	delete_post_meta( $post_id, cdltheme_post_views_meta_key() );
}
add_action( 'before_delete_post', 'cdltheme_cleanup_post_views_meta' );

/**
 * IDs dos N posts com mais visualizações; completa com posts recentes se faltar.
 *
 * @param int $count Quantidade desejada.
 * @return int[]
 */
function cdltheme_get_most_read_post_ids( int $count = 5 ): array {
	$count = max( 1, $count );
	$key   = cdltheme_post_views_meta_key();
	$ids   = array();

	$q = new WP_Query(
		array(
			'posts_per_page'      => $count * 3,
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'fields'              => 'ids',
			'ignore_sticky_posts' => true,
			'meta_key'            => $key,
			'orderby'             => 'meta_value_num',
			'order'               => 'DESC',
			'meta_compare'        => 'EXISTS',
		)
	);

	foreach ( array_map( 'intval', $q->posts ) as $post_id ) {
		if ( ! cdltheme_is_valid_most_read_post( $post_id ) ) {
			continue;
		}

		$ids[] = $post_id;
		if ( count( $ids ) >= $count ) {
			break;
		}
	}

	if ( count( $ids ) < $count ) {
		$fill = new WP_Query(
			array(
				'posts_per_page'      => $count * 2,
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'post__not_in'        => $ids,
				'fields'              => 'ids',
				'ignore_sticky_posts' => true,
				'orderby'             => 'date',
				'order'               => 'DESC',
			)
		);

		foreach ( array_map( 'intval', $fill->posts ) as $post_id ) {
			if ( ! cdltheme_is_valid_most_read_post( $post_id ) || in_array( $post_id, $ids, true ) ) {
				continue;
			}

			$ids[] = $post_id;
			if ( count( $ids ) >= $count ) {
				break;
			}
		}
	}

	return array_slice( $ids, 0, $count );
}

/**
 * Posts publicados para a seção "Mais lidas".
 *
 * @param int $count Quantidade desejada.
 * @return WP_Post[]
 */
function cdltheme_get_most_read_posts( int $count = 5 ): array {
	$ids = cdltheme_get_most_read_post_ids( $count );
	if ( ! $ids ) {
		return array();
	}

	$posts = get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'post__in'            => $ids,
			'orderby'             => 'post__in',
			'posts_per_page'      => $count,
			'ignore_sticky_posts' => true,
		)
	);

	return array_values(
		array_filter(
			$posts,
			static function ( $post ): bool {
				return $post instanceof WP_Post && cdltheme_is_valid_most_read_post( (int) $post->ID );
			}
		)
	);
}

/**
 * HTML da seção "Mais lidas" da home.
 */
function cdltheme_render_most_read_section(): string {
	$posts = cdltheme_get_most_read_posts( 5 );

	ob_start();
	?>
<div class="wp-block-group alignfull cdl-most-read has-section-beige-background-color has-background" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--50)">
	<div class="wp-block-group alignwide cdl-most-read__inner">
		<div class="cdl-most-read__header">
			<h2 class="cdl-most-read__heading"><?php esc_html_e( 'Mais lidas', 'cdltheme' ); ?></h2>
			<div class="cdl-most-read__intro">
				<p><?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'cdltheme' ); ?></p>
				<p><?php esc_html_e( 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 'cdltheme' ); ?></p>
			</div>
		</div>

		<?php if ( $posts ) : ?>
			<div class="cdl-most-read__list">
				<?php
				foreach ( $posts as $index => $post_obj ) :
					$num     = str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT );
					$title   = get_the_title( $post_obj );
					$link    = get_permalink( $post_obj );
					$raw_ex  = get_the_excerpt( $post_obj );
					if ( '' === trim( $raw_ex ) ) {
						$raw_ex = wp_trim_words( wp_strip_all_tags( $post_obj->post_content ), 28, '…' );
					}
					$excerpt = wp_trim_words( $raw_ex, 32, '…' );
					?>
					<article class="cdl-most-read__item">
						<div class="cdl-most-read__row">
							<span class="cdl-most-read__num" aria-hidden="true"><?php echo esc_html( $num ); ?></span>
							<h3 class="cdl-most-read__title">
								<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
							</h3>
						</div>
						<p class="cdl-most-read__excerpt"><?php echo esc_html( $excerpt ); ?></p>
					</article>
					<?php
				endforeach;
				?>
			</div>
		<?php else : ?>
			<p class="cdl-most-read__empty"><?php esc_html_e( 'Nenhum post publicado ainda.', 'cdltheme' ); ?></p>
		<?php endif; ?>
	</div>
</div>
	<?php
	$html = ob_get_clean();

	return is_string( $html ) ? $html : '';
}

/**
 * Renderiza "Mais lidas" sempre com dados atuais (ignora snapshot salvo no editor).
 *
 * @param string|null $pre_render   HTML pré-renderizado ou null.
 * @param array       $parsed_block Bloco parseado.
 */
function cdltheme_pre_render_most_read_group( $pre_render, array $parsed_block ) {
	if ( null !== $pre_render ) {
		return $pre_render;
	}

	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $pre_render;
	}

	if ( ( $parsed_block['blockName'] ?? '' ) !== 'core/group' ) {
		return $pre_render;
	}

	$class = (string) ( $parsed_block['attrs']['className'] ?? '' );
	if ( ! preg_match( '/\bcdl-most-read\b/', $class ) ) {
		return $pre_render;
	}

	return cdltheme_render_most_read_section();
}
add_filter( 'pre_render_block', 'cdltheme_pre_render_most_read_group', 8, 2 );

/**
 * Garante o bloco "Mais lidas" nos templates de home (tema ou customizado no editor).
 *
 * @param string $content Conteúdo do template.
 */
function cdltheme_ensure_most_read_in_home_template_content( string $content ): string {
	if ( str_contains( $content, 'cdl-home-most-read' ) || str_contains( $content, 'cdl-most-read' ) ) {
		return $content;
	}

	$pattern = "\n\t<!-- wp:pattern {\"slug\":\"cdltheme/cdl-home-most-read\"} /-->";
	$updated = preg_replace( '/(\s*<\/main>\s*<!-- \/wp:group -->)/', $pattern . '$1', $content, 1, $count );

	if ( $count > 0 ) {
		return is_string( $updated ) ? $updated : $content;
	}

	$updated = preg_replace( '/(\s*<!-- wp:template-part \{"slug":"footer")/', $pattern . '$1', $content, 1, $count );

	return ( $count > 0 && is_string( $updated ) ) ? $updated : $content . $pattern;
}

/**
 * Injeta "Mais lidas" ao carregar templates de home sem a seção.
 *
 * @param WP_Block_Template|null $block_template Template encontrado.
 * @param string                 $id             ID do template.
 * @param string                 $template_type  Tipo.
 */
function cdltheme_filter_home_template_most_read( $block_template, string $id, string $template_type ) {
	if ( 'wp_template' !== $template_type || ! $block_template instanceof WP_Block_Template ) {
		return $block_template;
	}

	if ( ! in_array( $block_template->slug, array( 'front-page', 'home' ), true ) ) {
		return $block_template;
	}

	if ( empty( $block_template->content ) ) {
		return $block_template;
	}

	$block_template->content = cdltheme_ensure_most_read_in_home_template_content( $block_template->content );

	return $block_template;
}
add_filter( 'get_block_template', 'cdltheme_filter_home_template_most_read', 20, 3 );

/**
 * Fallback no front: acrescenta "Mais lidas" ao <main> da home se ainda faltar.
 *
 * @param string $content HTML renderizado do bloco.
 * @param array  $block   Definição do bloco.
 */
function cdltheme_append_most_read_to_home_main( string $content, array $block ): string {
	if ( ( $block['blockName'] ?? '' ) !== 'core/group' ) {
		return $content;
	}

	if ( ( $block['attrs']['tagName'] ?? '' ) !== 'main' ) {
		return $content;
	}

	if ( ! is_front_page() && ! ( is_home() && ! is_front_page() ) ) {
		return $content;
	}

	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $content;
	}

	if ( str_contains( $content, 'cdl-most-read' ) ) {
		return $content;
	}

	return $content . cdltheme_render_most_read_section();
}
add_filter( 'render_block', 'cdltheme_append_most_read_to_home_main', 10, 2 );

/**
 * Regista tipos de conteúdo personalizados dos carrosséis (Seleções e Rádio).
 */
function cdltheme_register_company_carousel_post_types(): void {
	register_post_type(
		cdltheme_post_type_company_selections(),
		array(
			'labels'              => array(
				'name'               => _x( 'Seleções', 'post type general name', 'cdltheme' ),
				'singular_name'      => _x( 'Seleção', 'post type singular name', 'cdltheme' ),
				'add_new'            => _x( 'Adicionar nova', 'company_selections', 'cdltheme' ),
				'add_new_item'       => __( 'Adicionar nova seleção', 'cdltheme' ),
				'edit_item'          => __( 'Editar seleção', 'cdltheme' ),
				'new_item'           => __( 'Nova seleção', 'cdltheme' ),
				'view_item'          => __( 'Ver seleção', 'cdltheme' ),
				'search_items'       => __( 'Pesquisar seleções', 'cdltheme' ),
				'not_found'          => __( 'Nenhuma seleção encontrada.', 'cdltheme' ),
				'not_found_in_trash' => __( 'Nenhuma seleção na lixeira.', 'cdltheme' ),
				'menu_name'          => __( 'Seleções', 'cdltheme' ),
			),
			'public'                => true,
			'publicly_queryable'  => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'show_in_nav_menus'     => false,
			'show_in_admin_bar'     => true,
			'show_in_rest'          => true,
			'rest_base'             => 'company_selections',
			'menu_icon'             => 'dashicons-images-alt2',
			'menu_position'         => 21,
			'has_archive'           => true,
			'rewrite'               => array( 'slug' => 'selecoes-companhia' ),
			'supports'              => array( 'title', 'thumbnail' ),
			'capability_type'       => 'post',
		)
	);

	register_post_type(
		cdltheme_post_type_company_radio(),
		array(
			'labels'              => array(
				'name'               => _x( 'Rádio Companhia', 'post type general name', 'cdltheme' ),
				'singular_name'      => _x( 'Item de rádio', 'post type singular name', 'cdltheme' ),
				'add_new'            => _x( 'Adicionar novo', 'company_radio', 'cdltheme' ),
				'add_new_item'       => __( 'Adicionar item de rádio', 'cdltheme' ),
				'edit_item'          => __( 'Editar item de rádio', 'cdltheme' ),
				'new_item'           => __( 'Novo item de rádio', 'cdltheme' ),
				'view_item'          => __( 'Ver item', 'cdltheme' ),
				'search_items'       => __( 'Pesquisar rádio', 'cdltheme' ),
				'not_found'          => __( 'Nenhum item encontrado.', 'cdltheme' ),
				'not_found_in_trash' => __( 'Nenhum item na lixeira.', 'cdltheme' ),
				'menu_name'          => __( 'Rádio Companhia', 'cdltheme' ),
			),
			'public'                => true,
			'publicly_queryable'    => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'show_in_nav_menus'     => false,
			'show_in_admin_bar'     => true,
			'show_in_rest'          => true,
			'rest_base'             => 'company_radio',
			'menu_icon'             => 'dashicons-microphone',
			'menu_position'         => 22,
			'has_archive'           => true,
			'rewrite'               => array( 'slug' => 'radio-companhia' ),
			'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'capability_type'       => 'post',
		)
	);
}
add_action( 'init', 'cdltheme_register_company_carousel_post_types' );

/**
 * Garante que os carrosséis da home consultam os CPTs certos (modelos antigos no editor podem ainda pedir "post").
 *
 * @param array    $query Args do WP_Query.
 * @param WP_Block $block Bloco core/query.
 * @param int      $page  Página.
 * @return array
 */
function cdltheme_query_loop_force_carousel_post_types( array $query, WP_Block $block, int $page ): array {
	$class = isset( $block->attributes['className'] ) ? (string) $block->attributes['className'] : '';
	
	if ( strpos( $class, 'cdl-query-company-selections' ) !== false ) {
		$query['post_type'] = cdltheme_post_type_company_selections();
	} elseif ( strpos( $class, 'cdl-query-company-radio' ) !== false ) {
		$query['post_type'] = cdltheme_post_type_company_radio();
	}
	return $query;
}
add_filter( 'query_loop_block_query_vars', 'cdltheme_query_loop_force_carousel_post_types', 5, 3 );

/**
 * Meta REST + campos no editor para URLs do item de rádio.
 */
function cdltheme_register_radio_url_meta(): void {
	$pt = cdltheme_post_type_company_radio();

	foreach ( array( cdltheme_radio_meta_spotify(), cdltheme_radio_meta_youtube() ) as $meta_key ) {
		register_post_meta(
			$pt,
			$meta_key,
			array(
				'single'            => true,
				'type'              => 'string',
				'show_in_rest'      => true,
				'auth_callback'     => static function (): bool {
					return current_user_can( 'edit_posts' );
				},
				'sanitize_callback' => static function ( $value ) {
					$value = is_string( $value ) ? trim( $value ) : '';
					if ( '' === $value ) {
						return '';
					}
					return esc_url_raw( $value );
				},
			)
		);
	}
}
add_action( 'init', 'cdltheme_register_radio_url_meta' );

/**
 * Caixa no editor para Spotify / YouTube (além do painel REST).
 */
function cdltheme_radio_urls_meta_box( WP_Post $post ): void {
	if ( cdltheme_post_type_company_radio() !== $post->post_type ) {
		return;
	}
	wp_nonce_field( 'cdltheme_radio_urls', 'cdltheme_radio_urls_nonce' );
	$spotify = (string) get_post_meta( $post->ID, cdltheme_radio_meta_spotify(), true );
	$youtube = (string) get_post_meta( $post->ID, cdltheme_radio_meta_youtube(), true );
	?>
	<p>
		<label for="cdl_spotify_url"><strong><?php esc_html_e( 'Spotify', 'cdltheme' ); ?></strong></label><br />
		<input type="url" class="widefat" id="cdl_spotify_url" name="cdl_spotify_url" value="<?php echo esc_attr( $spotify ); ?>" placeholder="https://" />
	</p>
	<p>
		<label for="cdl_youtube_url"><strong><?php esc_html_e( 'YouTube', 'cdltheme' ); ?></strong></label><br />
		<input type="url" class="widefat" id="cdl_youtube_url" name="cdl_youtube_url" value="<?php echo esc_attr( $youtube ); ?>" placeholder="https://" />
	</p>
	<?php
}

function cdltheme_add_radio_meta_box(): void {
	add_meta_box(
		'cdltheme_radio_urls',
		__( 'Links Spotify e YouTube', 'cdltheme' ),
		'cdltheme_radio_urls_meta_box',
		cdltheme_post_type_company_radio(),
		'side',
		'high',
		array(
			'__block_editor_compatible_meta_box' => true,
			'__back_compat_meta_box'             => false,
		)
	);
}
add_action( 'add_meta_boxes', 'cdltheme_add_radio_meta_box' );

/**
 * Aviso no ecrã de edição de itens de rádio (onde estão os links).
 */
function cdltheme_radio_edit_admin_notice(): void {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'post' !== $screen->base || cdltheme_post_type_company_radio() !== $screen->post_type ) {
		return;
	}
	echo '<div class="notice notice-info"><p>';
	echo esc_html__( 'Os links Spotify e YouTube ficam no painel da direita, em «Links Spotify e YouTube» (abaixo das categorias / imagem destacada). Guarde o item depois de preencher.', 'cdltheme' );
	echo '</p></div>';
}
add_action( 'admin_notices', 'cdltheme_radio_edit_admin_notice' );

/**
 * Guarda URLs do rádio.
 */
function cdltheme_save_radio_urls_meta( int $post_id ): void {
	if ( ! isset( $_POST['cdltheme_radio_urls_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cdltheme_radio_urls_nonce'] ) ), 'cdltheme_radio_urls' ) ) {
		return;
	}
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( cdltheme_post_type_company_radio() !== get_post_type( $post_id ) ) {
		return;
	}

	$spotify = isset( $_POST['cdl_spotify_url'] ) ? esc_url_raw( wp_unslash( (string) $_POST['cdl_spotify_url'] ) ) : '';
	$youtube = isset( $_POST['cdl_youtube_url'] ) ? esc_url_raw( wp_unslash( (string) $_POST['cdl_youtube_url'] ) ) : '';

	update_post_meta( $post_id, cdltheme_radio_meta_spotify(), $spotify );
	update_post_meta( $post_id, cdltheme_radio_meta_youtube(), $youtube );
}
add_action( 'save_post', 'cdltheme_save_radio_urls_meta' );

/**
 * HTML dos botões Spotify / YouTube para um item de rádio.
 */
function cdltheme_get_radio_links_markup( int $post_id ): string {
	if ( $post_id <= 0 || cdltheme_post_type_company_radio() !== get_post_type( $post_id ) ) {
		return '';
	}

	$spotify = (string) get_post_meta( $post_id, cdltheme_radio_meta_spotify(), true );
	$youtube = (string) get_post_meta( $post_id, cdltheme_radio_meta_youtube(), true );

	if ( '' === $spotify && '' === $youtube ) {
		return '';
	}

	$spotify_label = esc_html__( 'Spotify', 'cdltheme' );
	$youtube_label = esc_html__( 'YouTube', 'cdltheme' );

	$out = '<div class="wp-block-buttons cdl-carousel__links" style="margin-top:0.75rem;">';
	
	if ( '' !== $spotify ) {
		$out .= '<div class="wp-block-button cdl-carousel__link-btn is-style-outline">';
		$out .= '<a class="wp-block-button__link wp-element-button" href="' . esc_url( $spotify ) . '" target="_blank" rel="noopener noreferrer">' . $spotify_label . '</a>';
		$out .= '</div>';
	}
	
	if ( '' !== $youtube ) {
		$out .= '<div class="wp-block-button cdl-carousel__link-btn is-style-outline">';
		$out .= '<a class="wp-block-button__link wp-element-button" href="' . esc_url( $youtube ) . '" target="_blank" rel="noopener noreferrer">' . $youtube_label . '</a>';
		$out .= '</div>';
	}
	
	$out .= '</div>';

	return $out;
}

/**
 * Injeta links Spotify/YouTube dentro dos slides do carrossel de rádio.
 * O filtro adiciona os links automaticamente ao renderizar cada slide.
 *
 * @param string   $content HTML do grupo.
 * @param array    $block   Bloco parseado.
 * @param WP_Block $instance Instância.
 */
function cdltheme_render_block_radio_slide_append_links( string $content, array $block, WP_Block $instance ): string {
	// Só processa blocos core/group
	if ( ( $block['blockName'] ?? '' ) !== 'core/group' ) {
		return $content;
	}
	
	// Verifica se é um slide de rádio
	$class = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
	if ( strpos( $class, 'cdl-carousel__slide--radio' ) === false ) {
		return $content;
	}
	
	// Evita duplicação (se os links já foram injetados)
	if ( strpos( $content, 'cdl-carousel__links' ) !== false ) {
		return $content;
	}
	
	// Valida o post atual
	$post_id = (int) get_the_ID();
	if ( $post_id <= 0 || cdltheme_post_type_company_radio() !== get_post_type( $post_id ) ) {
		return $content;
	}
	
	// Gera e injeta os links antes do fechamento da div
	$extra = cdltheme_get_radio_links_markup( $post_id );
	if ( '' === $extra ) {
		return $content;
	}
	
	// Injeta antes da tag de fechamento </div>
	$pos = strrpos( $content, '</div>' );
	if ( false === $pos ) {
		return $content . $extra;
	}
	
	return substr_replace( $content, $extra, $pos, 0 );
}
add_filter( 'render_block', 'cdltheme_render_block_radio_slide_append_links', 20, 3 );

add_action(
	'after_switch_theme',
	static function (): void {
		flush_rewrite_rules();
	}
);

/**
 * Caminho da imagem destacada padrão para posts sem thumb.
 */
function cdltheme_default_thumbnail_path(): string {
	return get_theme_file_path( 'assets/default.png' );
}

/**
 * URL pública da imagem destacada padrão.
 */
function cdltheme_default_thumbnail_url(): string {
	return get_theme_file_uri( 'assets/default.png' );
}

/**
 * @param int|null $post_id ID do post; null usa o post atual.
 */
function cdltheme_get_post_thumbnail_url( ?int $post_id = null, string $size = 'post-thumbnail' ): string {
	$post_id = $post_id ?? (int) get_the_ID();
	if ( $post_id <= 0 ) {
		return '';
	}

	$url = get_the_post_thumbnail_url( $post_id, $size );
	if ( is_string( $url ) && $url !== '' ) {
		return $url;
	}

	if ( 'post' === get_post_type( $post_id ) && is_readable( cdltheme_default_thumbnail_path() ) ) {
		return cdltheme_default_thumbnail_url();
	}

	return '';
}

/**
 * Fallback de imagem destacada para posts sem thumb.
 *
 * @param array<string, mixed> $attr
 */
function cdltheme_post_thumbnail_html_fallback( string $html, int $post_id, int $post_thumbnail_id, string|array $size, $attr ): string {
	unset( $post_thumbnail_id, $size );

	if ( $html !== '' || 'post' !== get_post_type( $post_id ) || ! is_readable( cdltheme_default_thumbnail_path() ) ) {
		return $html;
	}

	$attr = wp_parse_args(
		is_array( $attr ) ? $attr : array(),
		array(
			'class' => 'attachment-post-thumbnail size-post-thumbnail wp-post-image cdl-default-thumbnail',
		)
	);

	return sprintf(
		'<img src="%s" alt="%s" class="%s" loading="lazy" decoding="async" />',
		esc_url( cdltheme_default_thumbnail_url() ),
		esc_attr( trim( wp_strip_all_tags( get_the_title( $post_id ) ) ) ),
		esc_attr( (string) $attr['class'] )
	);
}
add_filter( 'post_thumbnail_html', 'cdltheme_post_thumbnail_html_fallback', 10, 5 );

/**
 * @param mixed $thumbnail_url
 */
function cdltheme_post_thumbnail_url_fallback( $thumbnail_url, int $post_id, string|array $size ): string {
	unset( $size );

	if ( is_string( $thumbnail_url ) && $thumbnail_url !== '' ) {
		return $thumbnail_url;
	}

	if ( 'post' !== get_post_type( $post_id ) || ! is_readable( cdltheme_default_thumbnail_path() ) ) {
		return is_string( $thumbnail_url ) ? $thumbnail_url : '';
	}

	return cdltheme_default_thumbnail_url();
}
add_filter( 'post_thumbnail_url', 'cdltheme_post_thumbnail_url_fallback', 10, 3 );

/**
 * Busca a URL de um bloco core/image pelo className dentro de uma árvore de blocos.
 *
 * @param array  $blocks    Blocos parseados.
 * @param string $class_name Classe CSS do bloco de imagem.
 */
function cdltheme_find_block_image_url_by_class( array $blocks, string $class_name ): ?string {
	foreach ( $blocks as $block ) {
		if ( ( $block['blockName'] ?? '' ) === 'core/image' ) {
			$block_class = (string) ( $block['attrs']['className'] ?? '' );
			if ( str_contains( $block_class, $class_name ) ) {
				if ( ! empty( $block['attrs']['url'] ) ) {
					return (string) $block['attrs']['url'];
				}

				if ( ! empty( $block['attrs']['id'] ) ) {
					$url = wp_get_attachment_image_url( (int) $block['attrs']['id'], 'full' );
					if ( $url ) {
						return $url;
					}
				}

				$inner_html = (string) ( $block['innerHTML'] ?? '' );
				if ( preg_match( '/\bsrc=["\']([^"\']+)["\']/', $inner_html, $matches ) ) {
					return html_entity_decode( $matches[1], ENT_QUOTES, 'UTF-8' );
				}
			}
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			$url = cdltheme_find_block_image_url_by_class( $block['innerBlocks'], $class_name );
			if ( null !== $url ) {
				return $url;
			}
		}
	}

	return null;
}

/**
 * Lê imagem editável do template part header salvo no editor (quando existir).
 *
 * @param string $class_name Classe CSS do bloco de imagem.
 * @param string $fallback   URL padrão do tema.
 */
function cdltheme_get_header_image_url( string $class_name, string $fallback ): string {
	$template = get_block_template( get_stylesheet() . '//header', 'wp_template_part' );
	if ( ! $template instanceof WP_Block_Template || empty( $template->content ) ) {
		return $fallback;
	}

	$url = cdltheme_find_block_image_url_by_class( parse_blocks( $template->content ), $class_name );
	if ( null === $url || '' === $url ) {
		return $fallback;
	}

	return esc_url( $url );
}

/**
 * Patterns PHP do tema que devem ser renderizados dinamicamente (ignoram snapshot do editor).
 *
 * @return string[] Slugs completos, ex.: cdltheme/cdl-home-most-read.
 */
function cdltheme_dynamic_pattern_slugs(): array {
	return array(
		'cdltheme/cdl-header',
		'cdltheme/cdl-home-most-read',
		'cdltheme/cdl-home-latest-carousel',
		'cdltheme/cdl-home-selections-carousel',
		'cdltheme/cdl-home-radio-carousel',
		'cdltheme/cdl-footer',
	);
}

/**
 * Renderiza um pattern PHP do tema via do_blocks().
 *
 * @param string $slug Slug completo do pattern.
 */
function cdltheme_render_pattern_from_file( string $slug ): ?string {
	if ( ! in_array( $slug, cdltheme_dynamic_pattern_slugs(), true ) ) {
		return null;
	}

	if ( 'cdltheme/cdl-home-most-read' === $slug ) {
		$html = cdltheme_render_most_read_section();
		return '' !== $html ? $html : null;
	}

	$short = str_contains( $slug, '/' ) ? substr( $slug, strrpos( $slug, '/' ) + 1 ) : $slug;
	$file  = get_theme_file_path( 'patterns/' . $short . '.php' );

	if ( ! is_readable( $file ) ) {
		return null;
	}

	ob_start();
	include $file;
	$markup = ob_get_clean();

	if ( ! is_string( $markup ) || '' === trim( $markup ) ) {
		return null;
	}

	return do_blocks( $markup );
}

/**
 * Força patterns dinâmicos a renderizar a partir dos arquivos PHP do tema.
 *
 * @param string|null $pre_render   HTML pré-renderizado ou null.
 * @param array       $parsed_block Bloco parseado.
 */
function cdltheme_pre_render_dynamic_pattern( $pre_render, array $parsed_block ) {
	if ( null !== $pre_render ) {
		return $pre_render;
	}

	if ( ( $parsed_block['blockName'] ?? '' ) !== 'core/pattern' ) {
		return $pre_render;
	}

	$slug = (string) ( $parsed_block['attrs']['slug'] ?? '' );
	if ( '' === $slug ) {
		return $pre_render;
	}

	$html = cdltheme_render_pattern_from_file( $slug );

	return null !== $html ? $html : $pre_render;
}
add_filter( 'pre_render_block', 'cdltheme_pre_render_dynamic_pattern', 9, 2 );

/**
 * Renderiza o cabeçalho sempre a partir do pattern do tema (ignora customização no editor).
 *
 * @param string|null $pre_render   HTML pré-renderizado ou null.
 * @param array       $parsed_block Bloco parseado.
 */
function cdltheme_pre_render_header_template_part( $pre_render, array $parsed_block ) {
	if ( null !== $pre_render ) {
		return $pre_render;
	}

	if ( ( $parsed_block['blockName'] ?? '' ) !== 'core/template-part' ) {
		return $pre_render;
	}

	if ( ( $parsed_block['attrs']['slug'] ?? '' ) !== 'header' ) {
		return $pre_render;
	}

	$theme = $parsed_block['attrs']['theme'] ?? wp_get_theme()->get_stylesheet();
	if ( $theme !== wp_get_theme()->get_stylesheet() ) {
		return $pre_render;
	}

	$html = cdltheme_render_pattern_from_file( 'cdltheme/cdl-header' );
	if ( null === $html ) {
		return $pre_render;
	}

	$tag = $parsed_block['attrs']['tagName'] ?? 'header';
	$tag = tag_escape( $tag );
	if ( '' === $tag ) {
		$tag = 'header';
	}

	return sprintf(
		'<%1$s class="wp-block-template-part">%2$s</%1$s>',
		$tag,
		$html
	);
}
add_filter( 'pre_render_block', 'cdltheme_pre_render_header_template_part', 10, 2 );

add_action(
	'after_setup_theme',
	static function (): void {
		load_theme_textdomain( 'cdltheme', get_theme_file_path( 'languages' ) );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
		add_editor_style( 'assets/css/fonts.css' );
		add_editor_style( 'assets/css/editor-style.css' );
		add_editor_style( 'assets/css/home.css' );
		add_editor_style( 'assets/css/archive.css' );
		add_editor_style( 'assets/css/content.css' );
	}
);

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		$ver = wp_get_theme()->get( 'Version' );
		wp_enqueue_style(
			'cdltheme-fonts',
			get_theme_file_uri( 'assets/css/fonts.css' ),
			array(),
			$ver
		);
		wp_enqueue_style(
			'cdltheme-header',
			get_theme_file_uri( 'assets/css/header.css' ),
			array( 'cdltheme-fonts' ),
			$ver
		);
		wp_enqueue_style(
			'cdltheme-footer',
			get_theme_file_uri( 'assets/css/footer.css' ),
			array( 'cdltheme-header' ),
			$ver
		);
		wp_enqueue_style(
			'cdltheme-content',
			get_theme_file_uri( 'assets/css/content.css' ),
			array( 'cdltheme-footer' ),
			$ver
		);
		wp_enqueue_script(
			'cdltheme-header-drawer',
			get_theme_file_uri( 'assets/js/header-drawer.js' ),
			array(),
			$ver,
			true
		);

		if ( is_front_page() || is_home() ) {
			wp_enqueue_style(
				'cdltheme-home',
				get_theme_file_uri( 'assets/css/home.css' ),
				array( 'cdltheme-footer' ),
				$ver
			);
			wp_enqueue_script(
				'cdltheme-home-carousel',
				get_theme_file_uri( 'assets/js/home-carousel.js' ),
				array(),
				$ver,
				true
			);
		}

		if ( is_category() || is_tag() || is_author() || is_search() || ( is_archive() && ! is_post_type_archive() ) || ( is_home() && ! is_front_page() ) ) {
			wp_enqueue_style(
				'cdltheme-archive',
				get_theme_file_uri( 'assets/css/archive.css' ),
				array( 'cdltheme-footer' ),
				$ver
			);
		}
	}
);
