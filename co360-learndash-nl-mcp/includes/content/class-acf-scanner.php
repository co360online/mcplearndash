<?php
/**
 * Scans ACF fields associated with Elementor templates.
 */
class CO360_LDNLMCP_ACF_Scanner {

    /**
     * List ACF fields for a given template.
     *
     * @param int $template_id Template ID.
     * @return array[] Array keyed by field name with label/type info.
     */
    public function get_fields_for_template( $template_id ) {
        if ( ! function_exists( 'acf_get_field_groups' ) || ! function_exists( 'acf_get_fields' ) ) {
            return array();
        }

        $template_id = intval( $template_id );
        if ( $template_id <= 0 ) {
            return array();
        }

        $groups = acf_get_field_groups( array( 'post_id' => $template_id ) );
        $fields = array();

        foreach ( $groups as $group ) {
            $group_fields = acf_get_fields( $group );
            foreach ( (array) $group_fields as $field ) {
                $name = isset( $field['name'] ) ? $field['name'] : '';
                if ( empty( $name ) ) {
                    continue;
                }
                $fields[ $name ] = array(
                    'label' => isset( $field['label'] ) ? $field['label'] : $name,
                    'type'  => isset( $field['type'] ) ? $field['type'] : 'text',
                );
            }
        }

        return $fields;
    }
}
