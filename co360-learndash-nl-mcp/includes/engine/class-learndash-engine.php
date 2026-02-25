<?php
/**
 * Engine to execute MCP plans against LearnDash.
 */
class CO360_LDMCP_LearnDash_Engine {
    /**
     * @var CO360_LDMCP_Course_Factory
     */
    protected $course_factory;

    /**
     * @var CO360_LDMCP_Lesson_Factory
     */
    protected $lesson_factory;

    /**
     * @var CO360_LDMCP_Topic_Factory
     */
    protected $topic_factory;

    /**
     * @var CO360_LDMCP_Quiz_Factory
     */
    protected $quiz_factory;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->course_factory = new CO360_LDMCP_Course_Factory();
        $this->lesson_factory = new CO360_LDMCP_Lesson_Factory();
        $this->topic_factory  = new CO360_LDMCP_Topic_Factory();
        $this->quiz_factory   = new CO360_LDMCP_Quiz_Factory();
    }

    /**
     * Execute plan produced by resolver.
     *
     * @param array $plan Plan array.
     * @param bool  $dry_run Dry-run flag.
     * @return array Result summary.
     */
    public function execute_plan( array $plan, bool $dry_run = false ): array {
        $result = [ 'created' => 0, 'updated' => 0, 'errors' => 0 ];
        if ( $dry_run ) {
            return $result;
        }

        $course_id = $this->course_factory->create( $plan['course'] );
        if ( is_wp_error( $course_id ) ) {
            $result['errors']++;
            return $result;
        }
        $result['created']++;

        foreach ( $plan['lessons'] as $lesson ) {
            $lesson_id = $this->lesson_factory->create( $course_id, $lesson );
            if ( is_wp_error( $lesson_id ) ) {
                $result['errors']++;
                continue;
            }
            $result['created']++;

            if ( ! empty( $lesson['topics'] ) ) {
                foreach ( $lesson['topics'] as $topic ) {
                    $topic_id = $this->topic_factory->create( $lesson_id, $course_id, $topic );
                    if ( is_wp_error( $topic_id ) ) {
                        $result['errors']++;
                        continue;
                    }
                    $result['created']++;
                }
            }

            if ( ! empty( $lesson['quiz'] ) ) {
                $quiz_result = $this->quiz_factory->create( $lesson_id, $lesson['quiz'] );
                if ( is_wp_error( $quiz_result ) ) {
                    $result['errors']++;
                } else {
                    $result['created']++;
                }
            }
        }

        return $result;
    }
}
