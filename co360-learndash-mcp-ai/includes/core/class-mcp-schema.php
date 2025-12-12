<?php
/**
 * JSON schema for LearnDash MCP payloads.
 */
class CO360_LDMCP_MCP_Schema {
    /**
     * Get schema array for json_schema response format.
     *
     * @return array
     */
    public function get_schema(): array {
        return [
            'name'   => 'LearnDashCourseMCP',
            'schema' => [
                'type'       => 'object',
                'properties' => [
                    'mcp'          => [ 'type' => 'string', 'enum' => [ 'LearnDashCourseMCP' ] ],
                    'mcp_version'  => [ 'type' => 'string', 'enum' => [ '1.0' ] ],
                    'mode'         => [ 'type' => 'string', 'enum' => [ 'execute', 'plan' ] ],
                    'dry_run'      => [ 'type' => 'boolean' ],
                    'context'      => [
                        'type'       => 'object',
                        'properties' => [
                            'language'      => [ 'type' => 'string' ],
                            'audience'      => [ 'type' => 'string' ],
                            'level'         => [ 'type' => 'string' ],
                            'accreditation' => [ 'type' => 'boolean' ],
                        ],
                        'required'   => [ 'language', 'audience' ],
                    ],
                    'course'       => [
                        'type'       => 'object',
                        'properties' => $this->get_course_properties(),
                        'required'   => [ 'title', 'objectives', 'credits', 'duration_hours', 'lessons' ],
                    ],
                ],
                'required'   => [ 'mcp', 'mcp_version', 'mode', 'dry_run', 'course' ],
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }

    /**
     * Course properties.
     *
     * @return array
     */
    protected function get_course_properties(): array {
        return [
            'title'           => [ 'type' => 'string' ],
            'objectives'      => [ 'type' => 'array', 'items' => [ 'type' => 'string' ] ],
            'credits'         => [ 'type' => 'integer' ],
            'duration_hours'  => [ 'type' => 'integer' ],
            'editorial_notes' => [ 'type' => 'string' ],
            'settings'        => [ 'type' => 'object' ],
            'lessons'         => [
                'type'  => 'array',
                'items' => [
                    'type'       => 'object',
                    'properties' => [
                        'title'   => [ 'type' => 'string' ],
                        'topics'  => [
                            'type'  => 'array',
                            'items' => [
                                'type'       => 'object',
                                'properties' => [ 'title' => [ 'type' => 'string' ] ],
                                'required'   => [ 'title' ],
                            ],
                        ],
                        'quiz'    => [ 'type' => 'object' ],
                        'meta'    => [ 'type' => 'object' ],
                    ],
                    'required'   => [ 'title' ],
                ],
            ],
        ];
    }
}
