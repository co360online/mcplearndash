<?php
/**
 * Main UI to build a course from natural language.
 */
class CO360_LDNLMCP_NL_Course_Page {

    /**
     * Render page.
     */
    public function render() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $preview_mcp   = null;
        $preview_tree  = null;
        $preview_array = array();
        $errors        = array();
        $success       = '';

        $description = isset( $_POST['co360_ldnlmcp_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['co360_ldnlmcp_description'] ) ) : '';
        $course_type = isset( $_POST['co360_ldnlmcp_course_type'] ) ? sanitize_text_field( wp_unslash( $_POST['co360_ldnlmcp_course_type'] ) ) : '';
        $level       = isset( $_POST['co360_ldnlmcp_level'] ) ? sanitize_text_field( wp_unslash( $_POST['co360_ldnlmcp_level'] ) ) : '';
        $language    = isset( $_POST['co360_ldnlmcp_language'] ) ? sanitize_text_field( wp_unslash( $_POST['co360_ldnlmcp_language'] ) ) : '';
        $dry_run     = isset( $_POST['co360_ldnlmcp_dry_run'] ) ? (bool) $_POST['co360_ldnlmcp_dry_run'] : false;

        $encoded_preview = isset( $_POST['co360_ldnlmcp_preview_payload'] ) ? sanitize_text_field( wp_unslash( $_POST['co360_ldnlmcp_preview_payload'] ) ) : '';
        if ( ! empty( $encoded_preview ) ) {
            $decoded_preview = json_decode( base64_decode( $encoded_preview ), true );
            if ( is_array( $decoded_preview ) ) {
                $preview_array = $decoded_preview;
                $preview_mcp   = wp_json_encode( $decoded_preview, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
                $preview_tree  = ( new CO360_LDNLMCP_MCP_Definition() )->to_tree( $decoded_preview );
            }
        }

        $selected_topic    = isset( $_POST['co360_ldnlmcp_topic'] ) ? sanitize_text_field( wp_unslash( $_POST['co360_ldnlmcp_topic'] ) ) : '';
        $selected_template = isset( $_POST['co360_ldnlmcp_template'] ) ? intval( $_POST['co360_ldnlmcp_template'] ) : 0;
        $selected_form     = isset( $_POST['co360_ldnlmcp_form'] ) ? intval( $_POST['co360_ldnlmcp_form'] ) : 0;

        $elementor_scanner = new CO360_LDNLMCP_Elementor_Scanner();
        $elementor_templates = $elementor_scanner->list_templates();
        $gf_scanner = new CO360_LDNLMCP_GravityForms_Scanner();
        $gf_forms = $gf_scanner->list_forms();
        $acf_scanner = new CO360_LDNLMCP_ACF_Scanner();
        $acf_fields_for_template = $selected_template ? $acf_scanner->get_fields_for_template( $selected_template ) : array();

        if ( isset( $_POST['co360_ldnlmcp_content_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['co360_ldnlmcp_content_nonce'] ) ), 'co360_ldnlmcp_content' ) ) {
            $selected_topic    = isset( $_POST['co360_ldnlmcp_topic'] ) ? sanitize_text_field( wp_unslash( $_POST['co360_ldnlmcp_topic'] ) ) : '';
            $selected_template = isset( $_POST['co360_ldnlmcp_template'] ) ? intval( $_POST['co360_ldnlmcp_template'] ) : 0;
            $selected_form     = isset( $_POST['co360_ldnlmcp_form'] ) ? intval( $_POST['co360_ldnlmcp_form'] ) : 0;

            if ( empty( $preview_array ) ) {
                $errors[] = __( 'Genera primero la estructura MCP para poder asignar contenido.', 'co360-ldnlmcp' );
            } elseif ( empty( $selected_topic ) || false === strpos( $selected_topic, '|' ) ) {
                $errors[] = __( 'Selecciona un tema válido para asignar contenido.', 'co360-ldnlmcp' );
            } else {
                list( $module_index, $lesson_index ) = array_map( 'intval', explode( '|', $selected_topic ) );
                if ( ! isset( $preview_array['course']['modules'][ $module_index ]['lessons'][ $lesson_index ] ) ) {
                    $errors[] = __( 'El tema seleccionado no existe en el MCP actual.', 'co360-ldnlmcp' );
                } else {
                    $topic_info = $preview_array['course']['modules'][ $module_index ]['lessons'][ $lesson_index ];
                    $elementor  = new CO360_LDNLMCP_Elementor_Scanner();
                    $acf        = new CO360_LDNLMCP_ACF_Scanner();
                    $gf         = new CO360_LDNLMCP_GravityForms_Scanner();
                    $template   = $elementor->get_template( $selected_template );

                    if ( ! $template ) {
                        $errors[] = __( 'Debes elegir un template de Elementor válido.', 'co360-ldnlmcp' );
                    } else {
                        $acf_meta   = $acf->get_fields_for_template( $selected_template );
                        $acf_values = array();
                        foreach ( $acf_meta as $field_key => $field_data ) {
                            $field_value = isset( $_POST[ 'co360_ldnlmcp_acf_' . $field_key ] ) ? wp_unslash( $_POST[ 'co360_ldnlmcp_acf_' . $field_key ] ) : '';
                            $acf_values[ $field_key ] = is_array( $field_value ) ? '' : sanitize_text_field( $field_value );
                        }

                        $form = $selected_form ? $gf->get_form( $selected_form ) : null;

                        $instruction_builder = new CO360_LDNLMCP_Content_Instruction_Builder();
                        $instruction = $instruction_builder->build_instruction( $topic_info, $template, $acf_values, $acf_meta, $form );
                        $description = trim( $description . "\n\n" . $instruction );
                        $success = __( 'Instrucción añadida al prompt. Vuelve a generar la estructura MCP.', 'co360-ldnlmcp' );
                    }
                }
            }
        }

        if ( isset( $_POST['co360_ldnlmcp_generate_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['co360_ldnlmcp_generate_nonce'] ) ), 'co360_ldnlmcp_generate' ) ) {
            $intent = new CO360_LDNLMCP_NL_Intent( $description, $course_type, $level, $language );
            $validator = new CO360_LDNLMCP_NL_Validator();
            $errors = $validator->validate_intent( $intent );

            if ( empty( $errors ) ) {
                $generator = new CO360_LDNLMCP_NL_To_MCP_Generator( new CO360_LDNLMCP_OpenAI_Client(), new CO360_LDNLMCP_Prompt_Library() );
                $response  = $generator->generate( $intent );

                if ( is_wp_error( $response ) ) {
                    $errors[] = $response->get_error_message();
                } else {
                    $mcp = new CO360_LDNLMCP_MCP_Definition();
                    $validation = $mcp->validate_payload( $response );
                    if ( is_wp_error( $validation ) ) {
                        $errors[] = $validation->get_error_message();
                    } else {
                        $preview_array = $response;
                        $preview_mcp = wp_json_encode( $response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
                        $preview_tree = $mcp->to_tree( $response );
                        if ( $dry_run ) {
                            $success = __( 'Simulación lista. Nada se ha creado aún.', 'co360-ldnlmcp' );
                        }
                    }
                }
            }
        }

        if ( isset( $_POST['co360_ldnlmcp_execute_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['co360_ldnlmcp_execute_nonce'] ) ), 'co360_ldnlmcp_execute' ) ) {
            $raw_mcp = isset( $_POST['co360_ldnlmcp_raw_mcp'] ) ? wp_unslash( $_POST['co360_ldnlmcp_raw_mcp'] ) : '';
            $dry_run = isset( $_POST['co360_ldnlmcp_dry_run'] ) ? (bool) $_POST['co360_ldnlmcp_dry_run'] : false;
            $decoded = json_decode( wp_kses_post( $raw_mcp ), true );

            if ( empty( $decoded ) ) {
                $errors[] = __( 'No se pudo leer el payload MCP.', 'co360-ldnlmcp' );
            } else {
                $mcp = new CO360_LDNLMCP_MCP_Definition();
                $validation = $mcp->validate_payload( $decoded );
                if ( is_wp_error( $validation ) ) {
                    $errors[] = $validation->get_error_message();
                } else {
                    $resolver = new CO360_LDNLMCP_MCP_Resolver();
                    $plan = $resolver->build_plan( $decoded );
                    if ( is_wp_error( $plan ) ) {
                        $errors[] = $plan->get_error_message();
                    } else {
                        $engine = new CO360_LDNLMCP_Learndash_Engine();
                        $result = $engine->execute_plan( $plan, $dry_run );
                        if ( is_wp_error( $result ) ) {
                            $errors[] = $result->get_error_message();
                        } else {
                            $success = $dry_run ? __( 'Ejecución en modo simulación completada.', 'co360-ldnlmcp' ) : __( 'Curso creado en LearnDash.', 'co360-ldnlmcp' );
                        }
                    }
                    $preview_array = $decoded;
                    $preview_mcp = wp_json_encode( $decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
                    $preview_tree = $mcp->to_tree( $decoded );
                }
            }
        }

        $encoded_preview_payload = ! empty( $preview_array ) ? base64_encode( wp_json_encode( $preview_array ) ) : '';

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Crear curso con IA', 'co360-ldnlmcp' ); ?></h1>
            <?php if ( ! empty( $errors ) ) : ?>
                <div class="error"><p><?php echo wp_kses_post( implode( '<br>', $errors ) ); ?></p></div>
            <?php endif; ?>

            <?php if ( ! empty( $success ) ) : ?>
                <div class="updated"><p><?php echo esc_html( $success ); ?></p></div>
            <?php endif; ?>

            <form method="post">
                <?php wp_nonce_field( 'co360_ldnlmcp_generate', 'co360_ldnlmcp_generate_nonce' ); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="co360_ldnlmcp_description"><?php esc_html_e( 'Describe el curso', 'co360-ldnlmcp' ); ?></label></th>
                        <td><textarea name="co360_ldnlmcp_description" id="co360_ldnlmcp_description" rows="8" class="large-text" required><?php echo isset( $description ) ? esc_textarea( $description ) : ''; ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Tipo de curso', 'co360-ldnlmcp' ); ?></th>
                        <td>
                            <select name="co360_ldnlmcp_course_type">
                                <option value="">—</option>
                                <option value="acreditado" <?php selected( isset( $course_type ) ? $course_type : '', 'acreditado' ); ?>><?php esc_html_e( 'Acreditado', 'co360-ldnlmcp' ); ?></option>
                                <option value="webinar" <?php selected( isset( $course_type ) ? $course_type : '', 'webinar' ); ?>><?php esc_html_e( 'Webinar', 'co360-ldnlmcp' ); ?></option>
                                <option value="casos" <?php selected( isset( $course_type ) ? $course_type : '', 'casos' ); ?>><?php esc_html_e( 'Casos clínicos', 'co360-ldnlmcp' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Nivel', 'co360-ldnlmcp' ); ?></th>
                        <td>
                            <select name="co360_ldnlmcp_level">
                                <option value="">—</option>
                                <option value="basico" <?php selected( isset( $level ) ? $level : '', 'basico' ); ?>><?php esc_html_e( 'Básico', 'co360-ldnlmcp' ); ?></option>
                                <option value="intermedio" <?php selected( isset( $level ) ? $level : '', 'intermedio' ); ?>><?php esc_html_e( 'Intermedio', 'co360-ldnlmcp' ); ?></option>
                                <option value="avanzado" <?php selected( isset( $level ) ? $level : '', 'avanzado' ); ?>><?php esc_html_e( 'Avanzado', 'co360-ldnlmcp' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="co360_ldnlmcp_language"><?php esc_html_e( 'Idioma', 'co360-ldnlmcp' ); ?></label></th>
                        <td><input type="text" name="co360_ldnlmcp_language" id="co360_ldnlmcp_language" value="<?php echo isset( $language ) ? esc_attr( $language ) : ''; ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Simular (dry-run)', 'co360-ldnlmcp' ); ?></th>
                        <td><label><input type="checkbox" name="co360_ldnlmcp_dry_run" value="1" <?php checked( isset( $dry_run ) ? $dry_run : false ); ?> /> <?php esc_html_e( 'Solo mostrar, no crear', 'co360-ldnlmcp' ); ?></label></td>
                    </tr>
                </table>
                <?php submit_button( __( 'Generar estructura', 'co360-ldnlmcp' ) ); ?>
            </form>

            <?php if ( $preview_mcp ) : ?>
                <h2><?php esc_html_e( 'Vista previa MCP', 'co360-ldnlmcp' ); ?></h2>
                <form method="post">
                    <?php wp_nonce_field( 'co360_ldnlmcp_execute', 'co360_ldnlmcp_execute_nonce' ); ?>
                    <textarea readonly rows="15" class="large-text code" name="co360_ldnlmcp_raw_mcp"><?php echo esc_textarea( $preview_mcp ); ?></textarea>
                    <p><?php esc_html_e( 'El MCP gobierna la ejecución. La IA no crea directamente.', 'co360-ldnlmcp' ); ?></p>
                    <input type="hidden" name="co360_ldnlmcp_dry_run" value="<?php echo isset( $dry_run ) && $dry_run ? '1' : '0'; ?>" />
                    <input type="hidden" name="co360_ldnlmcp_preview_payload" value="<?php echo esc_attr( $encoded_preview_payload ); ?>" />
                    <?php submit_button( __( 'Crear curso en LearnDash', 'co360-ldnlmcp' ) ); ?>
                </form>
            <?php endif; ?>

            <?php if ( $preview_mcp && ! empty( $preview_array['course']['modules'] ) ) : ?>
                <h2><?php esc_html_e( 'Contenido por tema', 'co360-ldnlmcp' ); ?></h2>
                <p><?php esc_html_e( 'Selecciona el tema, el template de Elementor, asigna campos ACF reales y (opcionalmente) un formulario de Gravity Forms. El sistema generará una instrucción de lenguaje controlado y la añadirá al prompt.', 'co360-ldnlmcp' ); ?></p>
                <form method="post">
                    <?php wp_nonce_field( 'co360_ldnlmcp_content', 'co360_ldnlmcp_content_nonce' ); ?>
                    <input type="hidden" name="co360_ldnlmcp_description" value="<?php echo esc_attr( $description ); ?>" />
                    <input type="hidden" name="co360_ldnlmcp_course_type" value="<?php echo esc_attr( $course_type ); ?>" />
                    <input type="hidden" name="co360_ldnlmcp_level" value="<?php echo esc_attr( $level ); ?>" />
                    <input type="hidden" name="co360_ldnlmcp_language" value="<?php echo esc_attr( $language ); ?>" />
                    <input type="hidden" name="co360_ldnlmcp_dry_run" value="<?php echo $dry_run ? '1' : '0'; ?>" />
                    <input type="hidden" name="co360_ldnlmcp_preview_payload" value="<?php echo esc_attr( $encoded_preview_payload ); ?>" />
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Tema', 'co360-ldnlmcp' ); ?></th>
                            <td>
                                <select name="co360_ldnlmcp_topic" required>
                                    <option value=""><?php esc_html_e( 'Selecciona un tema', 'co360-ldnlmcp' ); ?></option>
                                    <?php foreach ( $preview_array['course']['modules'] as $module_index => $module ) : ?>
                                        <?php if ( empty( $module['lessons'] ) ) { continue; } ?>
                                        <?php foreach ( $module['lessons'] as $lesson_index => $lesson ) :
                                            $value = $module_index . '|' . $lesson_index;
                                            $label = sprintf( 'Módulo %d → Tema %d: %s', $module_index + 1, $lesson_index + 1, $lesson['title'] );
                                            ?>
                                            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $selected_topic, $value ); ?>><?php echo esc_html( $label ); ?></option>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Template de Elementor', 'co360-ldnlmcp' ); ?></th>
                            <td>
                                <select name="co360_ldnlmcp_template" required>
                                    <option value=""><?php esc_html_e( 'Selecciona template', 'co360-ldnlmcp' ); ?></option>
                                    <?php foreach ( $elementor_templates as $template ) : ?>
                                        <option value="<?php echo esc_attr( $template['id'] ); ?>" <?php selected( $selected_template, $template['id'] ); ?>><?php echo esc_html( $template['title'] . ' (ID ' . $template['id'] . ')' ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ( empty( $elementor_templates ) ) : ?>
                                    <p class="description"><?php esc_html_e( 'No se encontraron templates de Elementor publicados.', 'co360-ldnlmcp' ); ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ( ! empty( $acf_fields_for_template ) ) : ?>
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Campos ACF del template', 'co360-ldnlmcp' ); ?></th>
                                <td>
                                    <?php foreach ( $acf_fields_for_template as $field_key => $field ) : ?>
                                        <p>
                                            <label for="co360_ldnlmcp_acf_<?php echo esc_attr( $field_key ); ?>"><?php echo esc_html( $field['label'] . ' (' . $field_key . ')' ); ?></label><br />
                                            <input type="text" id="co360_ldnlmcp_acf_<?php echo esc_attr( $field_key ); ?>" name="co360_ldnlmcp_acf_<?php echo esc_attr( $field_key ); ?>" value="<?php echo isset( $_POST[ 'co360_ldnlmcp_acf_' . $field_key ] ) ? esc_attr( wp_unslash( $_POST[ 'co360_ldnlmcp_acf_' . $field_key ] ) ) : ''; ?>" class="regular-text" />
                                        </p>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Formulario Gravity Forms (opcional)', 'co360-ldnlmcp' ); ?></th>
                            <td>
                                <select name="co360_ldnlmcp_form">
                                    <option value="0"><?php esc_html_e( 'Ninguno', 'co360-ldnlmcp' ); ?></option>
                                    <?php foreach ( $gf_forms as $form ) : ?>
                                        <option value="<?php echo esc_attr( $form['id'] ); ?>" <?php selected( $selected_form, $form['id'] ); ?>><?php echo esc_html( $form['title'] . ' (ID ' . $form['id'] . ')' ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ( empty( $gf_forms ) ) : ?>
                                    <p class="description"><?php esc_html_e( 'No se encontraron formularios de Gravity Forms.', 'co360-ldnlmcp' ); ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button( __( 'Generar instrucción para el MCP', 'co360-ldnlmcp' ), 'secondary' ); ?>
                </form>
            <?php endif; ?>

            <?php if ( $preview_tree ) : ?>
                <h2><?php esc_html_e( 'Árbol del curso', 'co360-ldnlmcp' ); ?></h2>
                <ul>
                    <?php foreach ( $preview_tree as $course ) : ?>
                        <li><strong><?php echo esc_html( $course['title'] ); ?></strong> (<?php echo esc_html( $course['credits'] ); ?> <?php esc_html_e( 'créditos', 'co360-ldnlmcp' ); ?>)
                            <?php if ( ! empty( $course['modules'] ) ) : ?>
                                <ul>
                                    <?php foreach ( $course['modules'] as $module ) : ?>
                                        <li><?php echo esc_html( $module['title'] ); ?>
                                            <?php if ( ! empty( $module['lessons'] ) ) : ?>
                                                <ul>
                                                    <?php foreach ( $module['lessons'] as $lesson ) : ?>
                                                        <li><?php echo esc_html( $lesson['title'] ); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                            <?php if ( ! empty( $module['quizzes'] ) ) : ?>
                                                <ul>
                                                    <?php foreach ( $module['quizzes'] as $quiz ) : ?>
                                                        <li><?php echo esc_html( $quiz['title'] ); ?> (<?php esc_html_e( 'Test', 'co360-ldnlmcp' ); ?>)</li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            <?php if ( ! empty( $course['final_exam'] ) ) : ?>
                                <p><?php esc_html_e( 'Examen final incluido.', 'co360-ldnlmcp' ); ?></p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
    }
}
