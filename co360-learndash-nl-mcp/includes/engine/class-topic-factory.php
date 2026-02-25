<?php
/**
 * Topic factory.
 */
class CO360_LDMCP_Topic_Factory {
    /**
     * Create topic attached to lesson and course.
     *
     * @param int   $lesson_id Parent lesson.
     * @param int   $course_id Parent course.
     * @param array $topic Topic payload.
     * @return int|WP_Error
     */
    public function create( int $lesson_id, int $course_id, array $topic ) {
        $post_data = [
            'post_title'   => $topic['title'],
            'post_type'    => 'sfwd-topic',
            'post_status'  => 'publish',
            'post_parent'  => $lesson_id,
            'post_content' => wp_kses_post( $topic['content'] ?? '' ),
        ];

        $topic_id = wp_insert_post( $post_data, true );
        if ( is_wp_error( $topic_id ) ) {
            return $topic_id;
        }

        learndash_set_course_relationship( $topic_id, $course_id );
        learndash_set_lesson_assignment( $topic_id, $lesson_id );
        return $topic_id;
    }
}
