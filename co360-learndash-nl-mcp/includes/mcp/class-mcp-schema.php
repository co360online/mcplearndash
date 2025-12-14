<?php
/**
 * MCP Schema for LearnDashCourseMCP v1.1
 */
class CO360_LDNLMCP_MCP_Schema {

    /**
     * Return JSON schema for LearnDashCourseMCP v1.1
     *
     * @return array
     */
    public function get_schema() {
        return array(
            'type'                 => 'object',
            'required'             => array( 'version', 'course' ),
            'additionalProperties' => false,
            'properties'           => array(
                'version' => array(
                    'type'    => 'string',
                    'enum'    => array( '1.1.0' ),
                ),
                'course'  => array(
                    'type'                 => 'object',
                    'required'             => array( 'title', 'credits', 'level', 'type', 'language', 'modules', 'final_exam' ),
                    'additionalProperties' => false,
                    'properties'           => array(
                        'title'   => array( 'type' => 'string' ),
                        'credits' => array( 'type' => 'integer', 'minimum' => 1 ),
                        'level'   => array( 'type' => 'string' ),
                        'type'    => array( 'type' => 'string' ),
                        'language'=> array( 'type' => 'string' ),
                        'modules' => array(
                            'type'  => 'array',
                            'items' => array(
                                'type'                 => 'object',
                                'required'             => array( 'title', 'order', 'lessons', 'quizzes' ),
                                'additionalProperties' => false,
                                'properties'           => array(
                                    'title'   => array( 'type' => 'string' ),
                                    'order'   => array( 'type' => 'integer' ),
                                    'lessons' => array(
                                        'type'  => 'array',
                                        'items' => array(
                                            'type'                 => 'object',
                                            'required'             => array( 'title', 'order' ),
                                            'additionalProperties' => false,
                                            'properties'           => array(
                                                'title' => array( 'type' => 'string' ),
                                                'order' => array( 'type' => 'integer' ),
                                                'content_block' => array(
                                                    'type'                 => array( 'object', 'null' ),
                                                    'required'             => array( 'type', 'template_id' ),
                                                    'additionalProperties' => false,
                                                    'properties'           => array(
                                                        'type'            => array( 'type' => 'string', 'enum' => array( 'elementor_template' ) ),
                                                        'template_id'     => array( 'type' => 'integer', 'minimum' => 1 ),
                                                        'acf_fields'      => array(
                                                            'type'                 => array( 'object', 'null' ),
                                                            'additionalProperties' => true,
                                                        ),
                                                        'gravity_form_id' => array( 'type' => array( 'integer', 'null' ) ),
                                                    ),
                                                ),
                                            ),
                                        ),
                                    ),
                                    'quizzes' => array(
                                        'type'  => 'array',
                                        'items' => array(
                                            'type'                 => 'object',
                                            'required'             => array( 'title', 'order' ),
                                            'additionalProperties' => false,
                                            'properties'           => array(
                                                'title' => array( 'type' => 'string' ),
                                                'order' => array( 'type' => 'integer' ),
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                        'final_exam' => array( 'type' => 'boolean' ),
                    ),
                ),
            ),
        );
    }
}
