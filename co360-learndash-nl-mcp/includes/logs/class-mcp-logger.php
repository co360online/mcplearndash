<?php
/**
 * Basic logger for MCP executions.
 */
class CO360_LDMCP_MCP_Logger {
    /**
     * Store log entry.
     *
     * @param array $entry Log data.
     * @return void
     */
    public function log( array $entry ): void {
        $logs = get_option( 'co360_ldmcp_logs', [] );
        $logs[] = wp_parse_args(
            $entry,
            [
                'timestamp'   => gmdate( 'Y-m-d H:i:s' ),
                'mcp_version' => '1.0',
                'model'       => '',
                'dry_run'     => true,
                'result'      => [
                    'created' => 0,
                    'updated' => 0,
                    'errors'  => 0,
                ],
            ]
        );
        update_option( 'co360_ldmcp_logs', $logs );
    }


    /**
     * Store stage log entry.
     *
     * @param string $stage Stage identifier.
     * @param array  $data Stage payload.
     * @return void
     */
    public function log_stage( string $stage, array $data = [] ): void {
        $this->log(
            [
                'stage' => $stage,
                'data'  => $data,
            ]
        );
    }

}
