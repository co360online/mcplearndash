<?php
/**
 * Quiz factory.
 */
class CO360_LDMCP_Quiz_Factory {
    /**
     * Create quiz placeholder.
     *
     * @param int   $parent_id Parent post.
     * @param array $quiz Quiz payload.
     * @return int|WP_Error
     */
    public function create( int $parent_id, array $quiz ) {
        $post_data = [
            'post_title'  => $quiz['title'] ?? 'Quiz IA',
            'post_type'   => 'sfwd-quiz',
            'post_status' => 'publish',
            'post_parent' => $parent_id,
        ];

        $quiz_id = wp_insert_post( $post_data, true );
        if ( is_wp_error( $quiz_id ) ) {
            return $quiz_id;
        }

        update_post_meta( $quiz_id, 'co360_scope', $quiz['scope'] ?? 'lesson' );
        if ( isset( $quiz['settings']['hide_feedback_until_last_attempt'] ) ) {
            update_post_meta( $quiz_id, 'co360_hide_feedback_until_last_attempt', (bool) $quiz['settings']['hide_feedback_until_last_attempt'] );
        }
        if ( isset( $quiz['settings']['attempts'] ) ) {
            update_post_meta( $quiz_id, 'co360_attempts', (int) $quiz['settings']['attempts'] );
        }

        return $quiz_id;
    }
}
