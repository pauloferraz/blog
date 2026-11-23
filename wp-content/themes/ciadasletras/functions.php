<?php
/**
 * Funções do tema Cia das Letras.
 *
 * @package ciadasletras
 */

defined( 'ABSPATH' ) || exit;

/**
 * Carrega o CSS global do tema — tipografia (Good Pro e Silva Text) e
 * estilos gerais (ex.: inversão do logotipo no rodapé) — em todas as
 * páginas, tanto no front-end quanto no editor.
 */
function ciadasletras_enqueue_site_assets(): void {
	$version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'ciadasletras-fonts',
		get_theme_file_uri( 'assets/css/fonts.css' ),
		array(),
		$version
	);

	wp_enqueue_style(
		'ciadasletras-site',
		get_theme_file_uri( 'assets/css/site.css' ),
		array( 'ciadasletras-fonts' ),
		$version
	);
}
add_action( 'wp_enqueue_scripts', 'ciadasletras_enqueue_site_assets' );
add_action( 'enqueue_block_editor_assets', 'ciadasletras_enqueue_site_assets' );

/**
 * Meta key usada para contar visualizações de posts.
 */
function ciadasletras_post_views_meta_key(): string {
	return 'ciadasletras_post_views';
}

/**
 * Incrementa visualizações do post (apenas singular de post, sem preview).
 */
function ciadasletras_track_post_views(): void {
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

	$key   = ciadasletras_post_views_meta_key();
	$count = (int) get_post_meta( $post_id, $key, true );
	update_post_meta( $post_id, $key, $count + 1 );
}
add_action( 'template_redirect', 'ciadasletras_track_post_views' );

/**
 * Verifica se um post pode aparecer em "Mais lidos".
 *
 * @param int $post_id ID do post.
 */
function ciadasletras_is_valid_most_read_post( int $post_id ): bool {
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
 * Remove a contagem de visualizações quando o post é excluído permanentemente.
 *
 * @param int $post_id ID do post.
 */
function ciadasletras_cleanup_post_views_meta( int $post_id ): void {
	if ( 'post' !== get_post_type( $post_id ) ) {
		return;
	}

	delete_post_meta( $post_id, ciadasletras_post_views_meta_key() );
}
add_action( 'before_delete_post', 'ciadasletras_cleanup_post_views_meta' );

/**
 * IDs dos N posts mais visualizados; completa com posts recentes se faltar.
 *
 * @param int $count Quantidade desejada.
 * @return int[]
 */
function ciadasletras_get_most_read_post_ids( int $count = 9 ): array {
	$count = max( 1, $count );
	$key   = ciadasletras_post_views_meta_key();
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
		if ( ! ciadasletras_is_valid_most_read_post( $post_id ) ) {
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
			if ( ! ciadasletras_is_valid_most_read_post( $post_id ) || in_array( $post_id, $ids, true ) ) {
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
 * Posts publicados para o carrossel "Mais lidos".
 *
 * @param int $count Quantidade desejada.
 * @return WP_Post[]
 */
function ciadasletras_get_most_read_posts( int $count = 9 ): array {
	$ids = ciadasletras_get_most_read_post_ids( $count );
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
				return $post instanceof WP_Post && ciadasletras_is_valid_most_read_post( (int) $post->ID );
			}
		)
	);
}

/**
 * HTML de um carrossel de posts (imagem, categoria, título e descrição):
 * usado tanto por "Mais lidos" quanto por "Mais recentes", que só diferem na
 * origem dos posts e nos textos de cabeçalho.
 *
 * @param WP_Post[] $posts Posts a exibir (já limitados à quantidade desejada).
 * @param array     $args {
 *     @type string $marker_class Classe usada para identificar a seção (ex.: "cdl-most-read-carousel").
 *     @type string $heading      Título da seção.
 *     @type string $description Subtítulo da seção.
 *     @type string $empty_message Mensagem exibida quando não há posts.
 *     @type string $prev_label   Rótulo acessível da seta "anterior".
 *     @type string $next_label   Rótulo acessível da seta "próximo".
 *     @type bool   $show_cta     Exibe o botão "Ver todos" (padrão: true).
 * }
 */
function ciadasletras_render_post_carousel_section( array $posts, array $args ): string {
	$marker_class  = $args['marker_class'];
	$heading       = $args['heading'];
	$description   = $args['description'];
	$empty_message = $args['empty_message'];
	$prev_label    = $args['prev_label'];
	$next_label    = $args['next_label'];
	$show_cta      = $args['show_cta'] ?? true;

	ob_start();
	?>
<div class="wp-block-group alignfull cdl-carousel-section <?php echo esc_attr( $marker_class ); ?>" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--30)">
	<div class="cdl-carousel-section__inner">
	<div class="cdl-carousel__header">
		<h2 class="cdl-carousel__heading"><?php echo esc_html( $heading ); ?></h2>

		<div class="cdl-carousel__controls">
			<p class="cdl-carousel__desc"><?php echo esc_html( $description ); ?></p>

			<div class="cdl-carousel__actions">
				<?php if ( $show_cta ) : ?>
					<div class="wp-block-buttons">
						<div class="wp-block-button cdl-carousel__cta"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e( 'Ver todos', 'ciadasletras' ); ?></a></div>
					</div>
				<?php endif; ?>

				<div class="wp-block-buttons cdl-carousel__arrows" role="group" aria-label="<?php echo esc_attr__( 'Navegação do carrossel', 'ciadasletras' ); ?>">
					<div class="wp-block-button cdl-carousel__arrow cdl-carousel__prev"><a class="wp-block-button__link wp-element-button" href="#" aria-label="<?php echo esc_attr( $prev_label ); ?>" aria-disabled="true">←</a></div>
					<div class="wp-block-button cdl-carousel__arrow cdl-carousel__next"><a class="wp-block-button__link wp-element-button" href="#" aria-label="<?php echo esc_attr( $next_label ); ?>">→</a></div>
				</div>
			</div>
		</div>
	</div>

	<?php if ( $posts ) : ?>
		<div class="cdl-carousel" data-cdl-carousel data-cdl-visible="3" data-cdl-total="<?php echo esc_attr( (string) count( $posts ) ); ?>">
			<div class="cdl-carousel__viewport">
				<ul class="cdl-carousel__track">
					<?php foreach ( $posts as $post_obj ) : ?>
						<?php
						$link       = get_permalink( $post_obj );
						$title      = get_the_title( $post_obj );
						$categories = get_the_category( $post_obj->ID );
						$raw_ex     = get_the_excerpt( $post_obj );
						if ( '' === trim( $raw_ex ) ) {
							$raw_ex = wp_strip_all_tags( $post_obj->post_content );
						}
						$excerpt = wp_trim_words( $raw_ex, 20, '…' );
						?>
						<li>
							<article class="cdl-carousel__slide">
								<a href="<?php echo esc_url( $link ); ?>" class="cdl-carousel__thumb">
									<?php
									if ( has_post_thumbnail( $post_obj ) ) {
										echo get_the_post_thumbnail( $post_obj, 'large' );
									}
									?>
								</a>

								<?php if ( $categories ) : ?>
									<div class="cdl-carousel__categories">
										<?php foreach ( $categories as $index => $cat ) : ?>
											<?php if ( $index > 0 ) : ?><span class="cdl-carousel__categories-sep">|</span><?php endif; ?>
											<a href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>

								<h3 class="cdl-carousel__title">
									<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
								</h3>

								<div class="cdl-carousel__excerpt">
									<p><?php echo esc_html( $excerpt ); ?></p>
								</div>
							</article>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	<?php else : ?>
		<p class="cdl-carousel__empty"><?php echo esc_html( $empty_message ); ?></p>
	<?php endif; ?>

	<div class="cdl-carousel-mobile-actions">
		<?php if ( $show_cta ) : ?>
			<div class="wp-block-buttons">
				<div class="wp-block-button cdl-carousel__cta"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e( 'Ver todos', 'ciadasletras' ); ?></a></div>
			</div>
		<?php endif; ?>

		<div class="wp-block-buttons cdl-carousel__arrows" role="group" aria-label="<?php echo esc_attr__( 'Navegação do carrossel', 'ciadasletras' ); ?>">
			<div class="wp-block-button cdl-carousel__arrow cdl-carousel__prev"><a class="wp-block-button__link wp-element-button" href="#" aria-label="<?php echo esc_attr( $prev_label ); ?>" aria-disabled="true">←</a></div>
			<div class="wp-block-button cdl-carousel__arrow cdl-carousel__next"><a class="wp-block-button__link wp-element-button" href="#" aria-label="<?php echo esc_attr( $next_label ); ?>">→</a></div>
		</div>
	</div>
	</div>
</div>
	<?php
	$html = ob_get_clean();

	return is_string( $html ) ? $html : '';
}

/**
 * HTML da seção "Mais lidos" da home: título, subtítulo e os 5 posts mais
 * visualizados numa lista numerada (título e descrição), sem carrossel —
 * os mesmos 5 itens aparecem tanto no desktop quanto no mobile.
 */
function ciadasletras_render_most_read_carousel_section(): string {
	$posts = ciadasletras_get_most_read_posts( 5 );

	ob_start();
	?>
<div class="wp-block-group alignfull cdl-carousel-section cdl-most-read cdl-most-read-carousel" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--30)">
	<div class="cdl-carousel-section__inner">
		<div class="cdl-most-read__header">
			<h2 class="cdl-most-read__heading"><?php esc_html_e( 'Mais lidos', 'ciadasletras' ); ?></h2>

			<div class="cdl-most-read__intro">
				<p><?php esc_html_e( 'Descubra os posts mais lidos do Blog da Companhia e explore histórias sobre livros, autores e literatura.', 'ciadasletras' ); ?></p>
			</div>
		</div>

		<?php if ( $posts ) : ?>
			<div class="cdl-most-read__list">
				<?php foreach ( $posts as $index => $post_obj ) : ?>
					<?php
					$num     = str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT );
					$title   = get_the_title( $post_obj );
					$link    = get_permalink( $post_obj );
					$raw_ex  = get_the_excerpt( $post_obj );
					if ( '' === trim( $raw_ex ) ) {
						$raw_ex = wp_strip_all_tags( $post_obj->post_content );
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
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="cdl-most-read__empty"><?php esc_html_e( 'Nenhum post publicado ainda.', 'ciadasletras' ); ?></p>
		<?php endif; ?>
	</div>
</div>
	<?php
	$html = ob_get_clean();

	return is_string( $html ) ? $html : '';
}

/**
 * Garante que o carrossel "Mais lidos" sempre reflita os dados atuais, mesmo
 * que o bloco HTML tenha sido "congelado" com uma versão antiga ao salvar o
 * template pelo Editor do Site.
 *
 * @param string $block_content HTML já renderizado do bloco.
 */
function ciadasletras_refresh_most_read_carousel_html( string $block_content ): string {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $block_content;
	}

	if ( ! str_contains( $block_content, 'cdl-most-read-carousel' ) ) {
		return $block_content;
	}

	return ciadasletras_render_most_read_carousel_section();
}
add_filter( 'render_block_core/html', 'ciadasletras_refresh_most_read_carousel_html' );

/**
 * Até N posts publicados, dos mais recentes para os mais antigos.
 *
 * @param int $count Quantidade desejada.
 * @return WP_Post[]
 */
function ciadasletras_get_recent_posts( int $count = 9 ): array {
	return get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, $count ),
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
		)
	);
}

/**
 * HTML do carrossel "Mais recentes" da home: título, subtítulo, CTA, setas e
 * os 9 posts mais recentes (imagem, categoria, título e descrição).
 * Idêntico ao carrossel "Mais lidos", mudando apenas a origem dos posts.
 */
function ciadasletras_render_recent_carousel_section(): string {
	return ciadasletras_render_post_carousel_section(
		ciadasletras_get_recent_posts( 9 ),
		array(
			'marker_class'  => 'cdl-recent-carousel',
			'heading'       => __( 'Últimos posts', 'ciadasletras' ),
			'description'   => __( 'Acompanhe as novidades, entrevistas, histórias e conteúdos mais recentes sobre nossos livros, autores e o universo da literatura.', 'ciadasletras' ),
			'empty_message' => __( 'Nenhum post publicado ainda.', 'ciadasletras' ),
			'prev_label'    => __( 'Posts anteriores', 'ciadasletras' ),
			'next_label'    => __( 'Próximos posts', 'ciadasletras' ),
			'show_cta'      => false,
		)
	);
}

/**
 * Garante que o carrossel "Mais recentes" sempre reflita os dados atuais,
 * mesmo que o bloco HTML tenha sido "congelado" ao salvar o template pelo
 * Editor do Site.
 *
 * @param string $block_content HTML já renderizado do bloco.
 */
function ciadasletras_refresh_recent_carousel_html( string $block_content ): string {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $block_content;
	}

	if ( ! str_contains( $block_content, 'cdl-recent-carousel' ) ) {
		return $block_content;
	}

	return ciadasletras_render_recent_carousel_section();
}
add_filter( 'render_block_core/html', 'ciadasletras_refresh_recent_carousel_html' );

/**
 * Carrega CSS/JS do carrossel apenas na home.
 */
function ciadasletras_enqueue_home_carousel_assets(): void {
	if ( ! is_front_page() && ! is_home() ) {
		return;
	}

	$version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'ciadasletras-home',
		get_theme_file_uri( 'assets/css/home.css' ),
		array(),
		$version
	);

	wp_enqueue_script(
		'ciadasletras-home-carousel',
		get_theme_file_uri( 'assets/js/home-carousel.js' ),
		array(),
		$version,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'ciadasletras_enqueue_home_carousel_assets' );

/**
 * Carrega o CSS do carrossel também dentro do editor (Editor do Site e
 * editor de posts), para os blocos "Mais lidos" e "Seleções da Companhia"
 * terem a mesma aparência do front-end em vez do estilo padrão dos blocos.
 */
function ciadasletras_enqueue_home_carousel_editor_assets(): void {
	wp_enqueue_style(
		'ciadasletras-home',
		get_theme_file_uri( 'assets/css/home.css' ),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'enqueue_block_editor_assets', 'ciadasletras_enqueue_home_carousel_editor_assets' );

/**
 * Slug do custom post type "Seleções da Companhia".
 */
function ciadasletras_post_type_selections(): string {
	return 'company_selections';
}

/**
 * Meta key do link de destino de cada seleção/item de rádio.
 */
function ciadasletras_selection_link_meta_key(): string {
	return 'ciadasletras_selection_link';
}

/**
 * Post types que usam a meta box genérica "Link" (imagem, título, descrição
 * e link são os mesmos campos em Seleções e em Rádio Companhia).
 *
 * @return string[]
 */
function ciadasletras_link_field_post_types(): array {
	return array( ciadasletras_post_type_selections(), ciadasletras_post_type_radio() );
}

/**
 * Registra o custom post type "Seleções da Companhia": imagem, título e
 * descrição (resumo) cadastrados no editor; o link é adicionado por uma
 * meta box própria (ciadasletras_add_selection_link_meta_box).
 */
function ciadasletras_register_selections_post_type(): void {
	register_post_type(
		ciadasletras_post_type_selections(),
		array(
			'labels'          => array(
				'name'               => _x( 'Seleções', 'post type general name', 'ciadasletras' ),
				'singular_name'      => _x( 'Seleção', 'post type singular name', 'ciadasletras' ),
				'add_new'            => _x( 'Adicionar nova', 'company_selections', 'ciadasletras' ),
				'add_new_item'       => __( 'Adicionar nova seleção', 'ciadasletras' ),
				'edit_item'          => __( 'Editar seleção', 'ciadasletras' ),
				'new_item'           => __( 'Nova seleção', 'ciadasletras' ),
				'view_item'          => __( 'Ver seleção', 'ciadasletras' ),
				'search_items'       => __( 'Pesquisar seleções', 'ciadasletras' ),
				'not_found'          => __( 'Nenhuma seleção encontrada.', 'ciadasletras' ),
				'not_found_in_trash' => __( 'Nenhuma seleção na lixeira.', 'ciadasletras' ),
				'menu_name'          => __( 'Seleções da Companhia', 'ciadasletras' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'show_in_rest'    => true,
			'rest_base'       => 'company_selections',
			'menu_icon'       => 'dashicons-images-alt2',
			'menu_position'   => 21,
			'supports'        => array( 'title', 'thumbnail', 'excerpt' ),
			'capability_type' => 'post',
		)
	);
}
add_action( 'init', 'ciadasletras_register_selections_post_type' );

/**
 * Adiciona a meta box com o campo "Link" às seleções e aos itens de rádio.
 */
function ciadasletras_add_selection_link_meta_box(): void {
	foreach ( ciadasletras_link_field_post_types() as $post_type ) {
		add_meta_box(
			'ciadasletras_selection_link',
			__( 'Link', 'ciadasletras' ),
			'ciadasletras_render_selection_link_meta_box',
			$post_type,
			'side',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'ciadasletras_add_selection_link_meta_box' );

/**
 * Renderiza o campo de URL da meta box "Link".
 *
 * @param WP_Post $post Seleção ou item de rádio em edição.
 */
function ciadasletras_render_selection_link_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'ciadasletras_save_selection_link', 'ciadasletras_selection_link_nonce' );

	$link = get_post_meta( $post->ID, ciadasletras_selection_link_meta_key(), true );
	?>
	<p>
		<label for="ciadasletras_selection_link_field" class="screen-reader-text"><?php esc_html_e( 'Link de destino', 'ciadasletras' ); ?></label>
		<input
			type="url"
			id="ciadasletras_selection_link_field"
			name="ciadasletras_selection_link_field"
			class="widefat"
			placeholder="https://"
			value="<?php echo esc_attr( $link ); ?>"
		/>
	</p>
	<p class="description"><?php esc_html_e( 'Para onde o card deve levar ao ser clicado.', 'ciadasletras' ); ?></p>
	<?php
}

/**
 * Salva o campo "Link" das seleções e dos itens de rádio.
 *
 * @param int $post_id ID do post salvo.
 */
function ciadasletras_save_selection_link( int $post_id ): void {
	if ( ! isset( $_POST['ciadasletras_selection_link_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ciadasletras_selection_link_nonce'] ) ), 'ciadasletras_save_selection_link' )
	) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! in_array( get_post_type( $post_id ), ciadasletras_link_field_post_types(), true ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$key = ciadasletras_selection_link_meta_key();

	if ( isset( $_POST['ciadasletras_selection_link_field'] ) ) {
		$url = esc_url_raw( wp_unslash( $_POST['ciadasletras_selection_link_field'] ) );

		if ( '' !== $url ) {
			update_post_meta( $post_id, $key, $url );
		} else {
			delete_post_meta( $post_id, $key );
		}
	}
}
add_action( 'save_post', 'ciadasletras_save_selection_link' );

/**
 * Até 18 seleções publicadas, das mais recentes para as mais antigas.
 *
 * @return WP_Post[]
 */
function ciadasletras_get_selections( int $count = 18 ): array {
	return get_posts(
		array(
			'post_type'           => ciadasletras_post_type_selections(),
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, $count ),
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
		)
	);
}

/**
 * HTML do carrossel "Seleções da Companhia" da home: título, subtítulo, CTA,
 * setas e até 18 seleções (imagem, título e descrição), cada uma linkando
 * para o endereço cadastrado no campo "Link".
 */
function ciadasletras_render_selections_carousel_section(): string {
	$selections = ciadasletras_get_selections( 18 );
	$link_key   = ciadasletras_selection_link_meta_key();

	ob_start();
	?>
<div class="wp-block-group alignfull cdl-carousel-section cdl-selections-carousel" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--30)">
	<div class="cdl-carousel-section__inner">
	<div class="cdl-carousel__header">
		<h2 class="cdl-carousel__heading"><?php esc_html_e( 'Seleções da Companhia', 'ciadasletras' ); ?></h2>

		<div class="cdl-carousel__controls">
			<p class="cdl-carousel__desc"><?php esc_html_e( 'Livros escolhidos pela Companhia para você descobrir novas histórias e encontrar sua próxima leitura.', 'ciadasletras' ); ?></p>

			<div class="cdl-carousel__actions">
				<div class="wp-block-buttons cdl-carousel__arrows" role="group" aria-label="<?php echo esc_attr__( 'Navegação do carrossel', 'ciadasletras' ); ?>">
					<div class="wp-block-button cdl-carousel__arrow cdl-carousel__prev"><a class="wp-block-button__link wp-element-button" href="#" aria-label="<?php echo esc_attr__( 'Seleções anteriores', 'ciadasletras' ); ?>" aria-disabled="true">←</a></div>
					<div class="wp-block-button cdl-carousel__arrow cdl-carousel__next"><a class="wp-block-button__link wp-element-button" href="#" aria-label="<?php echo esc_attr__( 'Próximas seleções', 'ciadasletras' ); ?>">→</a></div>
				</div>
			</div>
		</div>
	</div>

	<?php if ( $selections ) : ?>
		<div class="cdl-carousel" data-cdl-carousel data-cdl-visible="3" data-cdl-total="<?php echo esc_attr( (string) count( $selections ) ); ?>">
			<div class="cdl-carousel__viewport">
				<ul class="cdl-carousel__track">
					<?php foreach ( $selections as $selection ) : ?>
						<?php
						$title      = get_the_title( $selection );
						$target_url = get_post_meta( $selection->ID, $link_key, true );
						if ( '' === trim( (string) $target_url ) ) {
							$target_url = get_permalink( $selection );
						}
						$raw_ex  = get_the_excerpt( $selection );
						$excerpt = '' !== trim( $raw_ex ) ? wp_trim_words( $raw_ex, 20, '…' ) : '';
						?>
						<li>
							<article class="cdl-carousel__slide">
								<a href="<?php echo esc_url( $target_url ); ?>" class="cdl-carousel__thumb" target="_blank" rel="noopener">
									<?php
									if ( has_post_thumbnail( $selection ) ) {
										echo get_the_post_thumbnail( $selection, 'large' );
									}
									?>
								</a>

								<h3 class="cdl-carousel__title">
									<a href="<?php echo esc_url( $target_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $title ); ?></a>
								</h3>

								<?php if ( '' !== $excerpt ) : ?>
									<div class="cdl-carousel__excerpt">
										<p><?php echo esc_html( $excerpt ); ?></p>
									</div>
								<?php endif; ?>
							</article>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	<?php else : ?>
		<p class="cdl-carousel__empty"><?php esc_html_e( 'Ainda não há seleções publicadas. Crie entradas em «Seleções da Companhia» no painel.', 'ciadasletras' ); ?></p>
	<?php endif; ?>

	<div class="cdl-carousel-mobile-actions">
		<div class="wp-block-buttons cdl-carousel__arrows" role="group" aria-label="<?php echo esc_attr__( 'Navegação do carrossel', 'ciadasletras' ); ?>">
			<div class="wp-block-button cdl-carousel__arrow cdl-carousel__prev"><a class="wp-block-button__link wp-element-button" href="#" aria-label="<?php echo esc_attr__( 'Seleções anteriores', 'ciadasletras' ); ?>" aria-disabled="true">←</a></div>
			<div class="wp-block-button cdl-carousel__arrow cdl-carousel__next"><a class="wp-block-button__link wp-element-button" href="#" aria-label="<?php echo esc_attr__( 'Próximas seleções', 'ciadasletras' ); ?>">→</a></div>
		</div>
	</div>
	</div>
</div>
	<?php
	$html = ob_get_clean();

	return is_string( $html ) ? $html : '';
}

/**
 * Garante que o carrossel "Seleções da Companhia" sempre reflita os dados
 * atuais, mesmo que o bloco HTML tenha sido "congelado" ao salvar o template
 * pelo Editor do Site.
 *
 * @param string $block_content HTML já renderizado do bloco.
 */
function ciadasletras_refresh_selections_carousel_html( string $block_content ): string {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $block_content;
	}

	if ( ! str_contains( $block_content, 'cdl-selections-carousel' ) ) {
		return $block_content;
	}

	return ciadasletras_render_selections_carousel_section();
}
add_filter( 'render_block_core/html', 'ciadasletras_refresh_selections_carousel_html' );

/**
 * Slug do custom post type "Rádio Companhia".
 */
function ciadasletras_post_type_radio(): string {
	return 'company_radio';
}

/**
 * Meta keys dos links de Spotify e YouTube de cada item de rádio.
 */
function ciadasletras_radio_spotify_meta_key(): string {
	return 'ciadasletras_radio_spotify';
}

function ciadasletras_radio_youtube_meta_key(): string {
	return 'ciadasletras_radio_youtube';
}

/**
 * Registra o custom post type "Rádio Companhia": os mesmos campos de
 * Seleções (imagem, título, descrição e link) mais Spotify e YouTube,
 * adicionados pela meta box ciadasletras_add_radio_urls_meta_box.
 */
function ciadasletras_register_radio_post_type(): void {
	register_post_type(
		ciadasletras_post_type_radio(),
		array(
			'labels'          => array(
				'name'               => _x( 'Rádio Companhia', 'post type general name', 'ciadasletras' ),
				'singular_name'      => _x( 'Item de rádio', 'post type singular name', 'ciadasletras' ),
				'add_new'            => _x( 'Adicionar novo', 'company_radio', 'ciadasletras' ),
				'add_new_item'       => __( 'Adicionar item de rádio', 'ciadasletras' ),
				'edit_item'          => __( 'Editar item de rádio', 'ciadasletras' ),
				'new_item'           => __( 'Novo item de rádio', 'ciadasletras' ),
				'view_item'          => __( 'Ver item', 'ciadasletras' ),
				'search_items'       => __( 'Pesquisar rádio', 'ciadasletras' ),
				'not_found'          => __( 'Nenhum item encontrado.', 'ciadasletras' ),
				'not_found_in_trash' => __( 'Nenhum item na lixeira.', 'ciadasletras' ),
				'menu_name'          => __( 'Rádio Companhia', 'ciadasletras' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'show_in_rest'    => true,
			'rest_base'       => 'company_radio',
			'menu_icon'       => 'dashicons-microphone',
			'menu_position'   => 22,
			'supports'        => array( 'title', 'thumbnail', 'excerpt' ),
			'capability_type' => 'post',
		)
	);
}
add_action( 'init', 'ciadasletras_register_radio_post_type' );

/**
 * Adiciona a meta box com os campos "Spotify" e "YouTube" ao Rádio Companhia.
 */
function ciadasletras_add_radio_urls_meta_box(): void {
	add_meta_box(
		'ciadasletras_radio_urls',
		__( 'Spotify e YouTube', 'ciadasletras' ),
		'ciadasletras_render_radio_urls_meta_box',
		ciadasletras_post_type_radio(),
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'ciadasletras_add_radio_urls_meta_box' );

/**
 * Renderiza os campos de URL da meta box "Spotify e YouTube".
 *
 * @param WP_Post $post Item de rádio em edição.
 */
function ciadasletras_render_radio_urls_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'ciadasletras_save_radio_urls', 'ciadasletras_radio_urls_nonce' );

	$spotify = get_post_meta( $post->ID, ciadasletras_radio_spotify_meta_key(), true );
	$youtube = get_post_meta( $post->ID, ciadasletras_radio_youtube_meta_key(), true );
	?>
	<p>
		<label for="ciadasletras_radio_spotify_field"><strong><?php esc_html_e( 'Spotify', 'ciadasletras' ); ?></strong></label>
		<input
			type="url"
			id="ciadasletras_radio_spotify_field"
			name="ciadasletras_radio_spotify_field"
			class="widefat"
			placeholder="https://"
			value="<?php echo esc_attr( $spotify ); ?>"
		/>
	</p>
	<p>
		<label for="ciadasletras_radio_youtube_field"><strong><?php esc_html_e( 'YouTube', 'ciadasletras' ); ?></strong></label>
		<input
			type="url"
			id="ciadasletras_radio_youtube_field"
			name="ciadasletras_radio_youtube_field"
			class="widefat"
			placeholder="https://"
			value="<?php echo esc_attr( $youtube ); ?>"
		/>
	</p>
	<p class="description"><?php esc_html_e( 'Os botões só aparecem no card quando o respectivo link estiver preenchido.', 'ciadasletras' ); ?></p>
	<?php
}

/**
 * Salva os campos "Spotify" e "YouTube" do Rádio Companhia.
 *
 * @param int $post_id ID do post salvo.
 */
function ciadasletras_save_radio_urls( int $post_id ): void {
	if ( ! isset( $_POST['ciadasletras_radio_urls_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ciadasletras_radio_urls_nonce'] ) ), 'ciadasletras_save_radio_urls' )
	) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ciadasletras_post_type_radio() !== get_post_type( $post_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'ciadasletras_radio_spotify_field' => ciadasletras_radio_spotify_meta_key(),
		'ciadasletras_radio_youtube_field' => ciadasletras_radio_youtube_meta_key(),
	);

	foreach ( $fields as $field_name => $meta_key ) {
		if ( ! isset( $_POST[ $field_name ] ) ) {
			continue;
		}

		$url = esc_url_raw( wp_unslash( $_POST[ $field_name ] ) );

		if ( '' !== $url ) {
			update_post_meta( $post_id, $meta_key, $url );
		} else {
			delete_post_meta( $post_id, $meta_key );
		}
	}
}
add_action( 'save_post', 'ciadasletras_save_radio_urls' );

/**
 * Até 18 itens de rádio publicados, dos mais recentes para os mais antigos.
 *
 * @return WP_Post[]
 */
function ciadasletras_get_radio_items( int $count = 18 ): array {
	return get_posts(
		array(
			'post_type'           => ciadasletras_post_type_radio(),
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, $count ),
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
		)
	);
}

/**
 * HTML dos botões Spotify/YouTube de um item de rádio (só aparecem quando o
 * respectivo link estiver preenchido).
 *
 * @param int $post_id ID do item de rádio.
 */
function ciadasletras_get_radio_links_markup( int $post_id ): string {
	$spotify = (string) get_post_meta( $post_id, ciadasletras_radio_spotify_meta_key(), true );
	$youtube = (string) get_post_meta( $post_id, ciadasletras_radio_youtube_meta_key(), true );

	if ( '' === $spotify && '' === $youtube ) {
		return '';
	}

	ob_start();
	?>
	<div class="wp-block-buttons cdl-carousel__links">
		<?php if ( '' !== $spotify ) : ?>
			<div class="wp-block-button cdl-carousel__link-btn"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $spotify ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Spotify', 'ciadasletras' ); ?></a></div>
		<?php endif; ?>
		<?php if ( '' !== $youtube ) : ?>
			<div class="wp-block-button cdl-carousel__link-btn"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $youtube ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'YouTube', 'ciadasletras' ); ?></a></div>
		<?php endif; ?>
	</div>
	<?php
	$html = ob_get_clean();

	return is_string( $html ) ? $html : '';
}

/**
 * HTML do carrossel "Rádio Companhia" da home: título, subtítulo, CTA, setas
 * e até 18 itens (imagem, título, descrição e botões de Spotify/YouTube
 * quando cadastrados), cada um linkando para o endereço do campo "Link".
 */
function ciadasletras_render_radio_carousel_section(): string {
	$items    = ciadasletras_get_radio_items( 18 );
	$link_key = ciadasletras_selection_link_meta_key();

	ob_start();
	?>
<div class="wp-block-group alignfull cdl-carousel-section cdl-radio-carousel" style="padding-top:var(--wp--preset--spacing--70);padding-right:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--30)">
	<div class="cdl-carousel-section__inner">
	<div class="cdl-carousel__header">
		<h2 class="cdl-carousel__heading"><?php esc_html_e( 'Rádio Companhia', 'ciadasletras' ); ?></h2>

		<div class="cdl-carousel__controls">
			<p class="cdl-carousel__desc"><?php esc_html_e( 'Ouça os episódios e conversas do Rádio Companhia sobre livros, autores e ideias.', 'ciadasletras' ); ?></p>

			<div class="cdl-carousel__actions">
				<div class="wp-block-buttons">
					<div class="wp-block-button cdl-carousel__cta"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e( 'Ver todos', 'ciadasletras' ); ?></a></div>
				</div>

				<div class="wp-block-buttons cdl-carousel__arrows" role="group" aria-label="<?php echo esc_attr__( 'Navegação do carrossel', 'ciadasletras' ); ?>">
					<div class="wp-block-button cdl-carousel__arrow cdl-carousel__prev"><a class="wp-block-button__link wp-element-button" href="#" aria-label="<?php echo esc_attr__( 'Itens anteriores', 'ciadasletras' ); ?>" aria-disabled="true">←</a></div>
					<div class="wp-block-button cdl-carousel__arrow cdl-carousel__next"><a class="wp-block-button__link wp-element-button" href="#" aria-label="<?php echo esc_attr__( 'Próximos itens', 'ciadasletras' ); ?>">→</a></div>
				</div>
			</div>
		</div>
	</div>

	<?php if ( $items ) : ?>
		<div class="cdl-carousel" data-cdl-carousel data-cdl-visible="3" data-cdl-total="<?php echo esc_attr( (string) count( $items ) ); ?>">
			<div class="cdl-carousel__viewport">
				<ul class="cdl-carousel__track">
					<?php foreach ( $items as $item ) : ?>
						<?php
						$title      = get_the_title( $item );
						$target_url = get_post_meta( $item->ID, $link_key, true );
						if ( '' === trim( (string) $target_url ) ) {
							$target_url = get_permalink( $item );
						}
						$raw_ex  = get_the_excerpt( $item );
						$excerpt = '' !== trim( $raw_ex ) ? wp_trim_words( $raw_ex, 20, '…' ) : '';
						$links   = ciadasletras_get_radio_links_markup( $item->ID );
						?>
						<li>
							<article class="cdl-carousel__slide">
								<a href="<?php echo esc_url( $target_url ); ?>" class="cdl-carousel__thumb" target="_blank" rel="noopener">
									<?php
									if ( has_post_thumbnail( $item ) ) {
										echo get_the_post_thumbnail( $item, 'large' );
									}
									?>
								</a>

								<h3 class="cdl-carousel__title">
									<a href="<?php echo esc_url( $target_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $title ); ?></a>
								</h3>

								<?php if ( '' !== $excerpt ) : ?>
									<div class="cdl-carousel__excerpt">
										<p><?php echo esc_html( $excerpt ); ?></p>
									</div>
								<?php endif; ?>

								<?php echo $links; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</article>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	<?php else : ?>
		<p class="cdl-carousel__empty"><?php esc_html_e( 'Ainda não há itens publicados. Crie entradas em «Rádio Companhia» no painel.', 'ciadasletras' ); ?></p>
	<?php endif; ?>

	<div class="cdl-carousel-mobile-actions">
		<div class="wp-block-buttons">
			<div class="wp-block-button cdl-carousel__cta"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e( 'Ver todos', 'ciadasletras' ); ?></a></div>
		</div>

		<div class="wp-block-buttons cdl-carousel__arrows" role="group" aria-label="<?php echo esc_attr__( 'Navegação do carrossel', 'ciadasletras' ); ?>">
			<div class="wp-block-button cdl-carousel__arrow cdl-carousel__prev"><a class="wp-block-button__link wp-element-button" href="#" aria-label="<?php echo esc_attr__( 'Itens anteriores', 'ciadasletras' ); ?>" aria-disabled="true">←</a></div>
			<div class="wp-block-button cdl-carousel__arrow cdl-carousel__next"><a class="wp-block-button__link wp-element-button" href="#" aria-label="<?php echo esc_attr__( 'Próximos itens', 'ciadasletras' ); ?>">→</a></div>
		</div>
	</div>
	</div>
</div>
	<?php
	$html = ob_get_clean();

	return is_string( $html ) ? $html : '';
}

/**
 * Garante que o carrossel "Rádio Companhia" sempre reflita os dados atuais,
 * mesmo que o bloco HTML tenha sido "congelado" ao salvar o template pelo
 * Editor do Site.
 *
 * @param string $block_content HTML já renderizado do bloco.
 */
function ciadasletras_refresh_radio_carousel_html( string $block_content ): string {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $block_content;
	}

	if ( ! str_contains( $block_content, 'cdl-radio-carousel' ) ) {
		return $block_content;
	}

	return ciadasletras_render_radio_carousel_section();
}
add_filter( 'render_block_core/html', 'ciadasletras_refresh_radio_carousel_html' );
