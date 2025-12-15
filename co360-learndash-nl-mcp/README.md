# CO360 – LearnDash Natural Language Course Builder (MCP + AI)

Este plugin convierte lenguaje natural controlado en cursos LearnDash utilizando OpenAI pero **siempre gobernado por un Modelo de Contexto (MCP)**. La IA interpreta, el MCP valida y WordPress ejecuta.

## Instalación

1. Copia la carpeta `co360-learndash-nl-mcp` en `wp-content/plugins/`.
2. Activa el plugin desde el panel de WordPress.
3. Asegúrate de tener LearnDash instalado si deseas crear cursos reales. Sin LearnDash solo podrás simular.

## ¿Qué es el MCP?

El Model Context Protocol define el contrato entre la intención humana y la ejecución técnica. En este plugin el esquema `LearnDashCourseMCP v1.1` exige:

- Versión obligatoria.
- Curso con créditos > 0.
- Módulos anidados dentro del curso.
- Temas y tests anidados dentro de módulos.
- Opción de examen final.
- (v1.1) La estructura sigue siendo curso → módulos → temas → tests → examen final. El contenido avanzado (Elementor/ACF/Gravity Forms) se gestiona con un **Content Binding** interno separado del schema MCP.

Si faltan créditos, módulos o relaciones jerárquicas, el MCP rechaza la petición.

## Flujo completo

1. Ve a **CO360 → Crear curso con IA**.
2. Escribe una descripción en lenguaje natural controlado (no es chat libre).
3. Opcional: selecciona tipo, nivel, idioma y activa "Simular" para no crear nada.
4. Pulsa **Generar estructura**. La IA recibe un prompt fijo y devuelve **solo JSON MCP** usando structured outputs (sin contenido avanzado).
5. El MCP valida el payload y muestra la vista previa (árbol del curso).
6. Si quieres añadir contenido real a un tema, usa la sección **Contenido opcional por tema**: genera un Content Binding interno que no modifica el MCP ni llama a la IA.
7. Pulsa **Crear curso en LearnDash** para ejecutar el plan. Si está en dry-run, solo verás el plan. Si hay Content Binding, se aplicará durante la ejecución.

## Ejemplos de frases soportadas

- "Curso acreditado de 5 créditos para médicos"
- "3 módulos"
- "el segundo módulo tiene 2 temas"
- "módulo 3 sin temas"
- "test en el módulo 2"
- "examen final"
- "nivel avanzado"

Combina varias frases en el mismo texto. El parser interno extrae señales y las envía al modelo junto con tus metadatos.

## Ajustes de IA

En **CO360 → Ajustes IA** configura:

- API Key (oculta)
- Modelo (ej. `gpt-4o-mini`)
- Temperature
- Max tokens
- Botón de guardar

La llamada usa la API de Responses con `text.format.type = json_schema`, `strict = true` y el schema de `LearnDashCourseMCP v1.1`.

## Dry-run

Marca "Simular" para ejecutar en modo seguro. No se crea contenido, pero verás el payload MCP, el árbol previsto y los logs en el debug log de WordPress (si `WP_DEBUG_LOG` está activo).

## Seguridad

- Permisos `manage_options` en todas las pantallas.
- Nonces en los formularios de generación y ejecución.
- Sanitización de todos los datos recibidos.
- Si LearnDash no está activo, la ejecución real está bloqueada (solo simulación).

## Asignación de contenido a temas

1. Genera la estructura MCP del curso (sin necesidad de contenido adicional).
2. Si quieres contenido, en **Contenido opcional por tema (Elementor / ACF / Gravity Forms)** elige el tema (Módulo → Tema), selecciona un template de Elementor publicado, completa los campos ACF que se detecten para ese template y (si aplica) elige un formulario de Gravity Forms. La sección aparece cuando existe una vista previa MCP y se instancian los escáneres.
3. Pulsa **Guardar contenido opcional**. Se guarda un Content Binding interno (no modifica el texto ni el MCP enviado a la IA).
4. Ejecuta el MCP. El engine:
   - Inserta el template de Elementor como contenido del tema.
   - Asigna los campos ACF al post del tema.
   - Inserta el formulario Gravity Forms elegido.
   - Registra logs por cada binding aplicado y marca los temas sin contenido como `skipped_no_binding`.

### Ejemplo de binding guardado

- Tema: "Arquitecturas de modelos de IA" (Módulo 1 → Tema 1)
- Template Elementor: "Tema Vídeo + PDFs" (ID 327)
- Campos ACF: `vimeo_video_id = 123456789`, `pdf_1 = 456`, `pdf_2 = 789`
- Gravity Forms: "Evaluación Módulo 1" (ID 12)

## Principio rector

**El usuario escribe intención. La IA interpreta. El MCP manda. LearnDash ejecuta.**
