<?php
/**
 * MCP templates management.
 */
class CO360_LDMCP_Templates_Page {
    /**
     * Register submenu.
     */
    public function register_page(): void {
        add_submenu_page(
            'co360-ldmcp',
            __( 'Plantillas MCP', 'co360-learndash-mcp-ai' ),
            __( 'Plantillas MCP', 'co360-learndash-mcp-ai' ),
            'manage_options',
            'co360-ldmcp-templates',
            [ $this, 'render' ]
        );
    }

    /**
     * Render page.
     */
    public function render(): void {
        if ( isset( $_POST['co360_ldmcp_template_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['co360_ldmcp_template_nonce'] ) ), 'co360_ldmcp_template' ) ) {
            $name      = sanitize_text_field( wp_unslash( $_POST['template_name'] ?? '' ) );
            $template  = json_decode( wp_unslash( $_POST['template_payload'] ?? '' ), true );
            $templates = get_option( 'co360_ldmcp_templates', [] );
            if ( $name && $template ) {
                $templates[ $name ] = $template;
                update_option( 'co360_ldmcp_templates', $templates );
                echo '<div class="updated"><p>' . esc_html__( 'Plantilla guardada', 'co360-learndash-mcp-ai' ) . '</p></div>';
            }
        }

        $templates = get_option( 'co360_ldmcp_templates', [] );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Plantillas MCP', 'co360-learndash-mcp-ai' ); ?></h1>
            <form method="post">
                <?php wp_nonce_field( 'co360_ldmcp_template', 'co360_ldmcp_template_nonce' ); ?>
                <p><label><?php esc_html_e( 'Nombre', 'co360-learndash-mcp-ai' ); ?></label><br/>
                    <input type="text" name="template_name" /></p>
                <p><label><?php esc_html_e( 'Payload parcial', 'co360-learndash-mcp-ai' ); ?></label><br/>
                    <textarea name="template_payload" rows="8" cols="100"></textarea></p>
                <?php submit_button( __( 'Guardar plantilla', 'co360-learndash-mcp-ai' ) ); ?>
            </form>
            <h2><?php esc_html_e( 'Plantillas guardadas', 'co360-learndash-mcp-ai' ); ?></h2>
            <?php if ( $templates ) : ?>
                <ul>
                <?php foreach ( $templates as $name => $template ) : ?>
                    <li><strong><?php echo esc_html( $name ); ?></strong><pre><?php echo esc_html( wp_json_encode( $template, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ); ?></pre></li>
                <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p><?php esc_html_e( 'Sin plantillas guardadas aún.', 'co360-learndash-mcp-ai' ); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
}
