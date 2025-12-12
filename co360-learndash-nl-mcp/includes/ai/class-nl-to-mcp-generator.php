<?php
/**
 * Uses OpenAI to transform natural language into MCP payload.
 */
class CO360_LDNLMCP_NL_To_MCP_Generator {

    /**
     * Client.
     *
     * @var CO360_LDNLMCP_OpenAI_Client
     */
    protected $client;

    /**
     * Prompts.
     *
     * @var CO360_LDNLMCP_Prompt_Library
     */
    protected $prompt_library;

    /**
     * Parser.
     *
     * @var CO360_LDNLMCP_Natural_Language_Parser
     */
    protected $parser;

    /**
     * Constructor.
     */
    public function __construct( CO360_LDNLMCP_OpenAI_Client $client, CO360_LDNLMCP_Prompt_Library $prompt_library ) {
        $this->client = $client;
        $this->prompt_library = $prompt_library;
        $this->parser = new CO360_LDNLMCP_Natural_Language_Parser();
    }

    /**
     * Generate MCP payload.
     *
     * @param CO360_LDNLMCP_NL_Intent $intent Intent.
     * @return array|WP_Error
     */
    public function generate( CO360_LDNLMCP_NL_Intent $intent ) {
        $cues = $this->parser->parse( $intent->description );
        $schema = ( new CO360_LDNLMCP_MCP_Schema() )->get_schema();
        $system_prompt = $this->prompt_library->system_prompt();

        $user_prompt  = "Descripción:\n" . $intent->description . "\n";
        $user_prompt .= 'Metadatos: nivel=' . $intent->level . ', tipo=' . $intent->course_type . ', idioma=' . $intent->language . "\n";
        $user_prompt .= 'Pistas extraídas: ' . wp_json_encode( $cues );

        $response = $this->client->request_schema_output( $system_prompt . "\n" . $user_prompt, $schema );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // Inject defaults if missing.
        if ( empty( $response['course']['title'] ) ) {
            $response['course']['title'] = __( 'Curso generado MCP', 'co360-ldnlmcp' );
        }

        if ( empty( $response['version'] ) ) {
            $response['version'] = ( new CO360_LDNLMCP_MCP_Versioning() )->current_version();
        }

        return $response;
    }
}
