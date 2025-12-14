<?php
/**
 * Builds human-readable controlled instructions for content blocks.
 */
class CO360_LDNLMCP_Content_Instruction_Builder {

    /**
     * Build an instruction string to append to the NL textarea.
     *
     * @param array $topic_info Topic info with title and identifiers.
     * @param array $template   Template info [id,title].
     * @param array $acf_fields Values keyed by field name.
     * @param array $acf_meta   Field meta keyed by field name.
     * @param array|null $form  Optional Gravity Form info [id,title].
     * @return string
     */
    public function build_instruction( $topic_info, $template, $acf_fields, $acf_meta, $form = null ) {
        $lines   = array();
        $lines[] = sprintf( 'En el tema "%s":', $topic_info['title'] );
        $lines[] = sprintf( 'usar el template Elementor "%s" (ID %d).', $template['title'], intval( $template['id'] ) );

        if ( ! empty( $acf_fields ) ) {
            $lines[] = 'Asignar:';
            foreach ( $acf_fields as $field_key => $value ) {
                $label = isset( $acf_meta[ $field_key ]['label'] ) ? $acf_meta[ $field_key ]['label'] : $field_key;
                $lines[] = sprintf( '– %s: %s', $label, $value );
            }
        }

        if ( $form ) {
            $lines[] = sprintf( 'Incluir el formulario Gravity Forms "%s" (ID %d).', $form['title'], intval( $form['id'] ) );
        }

        return implode( "\n", $lines );
    }
}
