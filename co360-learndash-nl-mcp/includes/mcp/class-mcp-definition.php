<?php
/**
 * Defines MCP helpers.
 */
class CO360_LDNLMCP_MCP_Definition {

    /**
     * Validates payload against schema and MCP rules.
     *
     * @param array $payload Payload.
     * @return true|WP_Error
     */
    public function validate_payload( $payload ) {
        if ( empty( $payload['course']['credits'] ) ) {
            return new WP_Error( 'co360_ldnlmcp_missing_credits', __( 'El MCP rechaza cursos sin créditos.', 'co360-ldnlmcp' ) );
        }

        if ( empty( $payload['course']['modules'] ) || ! is_array( $payload['course']['modules'] ) ) {
            return new WP_Error( 'co360_ldnlmcp_missing_modules', __( 'El MCP requiere módulos definidos.', 'co360-ldnlmcp' ) );
        }

        foreach ( $payload['course']['modules'] as $module ) {
            if ( empty( $module['title'] ) ) {
                return new WP_Error( 'co360_ldnlmcp_missing_module_title', __( 'Cada módulo necesita título.', 'co360-ldnlmcp' ) );
            }
            if ( ! isset( $module['lessons'] ) ) {
                return new WP_Error( 'co360_ldnlmcp_missing_module_lessons', __( 'Cada módulo debe declarar sus temas (pueden ser 0).', 'co360-ldnlmcp' ) );
            }
        }

        // Basic schema check for version.
        if ( empty( $payload['version'] ) || '1.0.0' !== $payload['version'] ) {
            return new WP_Error( 'co360_ldnlmcp_version', __( 'Versión MCP inválida.', 'co360-ldnlmcp' ) );
        }

        return true;
    }

    /**
     * Converts payload to preview tree.
     *
     * @param array $payload Payload.
     * @return array
     */
    public function to_tree( $payload ) {
        $tree = array();
        $course = $payload['course'];
        $item = array(
            'title'   => $course['title'],
            'credits' => $course['credits'],
            'modules' => array(),
            'final_exam' => ! empty( $course['final_exam'] ),
        );

        foreach ( $course['modules'] as $module ) {
            $module_item = array(
                'title'   => $module['title'],
                'lessons' => array(),
                'quizzes' => array(),
            );

            if ( ! empty( $module['lessons'] ) ) {
                foreach ( $module['lessons'] as $lesson ) {
                    $module_item['lessons'][] = array(
                        'title' => $lesson['title'],
                    );
                }
            }

            if ( ! empty( $module['quizzes'] ) ) {
                foreach ( $module['quizzes'] as $quiz ) {
                    $module_item['quizzes'][] = array(
                        'title' => $quiz['title'],
                    );
                }
            }

            $item['modules'][] = $module_item;
        }

        $tree[] = $item;
        return $tree;
    }
}
