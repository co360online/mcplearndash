<?php
/**
 * MCP Definition and validation for LearnDash courses.
 */
class CO360_LDMCP_MCP_Definition {
    /**
     * Return MCP identity for registry.
     *
     * @param string $mcp_name MCP key.
     * @return array
     */
    public function get_identity( string $mcp_name ): array {
        return [
            'mcp'         => $mcp_name,
            'mcp_version' => '1.0',
            'domain'      => 'medical-elearning',
        ];
    }

    /**
     * Allowed entities for LearnDash MCP.
     *
     * @return array
     */
    public function get_entities(): array {
        return [ 'Course', 'Lesson', 'Topic', 'Quiz', 'Question', 'Certificate' ];
    }

    /**
     * Validate payload contract and rules.
     *
     * @param array $payload Provided MCP payload.
     * @return WP_Error|true
     */
    public function validate_payload( array $payload ) {
        $required = [ 'mcp', 'mcp_version', 'mode', 'course' ];
        foreach ( $required as $key ) {
            if ( empty( $payload[ $key ] ) ) {
                return new WP_Error( 'co360_missing_field', sprintf( 'Falta el campo requerido: %s', $key ) );
            }
        }

        if ( 'LearnDashCourseMCP' !== $payload['mcp'] ) {
            return new WP_Error( 'co360_invalid_mcp', 'MCP no reconocido.' );
        }

        $course = $payload['course'];
        $course_required = [ 'title', 'objectives', 'credits', 'duration_hours' ];
        foreach ( $course_required as $key ) {
            if ( empty( $course[ $key ] ) ) {
                return new WP_Error( 'co360_missing_course_field', sprintf( 'El curso requiere el campo: %s', $key ) );
            }
        }


        if ( isset( $course['course_start_ts'] ) && ! is_numeric( $course['course_start_ts'] ) ) {
            return new WP_Error( 'co360_invalid_course_start', 'La fecha de inicio del curso es inválida.' );
        }

        if ( isset( $course['course_end_ts'] ) && ! is_numeric( $course['course_end_ts'] ) ) {
            return new WP_Error( 'co360_invalid_course_end', 'La fecha de fin del curso es inválida.' );
        }

        if ( isset( $course['course_start_ts'], $course['course_end_ts'] ) && (int) $course['course_start_ts'] > (int) $course['course_end_ts'] ) {
            return new WP_Error( 'co360_invalid_course_dates_order', 'La fecha de inicio no puede ser posterior a la fecha de fin.' );
        }

        if ( ! empty( $course['lessons'] ) ) {
            foreach ( $course['lessons'] as $lesson_index => $lesson ) {
                $result = $this->validate_lesson( $lesson, $lesson_index );
                if ( is_wp_error( $result ) ) {
                    return $result;
                }
            }
        }

        return true;
    }

    /**
     * Validate lesson including topics/quizzes.
     *
     * @param array $lesson Lesson payload.
     * @param int   $index  Index.
     * @return WP_Error|true
     */
    protected function validate_lesson( array $lesson, int $index ) {
        if ( empty( $lesson['title'] ) ) {
            return new WP_Error( 'co360_missing_lesson_title', sprintf( 'La lección %d requiere título', $index + 1 ) );
        }

        if ( isset( $lesson['topics'] ) && ! empty( $lesson['topics'] ) ) {
            foreach ( $lesson['topics'] as $topic_index => $topic ) {
                if ( empty( $topic['title'] ) ) {
                    return new WP_Error( 'co360_missing_topic_title', sprintf( 'El topic %d de la lección %d requiere título', $topic_index + 1, $index + 1 ) );
                }
            }
        }

        if ( ! empty( $lesson['quiz'] ) ) {
            $quiz = $lesson['quiz'];
            if ( empty( $quiz['scope'] ) || ! in_array( $quiz['scope'], [ 'course', 'lesson', 'topic' ], true ) ) {
                return new WP_Error( 'co360_invalid_quiz_scope', 'Los cuestionarios deben declarar un scope válido.' );
            }
            if ( ! empty( $quiz['settings']['hide_feedback_until_last_attempt'] ) && empty( $quiz['settings']['attempts'] ) ) {
                return new WP_Error( 'co360_missing_attempts', 'hide_feedback_until_last_attempt requiere número de intentos.' );
            }
        }

        return true;
    }
}
