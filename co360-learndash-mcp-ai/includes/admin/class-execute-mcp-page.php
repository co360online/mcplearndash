<?php
/**
 * Page to execute MCP payloads.
 */
class CO360_LDMCP_Execute_MCP_Page {
    /**
     * Register submenu.
     */
    public function register_page(): void {
        add_submenu_page(
            'co360-ldmcp',
            __( 'Ejecutar MCP', 'co360-learndash-mcp-ai' ),
            __( 'Ejecutar MCP', 'co360-learndash-mcp-ai' ),
            'manage_options',
            'co360-ldmcp-execute',
            [ $this, 'render' ]
        );
    }

    /**
     * Render page.
     */
    public function render(): void {
        $definition = new CO360_LDMCP_MCP_Definition();
        $resolver   = new CO360_LDMCP_MCP_Resolver();
        $engine     = new CO360_LDMCP_LearnDash_Engine();
        $logger     = new CO360_LDMCP_MCP_Logger();
        $result     = null;

        if ( isset( $_POST['co360_ldmcp_execute_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['co360_ldmcp_execute_nonce'] ) ), 'co360_ldmcp_execute' ) ) {
            $payload = json_decode( wp_unslash( $_POST['payload'] ?? '' ), true );
            $validation = $definition->validate_payload( $payload );

            if ( is_wp_error( $validation ) ) {
                $result = [ 'error' => $validation->get_error_message() ];
            } else {
                $payload = apply_filters( 'co360_mcp_before_execute', $payload );
                $plan    = $resolver->resolve_plan( $payload );
                $result  = $engine->execute_plan( $plan, (bool) $payload['dry_run'] );
                $logger->log(
                    [
                        'model'   => get_option( 'co360_ldmcp_model', 'gpt-4.1' ),
                        'dry_run' => (bool) $payload['dry_run'],
                        'result'  => $result,
                    ]
                );
                do_action( 'co360_mcp_after_execute', $result );
            }
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Ejecutar MCP', 'co360-learndash-mcp-ai' ); ?></h1>
            <form method="post">
                <?php wp_nonce_field( 'co360_ldmcp_execute', 'co360_ldmcp_execute_nonce' ); ?>
                <p><label><?php esc_html_e( 'Pega el MCP Payload', 'co360-learndash-mcp-ai' ); ?></label><br/>
                    <textarea name="payload" rows="12" cols="100"></textarea></p>
                <?php submit_button( __( 'Validar y ejecutar', 'co360-learndash-mcp-ai' ) ); ?>
            </form>
            <?php if ( $result ) : ?>
                <h2><?php esc_html_e( 'Resultado', 'co360-learndash-mcp-ai' ); ?></h2>
                <pre><?php echo esc_html( wp_json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ); ?></pre>
            <?php endif; ?>
        </div>
        <?php
    }
}
