<?php
/**
 * Validates natural language intent before invoking AI.
 */
class CO360_LDNLMCP_NL_Validator {

    /**
     * Validate intent values.
     *
     * @param CO360_LDNLMCP_NL_Intent $intent Intent.
     * @return array Errors.
     */
    public function validate_intent( $intent ) {
        $errors = array();

        if ( empty( $intent->description ) ) {
            $errors[] = __( 'La descripción es obligatoria.', 'co360-ldnlmcp' );
        }

        if ( ! empty( $intent->course_type ) && ! in_array( $intent->course_type, array( 'acreditado', 'webinar', 'casos' ), true ) ) {
            $errors[] = __( 'Tipo de curso no soportado.', 'co360-ldnlmcp' );
        }

        if ( ! empty( $intent->level ) && ! in_array( $intent->level, array( 'basico', 'intermedio', 'avanzado' ), true ) ) {
            $errors[] = __( 'Nivel no soportado.', 'co360-ldnlmcp' );
        }

        return $errors;
    }
}
