<?php
/**
 * Handles communication with OpenAI Responses API.
 */
class CO360_LDNLMCP_OpenAI_Client {

    /**
     * Perform structured output call.
     *
     * @param string $prompt Prompt text.
     * @param array  $schema Schema definition.
     * @return array|WP_Error
     */
    public function request_schema_output( $prompt, $schema ) {
        $api_key = get_option( 'co360_ldnlmcp_api_key', '' );
        $model   = get_option( 'co360_ldnlmcp_model', 'gpt-4o-mini' );
        $temperature = floatval( get_option( 'co360_ldnlmcp_temperature', 0.2 ) );
        $max_tokens  = intval( get_option( 'co360_ldnlmcp_max_tokens', 1200 ) );

        if ( empty( $api_key ) ) {
            return new WP_Error( 'co360_ldnlmcp_no_api_key', __( 'Configura la API Key de OpenAI.', 'co360-ldnlmcp' ) );
        }

        $body = array(
            'model'    => $model,
            'messages' => array(
                array(
                    'role'    => 'system',
                    'content' => $prompt,
                ),
            ),
            'temperature' => $temperature,
            'max_output_tokens' => $max_tokens,
            'response_format' => array(
                'type'   => 'json_schema',
                'json_schema' => array(
                    'name'   => 'LearnDashCourseMCP',
                    'schema' => $schema,
                    'strict' => true,
                ),
            ),
        );

        $response = wp_remote_post(
            'https://api.openai.com/v1/responses',
            array(
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key,
                ),
                'body'    => wp_json_encode( $body ),
                'timeout' => 45,
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 200 !== $code ) {
            return new WP_Error( 'co360_ldnlmcp_openai_error', __( 'Error al llamar a OpenAI: ', 'co360-ldnlmcp' ) . wp_remote_retrieve_body( $response ) );
        }

        if ( empty( $data['output'][0]['content'][0]['text'] ) ) {
            return new WP_Error( 'co360_ldnlmcp_openai_invalid', __( 'La respuesta de OpenAI no incluye JSON MCP.', 'co360-ldnlmcp' ) );
        }

        $decoded = json_decode( $data['output'][0]['content'][0]['text'], true );
        if ( empty( $decoded ) ) {
            return new WP_Error( 'co360_ldnlmcp_openai_invalid_json', __( 'No se pudo decodificar el JSON devuelto.', 'co360-ldnlmcp' ) );
        }

        return $decoded;
    }
}
