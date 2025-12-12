<?php
/**
 * Simple page that explains MCP execution.
 */
class CO360_LDNLMCP_Execute_MCP_Page {

    /**
     * Render execute MCP page.
     */
    public function render() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Ejecutar MCP', 'co360-ldnlmcp' ); ?></h1>
            <p><?php esc_html_e( 'La ejecución siempre está gobernada por el MCP. Puedes pegar un payload MCP válido para recrear cursos.', 'co360-ldnlmcp' ); ?></p>
            <p><?php esc_html_e( 'Usa la página "Crear curso con IA" para generar payloads a partir de lenguaje natural controlado.', 'co360-ldnlmcp' ); ?></p>
        </div>
        <?php
    }
}
