<?php
/**
 * Resolve MCP payload into engine actions.
 */
class CO360_LDMCP_MCP_Resolver {
    /**
     * Translate payload into execution plan.
     *
     * @param array $payload MCP payload.
     * @return array
     */
    public function resolve_plan( array $payload ): array {
        $course = $payload['course'];
        $plan   = [
            'course'  => $course,
            'lessons' => $course['lessons'] ?? [],
        ];

        return $plan;
    }
}
