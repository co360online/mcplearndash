<?php
/**
 * Lesson factory.
 */
class CO360_LDMCP_Lesson_Factory {
    /**
     * Create lesson and attach to course.
     *
     * @param int   $course_id Parent course.
     * @param array $lesson Lesson payload.
     * @return int|WP_Error
     */
    public function create( int $course_id, array $lesson ) {
        $post_data = [
            'post_title'   => $lesson['title'],
            'post_type'    => 'sfwd-lessons',
            'post_status'  => 'publish',
            'post_parent'  => $course_id,
            'post_content' => wp_kses_post( $lesson['content'] ?? '' ),
        ];

        $lesson_id = wp_insert_post( $post_data, true );
        if ( is_wp_error( $lesson_id ) ) {
            return $lesson_id;
        }

        learndash_set_course_relationship( $lesson_id, $course_id );
        return $lesson_id;
    }
}
