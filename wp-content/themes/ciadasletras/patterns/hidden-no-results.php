<?php
/**
 * Title: Hidden No Results Content
 * Slug: ciadasletras/hidden-no-results-content
 * Inserter: no
 */
?>
<!-- wp:paragraph -->
<p>
<?php echo esc_html_x( 'Desculpe, mas nenhum resultado foi encontrado para sua busca. Tente novamente com outras palavras-chave.', 'Message explaining that there are no results returned from a search', 'ciadasletras' ); ?>
</p>
<!-- /wp:paragraph -->

<!-- wp:search {"label":"<?php echo esc_html_x( 'Buscar', 'label', 'ciadasletras' ); ?>","placeholder":"<?php echo esc_attr_x( 'Buscar...', 'placeholder for search field', 'ciadasletras' ); ?>","showLabel":false,"buttonText":"<?php esc_attr_e( 'Buscar', 'ciadasletras' ); ?>","buttonUseIcon":true,"backgroundColor":"navy","textColor":"base"} /-->
