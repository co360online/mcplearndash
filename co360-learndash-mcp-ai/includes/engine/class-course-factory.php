<?php
/**
 * Course factory to build LearnDash courses.
 */
class CO360_LDMCP_Course_Factory {
    /**
     * Create course post.
     *
     * @param array $course Course payload.
     * @return int|WP_Error
     */
    public function create( array $course ) {
        $post_data = [
            'post_title'   => $course['title'],
            'post_content' => wp_kses_post( implode( '\n', $course['objectives'] ?? [] ) ),
            'post_type'    => 'sfwd-courses',
            'post_status'  => 'publish',
        ];

        $course_id = wp_insert_post( $post_data, true );
        if ( is_wp_error( $course_id ) ) {
            return $course_id;
        }

        update_post_meta( $course_id, 'co360_credits', (int) $course['credits'] );
        update_post_meta( $course_id, 'co360_duration_hours', (int) $course['duration_hours'] );
        if ( isset( $course['editorial_notes'] ) ) {
            update_post_meta( $course_id, 'co360_editorial_notes', sanitize_textarea_field( $course['editorial_notes'] ) );
        }

        return $course_id;
    }
}
