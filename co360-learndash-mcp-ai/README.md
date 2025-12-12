# CO360 – LearnDash MCP AI Engine

Plugin de producción para definir y ejecutar Model Context Protocols (MCP) aplicados a LearnDash, con generación asistida por OpenAI y control de reglas pedagógicas.

## Características
- Registro de MCPs y versionado (LearnDashCourseMCP v1.0 incluido).
- Validación estricta del contrato MCP y reglas pedagógicas (topics requieren lesson, quizzes con scope, campos médicos obligatorios).
- Generación de payload MCP usando OpenAI Responses API con `json_schema` y prompts especializados.
- Motor de ejecución que resuelve MCP → acciones LearnDash (cursos, lecciones, topics, cuestionarios) con hooks `co360_mcp_before_execute` y `co360_mcp_after_execute`.
- Plantillas MCP reutilizables y librería de prompts orientada a cursos médicos.
- Logs estructurados de cada ejecución para auditoría.

## Estructura del plugin
Consulte los archivos bajo `includes/` para la separación de responsabilidades (core, engine, ai, admin, utils, logs).

## Uso rápido
1. Active el plugin en WordPress y configure su API Key de OpenAI en **CO360 MCP → Ajustes IA**.
2. Genere un MCP desde **CO360 MCP → Generar MCP** usando un briefing y tipo de curso.
3. Revise/edite el JSON generado y ejecútelo en **CO360 MCP → Ejecutar MCP**. Puede activar `dry_run` para pruebas.
4. Consulte logs en la opción de ajustes o mediante `get_option( 'co360_ldmcp_logs' )`.

## Extensión futura
- Añadir nuevos MCPs al registro (p. ej. `LearnDashQuizMCP`, `CertificateMCP`).
- Habilitar endpoints REST o formularios frontend para ingesta de payloads.
- Integrar modelos adicionales cargados dinámicamente desde OpenAI.
