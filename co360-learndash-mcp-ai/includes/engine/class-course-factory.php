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

        if ( isset( $course['course_start_ts'] ) && is_numeric( $course['course_start_ts'] ) ) {
            $start_ts = (int) $course['course_start_ts'];
            update_post_meta( $course_id, '_co360_course_start_date', $start_ts );
            if ( isset( $course['course_start_date_raw'] ) ) {
                update_post_meta( $course_id, '_co360_course_start_date_raw', sanitize_text_field( (string) $course['course_start_date_raw'] ) );
            }

            if ( function_exists( 'learndash_update_setting' ) ) {
                learndash_update_setting( $course_id, 'access_from', $start_ts );
            }
        }

        if ( isset( $course['course_end_ts'] ) && is_numeric( $course['course_end_ts'] ) ) {
            $end_ts = (int) $course['course_end_ts'];
            update_post_meta( $course_id, '_co360_course_end_date', $end_ts );
            if ( isset( $course['course_end_date_raw'] ) ) {
                update_post_meta( $course_id, '_co360_course_end_date_raw', sanitize_text_field( (string) $course['course_end_date_raw'] ) );
            }

            if ( isset( $start_ts ) && $end_ts >= $start_ts && function_exists( 'learndash_update_setting' ) ) {
                $seconds = $end_ts - $start_ts;
                $days    = (int) ceil( $seconds / DAY_IN_SECONDS );
                learndash_update_setting( $course_id, 'expire_access_days', max( 0, $days ) );
            }
        }

        return $course_id;
    }
}
