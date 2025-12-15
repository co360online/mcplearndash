<?php
/**
 * Simple parser for controlled natural language cues.
 */
class CO360_LDNLMCP_Natural_Language_Parser {

    /**
     * Parse text into signals for the MCP generator.
     *
     * @param string $text Description.
     * @return array Parsed cues.
     */
    public function parse( $text ) {
        $cues = array();
        $lower = strtolower( $text );

        // Detect credits.
        if ( preg_match( '/(\d+)\s*cr[eé]ditos?/', $lower, $matches ) ) {
            $cues['credits'] = intval( $matches[1] );
        }

        // Detect module count.
        if ( preg_match( '/(\d+)\s*m[oó]dulos?/', $lower, $matches ) ) {
            $cues['modules'] = intval( $matches[1] );
        }

        // Detect final exam.
        if ( false !== strpos( $lower, 'examen final' ) ) {
            $cues['final_exam'] = true;
        }

        // Detect level.
        if ( false !== strpos( $lower, 'avanzado' ) ) {
            $cues['level'] = 'avanzado';
        } elseif ( false !== strpos( $lower, 'intermedio' ) ) {
            $cues['level'] = 'intermedio';
        } elseif ( false !== strpos( $lower, 'básico' ) || false !== strpos( $lower, 'basico' ) ) {
            $cues['level'] = 'basico';
        }

        // Detect accreditation.
        if ( false !== strpos( $lower, 'acreditado' ) ) {
            $cues['accredited'] = true;
        }

        // Detect tests per module statements.
        if ( preg_match_all( '/m[oó]dulo\s*(\d+)\s*tiene\s*(\d+)\s*temas?/', $lower, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                $index = intval( $match[1] );
                $lessons = intval( $match[2] );
                $cues['module_lessons'][ $index ] = $lessons;
            }
        }

        if ( preg_match_all( '/m[oó]dulo\s*(\d+)\s*(sin temas|sin lecciones)/', $lower, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                $index = intval( $match[1] );
                $cues['module_lessons'][ $index ] = 0;
            }
        }

        if ( preg_match_all( '/test\s+en\s+el\s+m[oó]dulo\s*(\d+)/', $lower, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                $index = intval( $match[1] );
                $cues['module_tests'][ $index ] = true;
            }
        }

        return $cues;
    }
}
