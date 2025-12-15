<?php
/**
 * Help page for topic content binding.
 */
class CO360_LDNLMCP_Help_Page {

    /**
     * Render help content.
     */
    public function render() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Ayuda – Contenido de los Temas', 'co360-ldnlmcp' ); ?></h1>
            <p><?php esc_html_e( 'Flujo recomendado:', 'co360-ldnlmcp' ); ?></p>
            <ol>
                <li><?php esc_html_e( 'Usa la IA para crear la estructura del curso y sus temas.', 'co360-ldnlmcp' ); ?></li>
                <li><?php esc_html_e( 'Edita cada Tema individualmente desde el editor de WordPress.', 'co360-ldnlmcp' ); ?></li>
                <li><?php esc_html_e( 'En el metabox “Contenido del Tema (CO360)” asigna el Template de Elementor.', 'co360-ldnlmcp' ); ?></li>
                <li><?php esc_html_e( 'Rellena los campos ACF (vídeos, PDFs u otros contenidos) asociados al template.', 'co360-ldnlmcp' ); ?></li>
                <li><?php esc_html_e( 'Selecciona un formulario Gravity Forms si lo necesitas.', 'co360-ldnlmcp' ); ?></li>
                <li><?php esc_html_e( 'Guarda el Tema para aplicar los cambios.', 'co360-ldnlmcp' ); ?></li>
            </ol>
            <p><strong><?php esc_html_e( 'Importante:', 'co360-ldnlmcp' ); ?></strong> <?php esc_html_e( 'Estos contenidos avanzados no se gestionan con IA ni forman parte del MCP. Se aplican de forma determinista desde el metabox.', 'co360-ldnlmcp' ); ?></p>
        </div>
        <?php
    }
}
