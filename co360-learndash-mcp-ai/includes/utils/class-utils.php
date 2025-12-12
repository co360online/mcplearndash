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
}
