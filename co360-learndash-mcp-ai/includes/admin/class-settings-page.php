<?php
/**
 * Settings page for OpenAI integration.
 */
class CO360_LDMCP_Settings_Page {
    /**
     * Register submenu.
     */
    public function register_page(): void {
        add_submenu_page(
            'co360-ldmcp',
            __( 'Ajustes IA', 'co360-learndash-mcp-ai' ),
            __( 'Ajustes IA', 'co360-learndash-mcp-ai' ),
            'manage_options',
            'co360-ldmcp-settings',
            [ $this, 'render' ]
        );
    }

    /**
     * Render settings page.
     */
    public function render(): void {
        if ( isset( $_POST['co360_ldmcp_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['co360_ldmcp_settings_nonce'] ) ), 'co360_ldmcp_settings' ) ) {
            update_option( 'co360_ldmcp_openai_api_key', sanitize_text_field( wp_unslash( $_POST['openai_api_key'] ?? '' ) ) );
            update_option( 'co360_ldmcp_model', sanitize_text_field( wp_unslash( $_POST['model'] ?? 'gpt-4.1' ) ) );
            update_option( 'co360_ldmcp_temperature', (float) ( $_POST['temperature'] ?? 0.2 ) );
            update_option( 'co360_ldmcp_max_tokens', (int) ( $_POST['max_tokens'] ?? 2048 ) );
            update_option( 'co360_ldmcp_timeout', (int) ( $_POST['timeout'] ?? 30 ) );
            echo '<div class="updated"><p>' . esc_html__( 'Ajustes guardados', 'co360-learndash-mcp-ai' ) . '</p></div>';
        }

        $models = [ 'gpt-4.1', 'gpt-4o', 'gpt-4o-mini', 'custom' ];
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Ajustes de IA (OpenAI)', 'co360-learndash-mcp-ai' ); ?></h1>
            <form method="post">
                <?php wp_nonce_field( 'co360_ldmcp_settings', 'co360_ldmcp_settings_nonce' ); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">API Key</th>
                        <td><input type="password" name="openai_api_key" value="" placeholder="••••" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Modelo</th>
                        <td>
                            <select name="model">
                                <?php foreach ( $models as $model ) : ?>
                                    <option value="<?php echo esc_attr( $model ); ?>" <?php selected( get_option( 'co360_ldmcp_model', 'gpt-4.1' ), $model ); ?>><?php echo esc_html( $model ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="button">Cargar modelos desde OpenAI</button>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Temperatura</th>
                        <td><input type="number" step="0.1" name="temperature" value="<?php echo esc_attr( get_option( 'co360_ldmcp_temperature', 0.2 ) ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Max output tokens</th>
                        <td><input type="number" name="max_tokens" value="<?php echo esc_attr( get_option( 'co360_ldmcp_max_tokens', 2048 ) ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Timeout</th>
                        <td><input type="number" name="timeout" value="<?php echo esc_attr( get_option( 'co360_ldmcp_timeout', 30 ) ); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button( __( 'Guardar ajustes', 'co360-learndash-mcp-ai' ) ); ?>
            </form>
        </div>
        <?php
    }
}
