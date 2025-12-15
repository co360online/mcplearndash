<?php
/**
 * Executes MCP plans against LearnDash.
 */
class CO360_LDNLMCP_Learndash_Engine {

    /**
     * Execute plan.
     *
     * @param array $plan Execution plan.
     * @param bool  $dry_run Dry run.
     * @param array $content_binding Optional content bindings keyed by module/lesson.
     * @return true|WP_Error
     */
    public function execute_plan( $plan, $dry_run = false, $content_binding = array() ) {
        $logger = new CO360_LDNLMCP_MCP_Logger();
        $logger->log( 'Ejecutando plan MCP', $plan );
        $course = $plan['course'];

        $binding_model     = new CO360_LDNLMCP_Content_Binding( $content_binding );
        $content_resolver  = new CO360_LDNLMCP_Content_Block_Resolver();

        if ( ! $dry_run && ! function_exists( 'learndash_get_course_id' ) && ! defined( 'LEARNDASH_VERSION' ) ) {
            return new WP_Error( 'co360_ldnlmcp_no_learndash', __( 'LearnDash no está activo. Solo puedes simular.', 'co360-ldnlmcp' ) );
        }

        $course_id = 0;
        if ( $dry_run ) {
            $logger->log( 'Simulación: se crearía el curso', array( 'title' => $course['title'] ) );
        } else {
            $course_id = wp_insert_post( array(
                'post_type'   => 'sfwd-courses',
                'post_status' => 'publish',
                'post_title'  => $course['title'],
            ) );

            if ( is_wp_error( $course_id ) ) {
                return $course_id;
            }

            // Save credits and metadata.
            update_post_meta( $course_id, 'co360_ldnlmcp_credits', intval( $course['credits'] ) );
            update_post_meta( $course_id, 'co360_ldnlmcp_level', sanitize_text_field( $course['level'] ) );
            update_post_meta( $course_id, 'co360_ldnlmcp_type', sanitize_text_field( $course['type'] ) );
            update_post_meta( $course_id, 'co360_ldnlmcp_language', sanitize_text_field( $course['language'] ) );
        }

        // Create modules as lessons (LearnDash does not have modules natively).
        foreach ( $course['modules'] as $module_index => $module ) {
            if ( $dry_run ) {
                $lesson_id = 0;
                $logger->log( 'Simulación: se crearía módulo/lección', array( 'title' => $module['title'] ) );
            } else {
                $lesson_id = wp_insert_post( array(
                    'post_type'   => 'sfwd-lessons',
                    'post_status' => 'publish',
                    'post_title'  => $module['title'],
                    'menu_order'  => intval( $module['order'] ),
                ) );

                if ( is_wp_error( $lesson_id ) ) {
                    return $lesson_id;
                }

                $this->set_course_for_step( $lesson_id, $course_id );
            }

            if ( ! empty( $module['lessons'] ) ) {
                foreach ( $module['lessons'] as $lesson_index => $lesson ) {
                    if ( $dry_run ) {
                        $topic_id = 0;
                        $logger->log( 'Simulación: se crearía tema', array( 'title' => $lesson['title'], 'module' => $module['title'] ) );
                    } else {
                        $topic_id = wp_insert_post( array(
                            'post_type'   => 'sfwd-topic',
                            'post_status' => 'publish',
                            'post_title'  => $lesson['title'],
                            'menu_order'  => intval( $lesson['order'] ),
                        ) );

                        if ( is_wp_error( $topic_id ) ) {
                            return $topic_id;
                        }

                        $this->set_course_for_step( $topic_id, $course_id, $lesson_id );
                    }

                    $binding_block = $binding_model->get_content_block( $module_index, $lesson_index );
                    if ( null !== $binding_block ) {
                        $validated_block = $content_resolver->validate_and_enrich( $binding_block );
                        if ( is_wp_error( $validated_block ) ) {
                            return $validated_block;
                        }
                        $content_result = $this->apply_content_block( $topic_id, $validated_block, $dry_run, $lesson['title'] );
                        if ( is_wp_error( $content_result ) ) {
                            return $content_result;
                        }
                    } else {
                        ( new CO360_LDNLMCP_MCP_Logger() )->log(
                            'Tema sin content_block',
                            array(
                                'stage' => 'content_block',
                                'topic' => $lesson['title'],
                                'status' => 'skipped_no_binding',
                            )
                        );
                    }
                }
            }

            if ( ! empty( $module['quizzes'] ) ) {
                foreach ( $module['quizzes'] as $quiz ) {
                    if ( $dry_run ) {
                        $logger->log( 'Simulación: se crearía test', array( 'title' => $quiz['title'], 'module' => $module['title'] ) );
                        continue;
                    }

                    $quiz_id = wp_insert_post( array(
                        'post_type'   => 'sfwd-quiz',
                        'post_status' => 'publish',
                        'post_title'  => $quiz['title'],
                        'menu_order'  => intval( $quiz['order'] ),
                    ) );

                    if ( is_wp_error( $quiz_id ) ) {
                        return $quiz_id;
                    }

                    $this->set_course_for_step( $quiz_id, $course_id, $lesson_id );
                }
            }
        }

        if ( ! empty( $course['final_exam'] ) ) {
            if ( $dry_run ) {
                $logger->log( 'Simulación: se crearía examen final', array( 'course' => $course['title'] ) );
            } else {
                $quiz_id = wp_insert_post( array(
                    'post_type'   => 'sfwd-quiz',
                    'post_status' => 'publish',
                    'post_title'  => __( 'Examen final', 'co360-ldnlmcp' ),
                ) );

                if ( is_wp_error( $quiz_id ) ) {
                    return $quiz_id;
                }

                $this->set_course_for_step( $quiz_id, $course_id );
            }
        }

        $logger->log( 'Plan completado', array( 'course_id' => $course_id, 'dry_run' => $dry_run ) );
        return true;
    }

    /**
     * Link a step to a course using LearnDash helper when available, falling back to post_parent/meta.
     *
     * @param int $step_id   Step post ID.
     * @param int $course_id Course post ID.
     */
    private function set_course_for_step( $step_id, $course_id, $parent_id = 0 ) {
        if ( function_exists( 'ld_update_course_step' ) ) {
            ld_update_course_step( $course_id, $step_id, $parent_id );
            return;
        }

        if ( function_exists( 'learndash_set_course_for_step' ) ) {
            learndash_set_course_for_step( $step_id, $course_id );
        }

        // Fallback: ensure hierarchical link and course meta for older LD versions.
        wp_update_post( array(
            'ID'          => $step_id,
            'post_parent' => $parent_id ? $parent_id : $course_id,
        ) );

        update_post_meta( $step_id, 'course_id', $course_id );

        if ( $parent_id ) {
            update_post_meta( $step_id, 'lesson_id', $parent_id );
        }
    }

    /**
     * Apply content block to a topic.
     *
     * @param int   $topic_id      Topic ID (0 in dry-run).
     * @param array $content_block Content block definition.
     * @param bool  $dry_run       Simulation flag.
     * @param string $topic_title  Topic title for logs.
     *
     * @return true|WP_Error
     */
    private function apply_content_block( $topic_id, $content_block, $dry_run, $topic_title ) {
        $logger = new CO360_LDNLMCP_MCP_Logger();
        $context = array(
            'stage'        => 'content_block',
            'topic'        => $topic_title,
            'template_id'  => $content_block['template_id'],
            'acf_fields'   => isset( $content_block['acf_fields'] ) ? array_keys( $content_block['acf_fields'] ) : array(),
            'gravity_form' => isset( $content_block['gravity_form_id'] ) ? $content_block['gravity_form_id'] : null,
        );

        if ( $dry_run ) {
            $logger->log( 'Simulación: se aplicaría content_block', array_merge( $context, array( 'status' => 'simulated' ) ) );
            return true;
        }

        if ( ! class_exists( '\Elementor\Plugin' ) ) {
            return new WP_Error( 'co360_ldnlmcp_elementor_missing', __( 'Elementor no está activo para renderizar el template.', 'co360-ldnlmcp' ) );
        }

        $content = '';
        $template_content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( intval( $content_block['template_id'] ), true );
        if ( $template_content ) {
            $content .= $template_content;
        }

        if ( ! empty( $content_block['gravity_form_id'] ) ) {
            $content .= '\n' . do_shortcode( '[gravityform id="' . intval( $content_block['gravity_form_id'] ) . '" title="false" description="false"]' );
        }

        if ( ! empty( $content_block['acf_fields'] ) ) {
            foreach ( $content_block['acf_fields'] as $field_key => $value ) {
                if ( function_exists( 'update_field' ) ) {
                    update_field( $field_key, $value, $topic_id );
                } else {
                    update_post_meta( $topic_id, $field_key, $value );
                }
            }
        }

        if ( ! empty( $content ) ) {
            wp_update_post( array(
                'ID'           => $topic_id,
                'post_content' => $content,
            ) );
        }

        $logger->log( 'Content block aplicado', array_merge( $context, array( 'status' => 'applied' ) ) );
        return true;
    }
}
