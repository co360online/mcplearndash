<?php
/**
 * Library of prompts tuned for MCP generation.
 */
class CO360_LDMCP_Prompt_Library {
    /**
     * Get base system prompt.
     *
     * @return string
     */
    public function get_system_prompt(): string {
        return 'Actúa como generador MCP neutral y clínicamente preciso. Devuelve únicamente JSON válido según LearnDashCourseMCP v1.0.';
    }

    /**
     * Prompt for generic medical course.
     *
     * @return string
     */
    public function generate_medical_course(): string {
        return 'Genera un curso acreditado médico con objetivos claros, duración estimada, créditos y estructura jerárquica.';
    }

    /**
     * Prompt for course outline.
     *
     * @return string
     */
    public function generate_course_outline(): string {
        return 'Propón esquema de lecciones y topics cumpliendo reglas pedagógicas y de fechas en formato YYYY-MM-DD 00:00:00.';
    }

    /**
     * Prompt for clinical cases.
     *
     * @return string
     */
    public function generate_clinical_cases_course(): string {
        return 'Diseña curso de casos clínicos interactivos sin afirmaciones promocionales y con neutralidad clínica.';
    }

    /**
     * Prompt for webinar course.
     *
     * @return string
     */
    public function generate_webinar_course(): string {
        return 'Estructura webinar médico con bloque editorial y bloque pedagógico según contrato MCP.';
    }
}
