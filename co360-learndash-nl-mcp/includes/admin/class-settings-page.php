<?php
/**
 * Settings page for OpenAI configuration.
 */
class CO360_LDNLMCP_Settings_Page {

    /**
     * Render the settings page.
     */
    public function render() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( isset( $_POST['co360_ldnlmcp_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['co360_ldnlmcp_settings_nonce'] ) ), 'co360_ldnlmcp_save_settings' ) ) {
            $api_key     = isset( $_POST['co360_ldnlmcp_api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['co360_ldnlmcp_api_key'] ) ) : '';
            $model       = isset( $_POST['co360_ldnlmcp_model'] ) ? sanitize_text_field( wp_unslash( $_POST['co360_ldnlmcp_model'] ) ) : '';
            $temperature = isset( $_POST['co360_ldnlmcp_temperature'] ) ? floatval( $_POST['co360_ldnlmcp_temperature'] ) : 0;
            $max_tokens  = isset( $_POST['co360_ldnlmcp_max_tokens'] ) ? intval( $_POST['co360_ldnlmcp_max_tokens'] ) : 0;

            update_option( 'co360_ldnlmcp_api_key', $api_key );
            update_option( 'co360_ldnlmcp_model', $model );
            update_option( 'co360_ldnlmcp_temperature', $temperature );
            update_option( 'co360_ldnlmcp_max_tokens', $max_tokens );

            echo '<div class="updated"><p>' . esc_html__( 'Ajustes guardados.', 'co360-ldnlmcp' ) . '</p></div>';
        }

        $api_key     = get_option( 'co360_ldnlmcp_api_key', '' );
        $model       = get_option( 'co360_ldnlmcp_model', 'gpt-4o-mini' );
        $temperature = get_option( 'co360_ldnlmcp_temperature', 0.2 );
        $max_tokens  = get_option( 'co360_ldnlmcp_max_tokens', 1200 );

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Ajustes IA', 'co360-ldnlmcp' ); ?></h1>
            <form method="post">
                <?php wp_nonce_field( 'co360_ldnlmcp_save_settings', 'co360_ldnlmcp_settings_nonce' ); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="co360_ldnlmcp_api_key"><?php esc_html_e( 'OpenAI API Key', 'co360-ldnlmcp' ); ?></label></th>
                        <td><input name="co360_ldnlmcp_api_key" type="password" id="co360_ldnlmcp_api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" autocomplete="off" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="co360_ldnlmcp_model"><?php esc_html_e( 'Modelo', 'co360-ldnlmcp' ); ?></label></th>
                        <td><input name="co360_ldnlmcp_model" type="text" id="co360_ldnlmcp_model" value="<?php echo esc_attr( $model ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="co360_ldnlmcp_temperature"><?php esc_html_e( 'Temperature', 'co360-ldnlmcp' ); ?></label></th>
                        <td><input name="co360_ldnlmcp_temperature" type="number" step="0.1" min="0" max="1" id="co360_ldnlmcp_temperature" value="<?php echo esc_attr( $temperature ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="co360_ldnlmcp_max_tokens"><?php esc_html_e( 'Max Tokens', 'co360-ldnlmcp' ); ?></label></th>
                        <td><input name="co360_ldnlmcp_max_tokens" type="number" min="1" id="co360_ldnlmcp_max_tokens" value="<?php echo esc_attr( $max_tokens ); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button( __( 'Guardar ajustes', 'co360-ldnlmcp' ) ); ?>
            </form>
        </div>
        <?php
    }
}
