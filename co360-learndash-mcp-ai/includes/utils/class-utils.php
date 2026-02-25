<?php
/**
 * Utility helpers.
 */
class CO360_LDMCP_Utils {
    /**
     * Sanitize array deeply.
     *
     * @param array $data Data to sanitize.
     * @return array
     */
    public static function deep_sanitize( array $data ): array {
        return array_map(
            static function ( $value ) {
                if ( is_array( $value ) ) {
                    return self::deep_sanitize( $value );
                }
                return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : $value;
            },
            $data
        );
    }

    /**
     * Retrieve option with default.
     *
     * @param string $key Option name.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public static function get_option( string $key, $default = '' ) {
        $value = get_option( $key, $default );
        return empty( $value ) ? $default : $value;
    }

    /**
     * Parse a date string to midnight timestamp in site timezone.
     *
     * Accepted formats: YYYY-MM-DD and DD/MM/YYYY.
     *
     * @param string $date_string Raw date string.
     * @return int|null
     */
    public static function parse_date_to_timestamp( string $date_string ): ?int {
        $date_string = trim( $date_string );
        $timezone    = wp_timezone();

        if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_string ) ) {
            $date = DateTimeImmutable::createFromFormat( '!Y-m-d', $date_string, $timezone );
            if ( $date && $date->format( 'Y-m-d' ) === $date_string ) {
                return (int) $date->getTimestamp();
            }
            return null;
        }

        if ( preg_match( '/^\d{2}\/\d{2}\/\d{4}$/', $date_string ) ) {
            $date = DateTimeImmutable::createFromFormat( '!d/m/Y', $date_string, $timezone );
            if ( $date && $date->format( 'd/m/Y' ) === $date_string ) {
                return (int) $date->getTimestamp();
            }
            return null;
        }

        return null;
    }

    /**
     * Extract optional course start/end dates from free-text briefing.
     *
     * @param string $briefing Briefing text.
     * @return array
     */
    public static function extract_course_dates_from_briefing( string $briefing ): array {
        $result = [
            'course_start_ts'        => null,
            'course_end_ts'          => null,
            'course_start_date_raw'  => null,
            'course_end_date_raw'    => null,
            'error'                  => null,
        ];

        $start_match = [];
        $end_match   = [];

        if ( preg_match( '/^\s*(Inicio del curso|Fecha de inicio)\s*:\s*(.+)\s*$/mi', $briefing, $start_match ) ) {
            $result['course_start_date_raw'] = trim( $start_match[2] );
            $result['course_start_ts']       = self::parse_date_to_timestamp( $result['course_start_date_raw'] );
            if ( null === $result['course_start_ts'] ) {
                $result['error'] = 'La fecha de inicio no es válida. Usa YYYY-MM-DD o DD/MM/YYYY.';
                return $result;
            }
        }

        if ( preg_match( '/^\s*(Fin del curso|Fecha de finalización)\s*:\s*(.+)\s*$/mi', $briefing, $end_match ) ) {
            $result['course_end_date_raw'] = trim( $end_match[2] );
            $result['course_end_ts']       = self::parse_date_to_timestamp( $result['course_end_date_raw'] );
            if ( null === $result['course_end_ts'] ) {
                $result['error'] = 'La fecha de fin no es válida. Usa YYYY-MM-DD o DD/MM/YYYY.';
                return $result;
            }
        }

        if ( null !== $result['course_start_ts'] && null !== $result['course_end_ts'] && $result['course_start_ts'] > $result['course_end_ts'] ) {
            $result['error'] = 'La fecha de inicio no puede ser mayor que la fecha de fin.';
        }

        return $result;
    }
}
