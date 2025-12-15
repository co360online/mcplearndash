<?php
/**
 * Adds topic-level content binding metabox for Elementor/ACF/Gravity Forms.
 */
class CO360_LDNLMCP_Topic_Content_Metabox {

    /**
     * Query arg used to surface save notices.
     *
     * @var string
     */
    private $notice_query_arg = 'co360_cb_notice';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
        add_action( 'save_post_sfwd-topic', array( $this, 'save_metabox' ) );
        add_action( 'admin_notices', array( $this, 'render_notices' ) );
    }

    /**
     * Register metabox for LearnDash topics.
     */
    public function register_metabox() {
        add_meta_box(
            'co360_topic_content',
            __( 'Contenido del Tema (CO360)', 'co360-ldnlmcp' ),
            array( $this, 'render_metabox' ),
            'sfwd-topic',
            'normal',
            'high'
        );
    }

    /**
     * Render the metabox UI.
     *
     * @param WP_Post $post Current post.
     */
    public function render_metabox( $post ) {
        wp_nonce_field( 'co360_topic_content', 'co360_topic_content_nonce' );

        $stored = get_post_meta( $post->ID, '_co360_content_binding', true );
        $enabled = is_array( $stored ) && ! empty( $stored['enabled'] );
        $template_id = is_array( $stored ) && isset( $stored['template_id'] ) ? intval( $stored['template_id'] ) : 0;
        $acf_values = is_array( $stored ) && isset( $stored['acf_fields'] ) && is_array( $stored['acf_fields'] ) ? $stored['acf_fields'] : array();
        $gravity_form_id = is_array( $stored ) && ! empty( $stored['gravity_form_id'] ) ? intval( $stored['gravity_form_id'] ) : 0;

        $elementor_scanner = new CO360_LDNLMCP_Elementor_Scanner();
        $templates         = $elementor_scanner->list_templates();
        $acf_scanner       = new CO360_LDNLMCP_ACF_Scanner();
        $gf_scanner        = new CO360_LDNLMCP_GravityForms_Scanner();

        $templates_fields = array();
        foreach ( $templates as $template ) {
            $templates_fields[ $template['id'] ] = $acf_scanner->get_fields_for_template( $template['id'] );
        }

        $has_elementor = post_type_exists( 'elementor_library' );
        $has_gf        = class_exists( 'GFAPI' );

        echo '<p><strong>' . esc_html__( 'Cómo usar este bloque', 'co360-ldnlmcp' ) . '</strong><br />';
        echo esc_html__( 'Crea primero el curso y sus temas con la IA.', 'co360-ldnlmcp' ) . '<br />';
        echo esc_html__( 'Entra en cada Tema y configura aquí su contenido.', 'co360-ldnlmcp' ) . '<br />';
        echo esc_html__( 'Guarda el Tema para aplicar los cambios.', 'co360-ldnlmcp' ) . '<br />';
        echo esc_html__( '⚠️ La IA NO gestiona estos contenidos.', 'co360-ldnlmcp' ) . '</p>';

        if ( ! $has_elementor ) {
            echo '<div class="notice notice-warning"><p>' . esc_html__( 'Elementor no está activo. Actívalo para usar templates en el contenido del tema.', 'co360-ldnlmcp' ) . '</p></div>';
        }

        echo '<p><label><input type="checkbox" id="co360_content_binding_enabled" name="co360_content_binding_enabled" value="1" ' . checked( $enabled, true, false ) . ' /> ' . esc_html__( 'Usar contenido avanzado (Elementor / ACF / Gravity Forms)', 'co360-ldnlmcp' ) . '</label></p>';

        echo '<div id="co360_content_binding_panel"' . ( $enabled ? '' : ' style="display:none;"' ) . '>';
        echo '<p><label><strong>' . esc_html__( 'Tipo de contenido', 'co360-ldnlmcp' ) . '</strong></label><br />';
        echo '<label><input type="radio" name="co360_content_binding_type" value="elementor_template" checked="checked" /> ' . esc_html__( 'Template Elementor', 'co360-ldnlmcp' ) . '</label></p>';

        echo '<p><label for="co360_content_template"><strong>' . esc_html__( 'Template de Elementor', 'co360-ldnlmcp' ) . '</strong></label><br />';
        echo '<select id="co360_content_template" name="co360_content_binding_template">';
        echo '<option value="">' . esc_html__( 'Selecciona un template', 'co360-ldnlmcp' ) . '</option>';
        foreach ( $templates as $template ) {
            echo '<option value="' . esc_attr( $template['id'] ) . '"' . selected( $template_id, $template['id'], false ) . '>' . esc_html( $template['title'] . ' (ID ' . $template['id'] . ')' ) . '</option>';
        }
        echo '</select>';
        if ( empty( $templates ) ) {
            echo '<p class="description">' . esc_html__( 'Publica al menos un template de Elementor para vincularlo.', 'co360-ldnlmcp' ) . '</p>';
        }
        echo '</p>';

        echo '<div id="co360_acf_fields"></div>';

        if ( $has_gf ) {
            $forms = $gf_scanner->list_forms();
            echo '<p><label><input type="checkbox" id="co360_content_binding_form_enabled" name="co360_content_binding_form_enabled" value="1"' . checked( $gravity_form_id > 0, true, false ) . ' /> ' . esc_html__( 'Incluir formulario Gravity Forms', 'co360-ldnlmcp' ) . '</label></p>';
            echo '<p><select id="co360_content_binding_form" name="co360_content_binding_form">';
            echo '<option value="0">' . esc_html__( 'Ninguno', 'co360-ldnlmcp' ) . '</option>';
            foreach ( $forms as $form ) {
                echo '<option value="' . esc_attr( $form['id'] ) . '"' . selected( $gravity_form_id, $form['id'], false ) . '>' . esc_html( $form['title'] . ' (ID ' . $form['id'] . ')' ) . '</option>';
            }
            echo '</select>';
            if ( empty( $forms ) ) {
                echo '<p class="description">' . esc_html__( 'No hay formularios disponibles.', 'co360-ldnlmcp' ) . '</p>';
            }
            echo '</p>';
        }

        echo '</div>'; // panel.

        ?>
        <script>
            (function($){
                const panel = $('#co360_content_binding_panel');
                const enableToggle = $('#co360_content_binding_enabled');
                const templateSelect = $('#co360_content_template');
                const acfContainer = $('#co360_acf_fields');
                const fieldMap = <?php echo wp_json_encode( $templates_fields ); ?>;
                const currentValues = <?php echo wp_json_encode( $acf_values ); ?>;

                function togglePanel() {
                    if ( enableToggle.is(':checked') ) {
                        panel.show();
                    } else {
                        panel.hide();
                    }
                }

                function sanitizeValue(val) {
                    return val === null ? '' : val;
                }

                function renderFields(templateId) {
                    acfContainer.empty();
                    if (!templateId || !fieldMap[templateId] || Object.keys(fieldMap[templateId]).length === 0) {
                        acfContainer.append('<p class="description"><?php echo esc_js( __( 'El template no tiene campos ACF asociados.', 'co360-ldnlmcp' ) ); ?></p>');
                        return;
                    }
                    const fields = fieldMap[templateId];
                    Object.keys(fields).forEach(function(key){
                        const field = fields[key];
                        let input = '<input type="text" class="regular-text" name="co360_content_binding_acf['+ key +']" value="'+ sanitizeValue(currentValues[key] || '') +'" />';
                        if (field.type === 'number') {
                            input = '<input type="number" step="any" name="co360_content_binding_acf['+ key +']" value="'+ sanitizeValue(currentValues[key] || '') +'" />';
                        }
                        if (field.type === 'file') {
                            input = '<input type="number" class="small-text" name="co360_content_binding_acf['+ key +']" value="'+ sanitizeValue(currentValues[key] || '') +'" /> <span class="description"><?php echo esc_js( __( 'ID de adjunto (PDF, vídeo, etc.)', 'co360-ldnlmcp' ) ); ?></span>';
                        }
                        acfContainer.append('<p><label><strong>'+ field.label +' ('+ key +')</strong></label><br />'+ input +'</p>');
                    });
                }

                enableToggle.on('change', togglePanel);
                templateSelect.on('change', function(){
                    renderFields($(this).val());
                });

                togglePanel();
                renderFields(templateSelect.val());
            })(jQuery);
        </script>
        <?php
    }

    /**
     * Save metabox data and apply content binding.
     *
     * @param int $post_id Post ID.
     */
    public function save_metabox( $post_id ) {
        if ( ! isset( $_POST['co360_topic_content_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['co360_topic_content_nonce'] ) ), 'co360_topic_content' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $enabled = isset( $_POST['co360_content_binding_enabled'] );
        if ( ! $enabled ) {
            delete_post_meta( $post_id, '_co360_content_binding' );
            return;
        }

        $template_id = isset( $_POST['co360_content_binding_template'] ) ? absint( $_POST['co360_content_binding_template'] ) : 0;
        $acf_raw     = isset( $_POST['co360_content_binding_acf'] ) && is_array( $_POST['co360_content_binding_acf'] ) ? $_POST['co360_content_binding_acf'] : array();
        $acf_scanner = new CO360_LDNLMCP_ACF_Scanner();
        $acf_meta    = $acf_scanner->get_fields_for_template( $template_id );
        $acf_values  = array();

        foreach ( $acf_meta as $key => $field ) {
            $value = isset( $acf_raw[ $key ] ) ? $acf_raw[ $key ] : '';
            if ( is_array( $value ) ) {
                $value = '';
            }
            $acf_values[ $key ] = $this->sanitize_acf_value( $value, isset( $field['type'] ) ? $field['type'] : 'text' );
        }

        $include_form    = isset( $_POST['co360_content_binding_form_enabled'] );
        $gravity_form_id = $include_form && isset( $_POST['co360_content_binding_form'] ) ? absint( $_POST['co360_content_binding_form'] ) : 0;

        $content_block = array(
            'type'            => 'elementor_template',
            'template_id'     => $template_id,
            'acf_fields'      => $acf_values,
            'gravity_form_id' => $include_form && $gravity_form_id ? $gravity_form_id : null,
        );

        $resolver = new CO360_LDNLMCP_Content_Block_Resolver();
        $validated = $resolver->validate_and_enrich( $content_block );
        if ( is_wp_error( $validated ) ) {
            $this->add_notice( $validated->get_error_message(), 'error' );
            delete_post_meta( $post_id, '_co360_content_binding' );
            return;
        }

        $meta = array(
            'enabled'         => true,
            'content_type'    => 'elementor_template',
            'template_id'     => intval( $template_id ),
            'acf_fields'      => $acf_values,
            'gravity_form_id' => $include_form ? $gravity_form_id : 0,
        );

        update_post_meta( $post_id, '_co360_content_binding', $meta );

        $apply = $this->apply_binding_to_topic( $post_id, $meta, $validated );
        if ( is_wp_error( $apply ) ) {
            $this->add_notice( $apply->get_error_message(), 'error' );
            return;
        }

        $this->add_notice( __( 'Contenido avanzado aplicado al tema.', 'co360-ldnlmcp' ), 'updated' );
    }

    /**
     * Apply content binding to topic content and fields.
     *
     * @param int   $post_id   Topic ID.
     * @param array $meta      Stored meta array.
     * @param array $validated Validated content block with template metadata.
     * @return true|WP_Error
     */
    private function apply_binding_to_topic( $post_id, $meta, $validated ) {
        $content_parts = array();

        $content_parts[] = '[elementor-template id="' . intval( $meta['template_id'] ) . '"]';

        if ( ! empty( $meta['gravity_form_id'] ) ) {
            $content_parts[] = '[gravityform id="' . intval( $meta['gravity_form_id'] ) . '" title="false" description="false"]';
        }

        if ( ! empty( $meta['acf_fields'] ) ) {
            foreach ( $meta['acf_fields'] as $field_key => $value ) {
                if ( function_exists( 'update_field' ) ) {
                    update_field( $field_key, $value, $post_id );
                } else {
                    update_post_meta( $post_id, $field_key, $value );
                }
            }
        }

        $content = implode( "\n", $content_parts );
        $update = wp_update_post(
            array(
                'ID'           => $post_id,
                'post_content' => $content,
            ),
            true
        );

        if ( is_wp_error( $update ) ) {
            return $update;
        }

        ( new CO360_LDNLMCP_MCP_Logger() )->log(
            'Content Binding aplicado en Topic',
            array(
                'topic_id'      => $post_id,
                'template_id'   => $meta['template_id'],
                'acf_fields'    => array_keys( $meta['acf_fields'] ),
                'gravity_form'  => ! empty( $meta['gravity_form_id'] ) ? $meta['gravity_form_id'] : null,
                'template_name' => isset( $validated['template']['title'] ) ? $validated['template']['title'] : '',
            )
        );

        return true;
    }

    /**
     * Render admin notices after save.
     */
    public function render_notices() {
        if ( ! isset( $_GET[ $this->notice_query_arg ] ) ) {
            return;
        }
        $message = rawurldecode( sanitize_text_field( wp_unslash( $_GET[ $this->notice_query_arg ] ) ) );
        $type    = isset( $_GET['co360_cb_type'] ) ? sanitize_text_field( wp_unslash( $_GET['co360_cb_type'] ) ) : 'updated';
        $class   = 'notice '; // base class.
        $class  .= 'error' === $type ? 'notice-error' : 'notice-success';
        echo '<div class="' . esc_attr( $class ) . '"><p>' . esc_html( $message ) . '</p></div>';
    }

    /**
     * Queue notice for next redirect.
     *
     * @param string $message Message text.
     * @param string $type    notice type (updated|error).
     */
    private function add_notice( $message, $type = 'updated' ) {
        add_filter(
            'redirect_post_location',
            function( $location ) use ( $message, $type ) {
                $location = add_query_arg( $this->notice_query_arg, rawurlencode( $message ), $location );
                $location = add_query_arg( 'co360_cb_type', $type, $location );
                return $location;
            }
        );
    }

    /**
     * Sanitize ACF field value based on type.
     *
     * @param string $value Raw value.
     * @param string $type  Field type.
     * @return mixed
     */
    private function sanitize_acf_value( $value, $type ) {
        switch ( $type ) {
            case 'number':
                return is_numeric( $value ) ? $value + 0 : 0;
            case 'file':
            case 'image':
                return absint( $value );
            default:
                return sanitize_text_field( $value );
        }
    }
}
