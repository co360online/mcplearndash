<?php
/**
 * Scans Elementor templates available for content blocks.
 */
class CO360_LDNLMCP_Elementor_Scanner {

    /**
     * List Elementor templates.
     *
     * @return array[] Array of templates [id, title].
     */
    public function list_templates() {
        if ( ! post_type_exists( 'elementor_library' ) ) {
            return array();
        }

        $query = new WP_Query(
            array(
                'post_type'      => 'elementor_library',
                'posts_per_page' => 100,
                'post_status'    => 'publish',
                'orderby'        => 'title',
                'order'          => 'ASC',
                'fields'         => 'ids',
            )
        );

        $templates = array();
        foreach ( $query->posts as $template_id ) {
            $templates[] = array(
                'id'    => $template_id,
                'title' => get_the_title( $template_id ),
            );
        }

        return $templates;
    }

    /**
     * Get a single template by ID.
     *
     * @param int $template_id Template ID.
     * @return array|null
     */
    public function get_template( $template_id ) {
        $template_id = intval( $template_id );
        if ( $template_id <= 0 || ! get_post( $template_id ) ) {
            return null;
        }

        return array(
            'id'    => $template_id,
            'title' => get_the_title( $template_id ),
        );
    }
}
