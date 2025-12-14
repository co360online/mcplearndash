<?php
/**
 * Scans available Gravity Forms.
 */
class CO360_LDNLMCP_GravityForms_Scanner {

    /**
     * List Gravity Forms.
     *
     * @return array[] Array of forms [id, title].
     */
    public function list_forms() {
        if ( ! class_exists( 'GFAPI' ) ) {
            return array();
        }

        $forms = GFAPI::get_forms();
        $result = array();
        foreach ( (array) $forms as $form ) {
            if ( empty( $form['id'] ) ) {
                continue;
            }
            $result[] = array(
                'id'    => intval( $form['id'] ),
                'title' => isset( $form['title'] ) ? $form['title'] : __( 'Formulario sin título', 'co360-ldnlmcp' ),
            );
        }

        return $result;
    }

    /**
     * Get single form by ID.
     *
     * @param int $form_id Form ID.
     * @return array|null
     */
    public function get_form( $form_id ) {
        if ( ! class_exists( 'GFAPI' ) ) {
            return null;
        }

        $form = GFAPI::get_form( intval( $form_id ) );
        if ( ! $form || is_wp_error( $form ) ) {
            return null;
        }

        return array(
            'id'    => intval( $form['id'] ),
            'title' => isset( $form['title'] ) ? $form['title'] : __( 'Formulario sin título', 'co360-ldnlmcp' ),
        );
    }
}
