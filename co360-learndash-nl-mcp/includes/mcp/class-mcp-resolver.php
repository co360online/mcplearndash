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
        $plan = array(
            'course'  => $payload['course'],
            'version' => $payload['version'],
        );

        $content_block_resolver = new CO360_LDNLMCP_Content_Block_Resolver();

        foreach ( $plan['course']['modules'] as $module_index => $module ) {
            if ( empty( $module['lessons'] ) ) {
                continue;
            }
            foreach ( $module['lessons'] as $lesson_index => $lesson ) {
                if ( empty( $lesson['content_block'] ) ) {
                    continue;
                }
                $result = $content_block_resolver->validate_and_enrich( $lesson['content_block'] );
                if ( is_wp_error( $result ) ) {
                    return $result;
                }
                $plan['course']['modules'][ $module_index ]['lessons'][ $lesson_index ]['content_block'] = $result;
            }
        }

        return $plan;
    }
}
