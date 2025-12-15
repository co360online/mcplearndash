<?php
/**
 * Basic logger for MCP actions.
 */
class CO360_LDNLMCP_MCP_Logger {

    /**
     * Log an entry.
     *
     * @param string $message Message.
     * @param array  $context Context.
     */
    public function log( $message, $context = array() ) {
        $entry = '[' . gmdate( 'c' ) . '] ' . $message . ' ' . wp_json_encode( $context );
        if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            error_log( $entry );
        }
    }
}
