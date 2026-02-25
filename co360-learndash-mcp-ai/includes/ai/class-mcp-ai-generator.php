<?php
/**
 * Generate MCP payloads with OpenAI.
 */
class CO360_LDMCP_MCP_AI_Generator {
    /**
     * @var CO360_LDMCP_OpenAI_Client
     */
    protected $client;

    /**
     * @var CO360_LDMCP_Prompt_Library
     */
    protected $library;

    /**
     * @var CO360_LDMCP_MCP_Schema
     */
    protected $schema;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->client  = new CO360_LDMCP_OpenAI_Client();
        $this->library = new CO360_LDMCP_Prompt_Library();
        $this->schema  = new CO360_LDMCP_MCP_Schema();
    }

    /**
     * Generate MCP payload from briefing and template.
     *
     * @param string $briefing User briefing.
     * @param string $course_type Selected course type.
     * @param array  $context Context array.
     * @return array|WP_Error
     */
    public function generate( string $briefing, string $course_type, array $context = [] ) {
        $system_prompt = $this->library->get_system_prompt();
        $course_prompt = $this->get_course_prompt( $course_type );

        $messages = [
            [ 'role' => 'system', 'content' => $system_prompt ],
            [ 'role' => 'user', 'content' => $course_prompt . '\nBriefing: ' . $briefing ],
        ];

        $model      = get_option( 'co360_ldmcp_model', 'gpt-4.1' );
        $schema     = $this->schema->get_schema();
        $date_parts = CO360_LDMCP_Utils::extract_course_dates_from_briefing( $briefing );

        if ( ! empty( $date_parts['error'] ) ) {
            return new WP_Error( 'co360_invalid_dates', $date_parts['error'], $date_parts );
        }

        $result = $this->client->generate_json( $model, $messages, $schema );
        if ( is_wp_error( $result ) ) {
            return $result;
        }

        if ( isset( $result['course'] ) && is_array( $result['course'] ) ) {
            if ( null !== $date_parts['course_start_ts'] ) {
                $result['course']['course_start_ts']       = $date_parts['course_start_ts'];
                $result['course']['course_start_date_raw'] = $date_parts['course_start_date_raw'];
            }
            if ( null !== $date_parts['course_end_ts'] ) {
                $result['course']['course_end_ts']       = $date_parts['course_end_ts'];
                $result['course']['course_end_date_raw'] = $date_parts['course_end_date_raw'];
            }
        }

        return $result;
    }

    /**
     * Map course type to prompt.
     *
     * @param string $course_type Course type.
     * @return string
     */
    protected function get_course_prompt( string $course_type ): string {
        switch ( $course_type ) {
            case 'webinar':
                return $this->library->generate_webinar_course();
            case 'casos':
                return $this->library->generate_clinical_cases_course();
            default:
                return $this->library->generate_medical_course();
        }
    }
}
