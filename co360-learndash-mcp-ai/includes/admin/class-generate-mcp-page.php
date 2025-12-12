<?php
/**
 * Page to generate MCP via OpenAI.
 */
class CO360_LDMCP_Generate_MCP_Page {
    /**
     * Register submenu.
     */
    public function register_page(): void {
        add_submenu_page(
            'co360-ldmcp',
            __( 'Generar MCP', 'co360-learndash-mcp-ai' ),
            __( 'Generar MCP', 'co360-learndash-mcp-ai' ),
            'manage_options',
            'co360-ldmcp-generate',
            [ $this, 'render' ]
        );
    }

    /**
     * Render page.
     */
    public function render(): void {
        $generator = new CO360_LDMCP_MCP_AI_Generator();
        $output    = null;
        $error     = null;

        if ( isset( $_POST['co360_ldmcp_generate_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['co360_ldmcp_generate_nonce'] ) ), 'co360_ldmcp_generate' ) ) {
            $briefing = sanitize_textarea_field( wp_unslash( $_POST['briefing'] ?? '' ) );
            $type     = sanitize_text_field( wp_unslash( $_POST['course_type'] ?? 'curso' ) );
            $context  = [
                'language'      => sanitize_text_field( wp_unslash( $_POST['language'] ?? 'es_ES' ) ),
                'audience'      => sanitize_text_field( wp_unslash( $_POST['audience'] ?? 'medicos' ) ),
                'level'         => sanitize_text_field( wp_unslash( $_POST['level'] ?? 'avanzado' ) ),
                'accreditation' => ! empty( $_POST['accreditation'] ),
            ];
            $result = $generator->generate( $briefing, $type, $context );
            if ( is_wp_error( $result ) ) {
                $error = $result;
            } else {
                $output = $result;
            }
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Generar MCP con IA', 'co360-learndash-mcp-ai' ); ?></h1>
            <form method="post">
                <?php wp_nonce_field( 'co360_ldmcp_generate', 'co360_ldmcp_generate_nonce' ); ?>
                <p><label><?php esc_html_e( 'Briefing', 'co360-learndash-mcp-ai' ); ?></label><br/>
                    <textarea name="briefing" rows="5" cols="80"></textarea></p>
                <p><label><?php esc_html_e( 'Tipo de curso', 'co360-learndash-mcp-ai' ); ?></label>
                    <select name="course_type">
                        <option value="curso">Curso acreditado</option>
                        <option value="webinar">Webinar</option>
                        <option value="casos">Casos clínicos</option>
                        <option value="hibrido">Curso híbrido</option>
                    </select></p>
                <p><label>Idioma</label><input type="text" name="language" value="es_ES" />
                    <label>Audiencia</label><input type="text" name="audience" value="medicos" />
                    <label>Nivel</label><input type="text" name="level" value="avanzado" />
                    <label><input type="checkbox" name="accreditation" checked /> Acreditación</label></p>
                <?php submit_button( __( 'Generar MCP con IA', 'co360-learndash-mcp-ai' ) ); ?>
            </form>
            <?php if ( $error ) : ?>
                <div class="notice notice-error"><p><?php echo esc_html( $error->get_error_message() ); ?></p>
                <?php if ( $error->get_error_data() ) : ?>
                    <pre><?php echo esc_html( wp_json_encode( $error->get_error_data(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ); ?></pre>
                <?php endif; ?></div>
            <?php endif; ?>
            <?php if ( $output ) : ?>
                <h2><?php esc_html_e( 'MCP generado', 'co360-learndash-mcp-ai' ); ?></h2>
                <pre><?php echo esc_html( wp_json_encode( $output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ); ?></pre>
            <?php endif; ?>
        </div>
        <?php
    }
}
