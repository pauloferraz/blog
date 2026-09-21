<?php
/**
 * Title: Homepage Hero
 * Slug: ciadasletras/hero
 * Inserter: no
 */
?>
<!-- wp:group {"align":"wide","className":"cdl-hero","style":{"spacing":{"blockGap":"var:preset|spacing|30","margin":{"bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained","contentSize":"650px","justifyContent":"left"}} -->
<div class="wp-block-group alignwide cdl-hero" style="margin-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:heading {"level":1} -->
	<h1><?php echo esc_html_x( 'Bem-vindo ao Blog da Companhia', 'Main heading for homepage', 'ciadasletras' ); ?></h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"cdl-hero__subtitle"} -->
	<p class="cdl-hero__subtitle"><?php echo esc_html_x( 'Um espaço para descobrir histórias, autores e ideias. Encontre entrevistas, ensaios, novidades, bastidores e conteúdos para ampliar suas leituras e conhecer ainda mais o universo dos nossos livros.', 'Homepage hero description', 'ciadasletras' ); ?></p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
