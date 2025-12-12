<?php
/**
 * OpenAI client wrapper.
 */
class CO360_LDMCP_OpenAI_Client {
    /**
     * Get configured API key.
     *
     * @return string
     */
    protected function get_api_key(): string {
        return (string) get_option( 'co360_ldmcp_openai_api_key', '' );
    }

    /**
     * Build headers for requests.
     *
     * @return array
     */
    protected function get_headers(): array {
        return [
            'Authorization' => 'Bearer ' . $this->get_api_key(),
            'Content-Type'  => 'application/json',
        ];
    }

    /**
     * Call OpenAI responses API using schema enforcement.
     *
     * @param string $model Model identifier.
     * @param array  $messages Chat messages.
     * @param array  $schema Schema array.
     * @param array  $params Extra params.
     * @return array|WP_Error
     */
    public function generate_json( string $model, array $messages, array $schema, array $params = [] ) {
        $endpoint = 'https://api.openai.com/v1/responses';
        $body     = [
            'model'           => $model,
            'messages'        => $messages,
            'response_format' => [
                'type'  => 'json_schema',
                'json_schema' => $schema,
                'strict' => true,
            ],
        ];

        $settings = [
            'temperature'      => (float) get_option( 'co360_ldmcp_temperature', 0.2 ),
            'max_output_tokens'=> (int) get_option( 'co360_ldmcp_max_tokens', 2048 ),
            'timeout'          => (int) get_option( 'co360_ldmcp_timeout', 30 ),
        ];
        $body = array_merge( $body, $settings, $params );

        $response = wp_remote_post(
            $endpoint,
            [
                'headers' => $this->get_headers(),
                'body'    => wp_json_encode( $body ),
                'timeout' => $settings['timeout'],
            ]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $data['output_parsed'] ) ) {
            return new WP_Error( 'co360_invalid_response', 'La respuesta de OpenAI no contiene JSON válido.' );
        }

        return $data['output_parsed'];
    }
}
