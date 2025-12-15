<?php
/**
 * Builds execution plan from MCP payload.
 */
class CO360_LDNLMCP_MCP_Resolver {

    /**
     * Build plan for LearnDash engine.
     *
     * @param array $payload Payload.
     * @return array
     */
    public function build_plan( $payload ) {
        return array(
            'course'  => $payload['course'],
            'version' => $payload['version'],
        );
    }
}
