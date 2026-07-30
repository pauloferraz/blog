<?php
/**
 * Importa posts a partir de wp-content/uploads/import/posts.csv
 *
 * Uso (com MySQL e WordPress no ar):
 *   php scripts/import-posts.php
 *   php scripts/import-posts.php --dry-run
 *   php scripts/import-posts.php --limit=100
 *   php scripts/import-posts.php --offset=100 --limit=100
 *   php scripts/import-posts.php --repair-categories
 *   php scripts/import-posts.php --repair-thumbnails
 *
 * @package cdl-blog
 */

declare(strict_types=1);

if ( PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, "Execute via CLI: php scripts/import-posts.php\n" );
	exit( 1 );
}

$root = dirname( __DIR__ );
require_once $root . '/wp-load.php';

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Parse simple CLI flags.
 *
 * @return array{dry_run: bool, limit: int|null, offset: int, csv: string, repair_categories: bool, repair_thumbnails: bool}
 */
function cdl_import_parse_args( array $argv ): array {
	$opts = array(
		'dry_run'            => false,
		'limit'              => null,
		'offset'             => 0,
		'csv'                => WP_CONTENT_DIR . '/uploads/import/posts.csv',
		'repair_categories'  => false,
		'repair_thumbnails'  => false,
	);

	foreach ( array_slice( $argv, 1 ) as $arg ) {
		if ( '--dry-run' === $arg ) {
			$opts['dry_run'] = true;
		} elseif ( '--repair-categories' === $arg ) {
			$opts['repair_categories'] = true;
		} elseif ( '--repair-thumbnails' === $arg ) {
			$opts['repair_thumbnails'] = true;
		} elseif ( str_starts_with( $arg, '--limit=' ) ) {
			$opts['limit'] = max( 1, (int) substr( $arg, 8 ) );
		} elseif ( str_starts_with( $arg, '--offset=' ) ) {
			$opts['offset'] = max( 0, (int) substr( $arg, 9 ) );
		} elseif ( str_starts_with( $arg, '--csv=' ) ) {
			$opts['csv'] = substr( $arg, 6 );
		}
	}

	return $opts;
}

/**
 * @return array<int, array<string, string>>
 */
function cdl_import_read_csv( string $path ): array {
	if ( ! is_readable( $path ) ) {
		fwrite( STDERR, "CSV não encontrado: {$path}\n" );
		exit( 1 );
	}

	$handle = fopen( $path, 'rb' );
	if ( false === $handle ) {
		fwrite( STDERR, "Não foi possível abrir: {$path}\n" );
		exit( 1 );
	}

	$header = fgetcsv( $handle );
	if ( ! is_array( $header ) ) {
		fclose( $handle );
		fwrite( STDERR, "CSV inválido (sem cabeçalho).\n" );
		exit( 1 );
	}

	$rows = array();
	while ( ( $row = fgetcsv( $handle ) ) !== false ) {
		if ( count( $row ) < count( $header ) ) {
			$row = array_pad( $row, count( $header ), '' );
		}
		$assoc = array();
		foreach ( $header as $i => $key ) {
			$assoc[ $key ] = isset( $row[ $i ] ) ? (string) $row[ $i ] : '';
		}
		if ( trim( $assoc['title'] ?? '' ) !== '' ) {
			$rows[] = $assoc;
		}
	}

	fclose( $handle );
	return $rows;
}

function cdl_import_log( string $message ): void {
	echo $message . PHP_EOL;
}

function cdl_import_parse_date( string $raw ): string {
	$raw = trim( $raw );
	if ( '' === $raw ) {
		return current_time( 'mysql' );
	}

	$ts = strtotime( $raw );
	if ( false === $ts ) {
		return current_time( 'mysql' );
	}

	return gmdate( 'Y-m-d H:i:s', $ts );
}

function cdl_import_normalize_content( string $content ): string {
	$content = trim( $content );
	if ( '' === $content ) {
		return '';
	}

	if ( str_contains( $content, '<' ) && str_contains( $content, '>' ) ) {
		return wp_kses_post( $content );
	}

	return wpautop( esc_html( $content ) );
}

/**
 * @return string[]
 */
function cdl_import_split_terms( string $raw ): array {
	$parts = preg_split( '/\s*,\s*/', trim( $raw ) ) ?: array();
	$parts = array_map( 'trim', $parts );
	$parts = array_filter( $parts, static fn( string $v ): bool => $v !== '' );

	return array_values( array_unique( $parts ) );
}

/**
 * Atribui termos de taxonomia a um post.
 *
 * Para categorias, usa wp_set_object_terms (wp_set_post_terms falha silenciosamente
 * quando a categoria ainda não existe no banco).
 *
 * @param string[] $terms
 * @return bool
 */
function cdl_import_set_taxonomy_terms( int $post_id, array $terms, string $taxonomy, bool $dry_run ): bool {
	$terms = array_values(
		array_filter(
			array_map( 'trim', $terms ),
			static fn( string $v ): bool => $v !== ''
		)
	);

	if ( ! $terms ) {
		return true;
	}

	if ( $dry_run ) {
		cdl_import_log( "  [dry-run] {$taxonomy}: " . implode( ', ', $terms ) );
		return true;
	}

	$result = wp_set_object_terms( $post_id, $terms, $taxonomy, false );
	if ( is_wp_error( $result ) ) {
		cdl_import_log( "  ERRO ao definir {$taxonomy}: " . $result->get_error_message() );
		return false;
	}

	return true;
}

function cdl_import_find_user_by_display_name( string $name ): ?WP_User {
	$name = trim( $name );
	if ( '' === $name ) {
		return null;
	}

	$users = get_users(
		array(
			'search'         => $name,
			'search_columns' => array( 'display_name', 'user_nicename', 'user_login' ),
			'number'         => 20,
		)
	);

	foreach ( $users as $user ) {
		if ( 0 === strcasecmp( $user->display_name, $name ) ) {
			return $user;
		}
	}

	return null;
}

function cdl_import_get_or_create_author( string $name, bool $dry_run ): int {
	$name = trim( $name );
	if ( '' === $name ) {
		return (int) get_current_user_id() ?: 1;
	}

	$user = cdl_import_find_user_by_display_name( $name );
	if ( $user instanceof WP_User ) {
		return (int) $user->ID;
	}

	if ( $dry_run ) {
		cdl_import_log( "  [dry-run] criaria autor: {$name}" );
		return 1;
	}

	$login_base = sanitize_user( remove_accents( $name ), true );
	$login_base = $login_base !== '' ? $login_base : 'autor';
	$login      = $login_base;
	$suffix     = 1;

	while ( username_exists( $login ) ) {
		$login = $login_base . $suffix;
		++$suffix;
	}

	$user_id = wp_insert_user(
		array(
			'user_login'   => $login,
			'user_pass'    => wp_generate_password( 24, true, true ),
			'user_email'   => $login . '@import.local',
			'display_name' => $name,
			'first_name'   => $name,
			'role'         => 'author',
		)
	);

	if ( is_wp_error( $user_id ) ) {
		cdl_import_log( '  AVISO: não foi possível criar autor "' . $name . '": ' . $user_id->get_error_message() );
		return 1;
	}

	cdl_import_log( "  Autor criado: {$name} (ID {$user_id})" );
	return (int) $user_id;
}

function cdl_import_find_existing_post( string $title, string $date ): ?int {
	$query = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => 'any',
			'title'                  => $title,
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'date_query'             => array(
				array(
					'column'    => 'post_date',
					'year'      => (int) gmdate( 'Y', strtotime( $date ) ?: time() ),
					'monthnum'  => (int) gmdate( 'n', strtotime( $date ) ?: time() ),
					'day'       => (int) gmdate( 'j', strtotime( $date ) ?: time() ),
				),
			),
		)
	);

	if ( ! empty( $query->posts[0] ) ) {
		return (int) $query->posts[0];
	}

	return null;
}

function cdl_import_default_thumb_path(): string {
	if ( function_exists( 'cdltheme_default_thumbnail_path' ) ) {
		return cdltheme_default_thumbnail_path();
	}

	return get_theme_file_path( 'assets/default.png' );
}

function cdl_import_default_thumb_url(): string {
	if ( function_exists( 'cdltheme_default_thumbnail_url' ) ) {
		return cdltheme_default_thumbnail_url();
	}

	return get_theme_file_uri( 'assets/default.png' );
}

function cdl_import_get_default_thumb_attachment_id(): int {
	static $attachment_id = null;

	if ( null !== $attachment_id ) {
		return $attachment_id;
	}

	$path = cdl_import_default_thumb_path();
	if ( ! is_readable( $path ) ) {
		cdl_import_log( '  AVISO: imagem padrão não encontrada em ' . $path );
		$attachment_id = 0;
		return 0;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_cdl_default_thumb_source',
			'meta_value'     => 'theme-default.png',
		)
	);

	if ( ! empty( $existing[0] ) ) {
		$attachment_id = (int) $existing[0];
		return $attachment_id;
	}

	$file_name = wp_unique_filename( wp_upload_dir()['path'], 'default.png' );
	$upload    = wp_upload_bits( $file_name, null, (string) file_get_contents( $path ) );
	if ( ! empty( $upload['error'] ) ) {
		cdl_import_log( '  AVISO: falha ao copiar imagem padrão: ' . $upload['error'] );
		$attachment_id = 0;
		return 0;
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_title'     => 'Imagem padrão do blog',
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_mime_type' => 'image/png',
			'guid'           => $upload['url'],
		),
		$upload['file']
	);

	if ( is_wp_error( $attachment_id ) ) {
		cdl_import_log( '  AVISO: falha ao registrar imagem padrão: ' . $attachment_id->get_error_message() );
		$attachment_id = 0;
		return 0;
	}

	$metadata = wp_generate_attachment_metadata( (int) $attachment_id, $upload['file'] );
	if ( is_array( $metadata ) ) {
		wp_update_attachment_metadata( (int) $attachment_id, $metadata );
	}

	update_post_meta( (int) $attachment_id, '_cdl_default_thumb_source', 'theme-default.png' );
	$attachment_id = (int) $attachment_id;

	return $attachment_id;
}

function cdl_import_set_featured_image( int $post_id, string $url, bool $dry_run ): void {
	$url = trim( $url );

	if ( '' === $url ) {
		if ( $dry_run ) {
			cdl_import_log( '  [dry-run] usaria imagem padrão: ' . cdl_import_default_thumb_url() );
			return;
		}

		$attachment_id = cdl_import_get_default_thumb_attachment_id();
		if ( $attachment_id <= 0 ) {
			return;
		}

		set_post_thumbnail( $post_id, $attachment_id );
		update_post_meta( $post_id, '_cdl_import_thumb_url', 'theme:default.png' );
		cdl_import_log( '  Imagem padrão definida.' );
		return;
	}

	if ( $dry_run ) {
		cdl_import_log( "  [dry-run] baixaria imagem: {$url}" );
		return;
	}

	$existing = get_post_meta( $post_id, '_cdl_import_thumb_url', true );
	if ( is_string( $existing ) && $existing === $url && has_post_thumbnail( $post_id ) ) {
		return;
	}

	$attachment_id = media_sideload_image( $url, $post_id, null, 'id' );
	if ( is_wp_error( $attachment_id ) ) {
		cdl_import_log( '  AVISO: imagem remota falhou, usando padrão: ' . $attachment_id->get_error_message() );
		$attachment_id = cdl_import_get_default_thumb_attachment_id();
		if ( $attachment_id <= 0 ) {
			return;
		}
		update_post_meta( $post_id, '_cdl_import_thumb_url', 'theme:default.png' );
	} else {
		update_post_meta( $post_id, '_cdl_import_thumb_url', $url );
	}

	set_post_thumbnail( $post_id, (int) $attachment_id );
}

$opts = cdl_import_parse_args( $argv );
$all_rows = cdl_import_read_csv( $opts['csv'] );
$rows     = array_slice( $all_rows, $opts['offset'] );

if ( null !== $opts['limit'] ) {
	$rows = array_slice( $rows, 0, $opts['limit'] );
}

cdl_import_log( 'Arquivo: ' . $opts['csv'] );
cdl_import_log( 'Total no CSV: ' . count( $all_rows ) );
if ( $opts['offset'] > 0 ) {
	cdl_import_log( 'Offset: ' . $opts['offset'] );
}
cdl_import_log( 'Registros neste lote: ' . count( $rows ) );

if ( $opts['repair_categories'] ) {
	cdl_import_log( $opts['dry_run'] ? 'Modo: reparo de categorias (dry-run)' : 'Modo: reparo de categorias' );
	cdl_import_log( str_repeat( '-', 60 ) );

	$repaired = 0;
	$missing  = 0;

	foreach ( $rows as $index => $row ) {
		$title   = trim( $row['title'] ?? '' );
		$date    = cdl_import_parse_date( $row['released_date'] ?? '' );
		$post_id = cdl_import_find_existing_post( $title, $date );

		if ( ! $post_id ) {
			++$missing;
			cdl_import_log( '[?] Post não encontrado: ' . $title );
			continue;
		}

		$categories = cdl_import_split_terms( $row['category'] ?? '' );
		if ( ! $categories ) {
			continue;
		}

		cdl_import_log( '[' . ( $index + 1 ) . '/' . count( $rows ) . "] {$title} → " . implode( ', ', $categories ) );
		cdl_import_set_taxonomy_terms( $post_id, $categories, 'category', $opts['dry_run'] );
		++$repaired;
	}

	cdl_import_log( str_repeat( '-', 60 ) );
	cdl_import_log( 'Categorias corrigidas: ' . $repaired );
	cdl_import_log( 'Posts não encontrados: ' . $missing );
	exit( 0 );
}

if ( $opts['repair_thumbnails'] ) {
	cdl_import_log( $opts['dry_run'] ? 'Modo: reparo de imagens (dry-run)' : 'Modo: reparo de imagens' );
	cdl_import_log( str_repeat( '-', 60 ) );

	$repaired = 0;
	$missing  = 0;

	foreach ( $rows as $index => $row ) {
		$title   = trim( $row['title'] ?? '' );
		$date    = cdl_import_parse_date( $row['released_date'] ?? '' );
		$post_id = cdl_import_find_existing_post( $title, $date );

		if ( ! $post_id ) {
			++$missing;
			cdl_import_log( '[?] Post não encontrado: ' . $title );
			continue;
		}

		$thumb = trim( $row['thumb'] ?? '' );
		if ( '' !== $thumb ) {
			continue;
		}

		if ( ! $opts['dry_run'] && has_post_thumbnail( $post_id ) ) {
			$existing = get_post_meta( $post_id, '_cdl_import_thumb_url', true );
			if ( 'theme:default.png' === $existing ) {
				continue;
			}
		}

		cdl_import_log( '[' . ( $index + 1 ) . '/' . count( $rows ) . "] {$title} → imagem padrão" );
		cdl_import_set_featured_image( $post_id, '', $opts['dry_run'] );
		++$repaired;
	}

	cdl_import_log( str_repeat( '-', 60 ) );
	cdl_import_log( 'Imagens padrão aplicadas: ' . $repaired );
	cdl_import_log( 'Posts não encontrados: ' . $missing );
	exit( 0 );
}

cdl_import_log( $opts['dry_run'] ? 'Modo: dry-run' : 'Modo: importação real' );
cdl_import_log( str_repeat( '-', 60 ) );

$stats = array(
	'created'  => 0,
	'skipped'  => 0,
	'errors'   => 0,
	'no_thumb' => 0,
);

foreach ( $rows as $index => $row ) {
	$num   = $index + 1;
	$title = trim( $row['title'] ?? '' );
	$date  = cdl_import_parse_date( $row['released_date'] ?? '' );

	cdl_import_log( "[{$num}/" . count( $rows ) . "] {$title}" );

	if ( cdl_import_find_existing_post( $title, $date ) ) {
		cdl_import_log( '  Pulado (post já existe).' );
		++$stats['skipped'];
		continue;
	}

	$author_id = cdl_import_get_or_create_author( $row['author'] ?? '', $opts['dry_run'] );
	$content   = cdl_import_normalize_content( $row['content'] ?? '' );

	if ( $opts['dry_run'] ) {
		cdl_import_log( "  [dry-run] criaria post | autor ID {$author_id} | data {$date}" );
		cdl_import_set_taxonomy_terms( 0, cdl_import_split_terms( $row['category'] ?? '' ), 'category', true );
		cdl_import_set_taxonomy_terms( 0, cdl_import_split_terms( $row['tags'] ?? '' ), 'post_tag', true );
		cdl_import_set_featured_image( 0, trim( $row['thumb'] ?? '' ), true );
		if ( trim( $row['thumb'] ?? '' ) === '' ) {
			++$stats['no_thumb'];
		}
		++$stats['created'];
		continue;
	}

	$post_id = wp_insert_post(
		array(
			'post_title'   => $title,
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_type'    => 'post',
			'post_author'  => $author_id,
			'post_date'    => $date,
			'post_date_gmt' => get_gmt_from_date( $date ),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		cdl_import_log( '  ERRO: ' . $post_id->get_error_message() );
		++$stats['errors'];
		continue;
	}

	cdl_import_set_taxonomy_terms( $post_id, cdl_import_split_terms( $row['category'] ?? '' ), 'category', false );
	cdl_import_set_taxonomy_terms( $post_id, cdl_import_split_terms( $row['tags'] ?? '' ), 'post_tag', false );

	$thumb = trim( $row['thumb'] ?? '' );
	if ( '' === $thumb ) {
		++$stats['no_thumb'];
	}
	cdl_import_set_featured_image( $post_id, $thumb, false );

	cdl_import_log( "  Criado ID {$post_id}" );
	++$stats['created'];
}

cdl_import_log( str_repeat( '-', 60 ) );
cdl_import_log( 'Criados: ' . $stats['created'] );
cdl_import_log( 'Pulados: ' . $stats['skipped'] );
cdl_import_log( 'Erros:   ' . $stats['errors'] );
cdl_import_log( 'Sem thumb (usaram padrão): ' . $stats['no_thumb'] );

exit( $stats['errors'] > 0 ? 1 : 0 );
