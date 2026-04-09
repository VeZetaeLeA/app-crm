# 🚀 Informe Técnico-Funcional: Módulo "Estrategia Instagram"

Este documento presenta una auditoría profunda orientada al negocio, evaluando el flujo actual bajo los pilares de **Psicología de Marca, UX/UI Estratégico y Arquitectura de Software**. El objetivo es evolucionar este módulo de un "simple generador" a un orquestador de contenido inmersivo y de alta conversión.

---

## 1. Análisis del Estado Actual

### Arquitectura Técnica
- **Código y Lógica**: El módulo sigue un patrón MVC limpio (`InstagramController` y `InstagramService`).
- **El Cuello de Botella (Mocking)**: Actualmente, la "Inteligencia Artificial" es un espejismo. El método `generatePostContent()` devuelve selecciones aleatorias de un array *hardcodeado*. El servicio no tiene conexión real con ningún LLM, lo que limita la escalabilidad.
- **Exportación Funcional pero Básica**: Se facilita una funcionalidad útil de exportación a CSV, pero el diseño de la validación está muy acoplado al HTML (recarga de páginas en lugar de asincronía).

### UX/UI y Diseño Visual
- **Glassmorphism Oscuro**: Se percibe un esfuerzo por mantener un tema sofisticado ("SaaS Premium", colores nocturnos, tipografía blanca/gris, y bordes translúcidos). 
- **Fricción Visual**: Existen elementos de contraste disonante (ej. botones que mezclan `shadow-gold` y `shadow-neon` sin un patrón semántico fijo) que fatigan la lectura y compiten jerárquicamente.
- **UX Transaccional**: La edición de post se realiza a través de un Modal de Bootstrap que rompe el contexto de la lectura del calendario completo. Las acciones de "regeneración" requieren recarga completa de la página.

---

## 2. Oportunidades de Mejora y Neuro-Marketing

### A. Psicología del Consumidor & Sesgos Cognitivos (Marketing Psychology)
- **El Efecto IKEA (Apropiación)**: Actualmente generamos los 7 días con un solo clic. Si le pedimos al usuario *un poco* de input previo (ej. "¿En qué te quieres enfocar esta semana? Venta dura / Autoridad / Comunidad"), el usuario sentirá mayor autoría y pertenencia sobre el resultado de la IA.
- **El Efecto Zeigarnik (Completitud)**: Mostrar un indicador de progreso (ej. "Calendario 70% revisado") incita de manera inconsciente a terminar de leer y pulir los textos antes de finalizar.
- **Contraste y Atractivo Visual (Neuromarketing)**: Reemplazar los textos monótonos por una paleta controlada del *Bento Grid*. Dividir visualmente la carga cognitiva: separar abrumadoramente el *Caption* del *Visual Prompt* con zonas de color diferentes.

### B. UX/UI Estratégico (UI/UX Pro Max)
- **Micro-interacciones de Delight**: En lugar de recargar la página, la acción de "Re-generar" debe ocurrir mediante un Fetch/AJAX. Durante esos 3-5 segundos, mostrar una transición tipo *Skeleton Loader con efecto escáner (shimmer)* genera expectativa y dopamina.
- **Inline Editing vs Modales**: Eliminar el aparatoso modal actual en pro de la edición "Click-to-edit". Que el usuario pueda dar doble clic sobre el texto dentro de la misma grilla y modificar el contenido y el prompt sin salir del contexto visual de la semana.

---

## 3. Propuesta de Elevación: Modelo AI y Prompts Estructurados

La mayor debilidad funcional se transforma en nuestra ventaja competitiva. Debemos conectar la lógica con un API (ej. `OpenAI` o la `AI_MODEL` interna) usando **System Prompts** expertos en Copywriting y un formato de salida JSON estricto para asegurar la consistencia.

### Arquitectura de Generación (JSON Schema)

Actualmente la base de datos espera strings fijos. Proponemos adaptar el esquema o guardar JSON strings, para poder generar contenido variable basado en el **Formato**. 

Para los formatos **Post Simple**, el output es tradicional (Internal Title, Caption, CTA, Visual Prompt).
Sin embargo, para **Carruseles y Reels** (que requieren un ritmo o gancho-retención-cierre), estructuraremos el Prompt para obligar a la IA a separar todo en "Escenas/Slides", listo para pasarse a herramientas de generación de imagen/video.

### 📜 El "Master Prompt" Estructural para la IA (System Instructions)
> **Rol:** Actúa como el Director Creativo de "VeZetaeLeA", experto en neuro-copywriting y diseño visual corporativo (Estética Cyberpunk elegante / Glassmorphism B2B).
> **Input del Usuario:** Pilar Estratégico ("Soluciones en Producción") + Formato ("Carrusel") + Contexto.
> **Instrucción:** Devuelve un JSON estructurado. Si el formato es un Post estándar, rellena el objeto `standard_post`. Si el formato exige múltiples secuencias (Carrusel, Reel), devuelve el objeto `sequence_post` donde cada nodo es una "Escena" independiente.
> **Reglas Visuales:** Los "Visual Prompts" deben ser en inglés, con descripciones altamente técnicas (ej. "Cinematic lighting, 8k resolution, modern corporate aesthetic") para alimentar directamente un motor como Midjourney o Stable Diffusion.

### Ejemplo de Estructura de Respuesta Esperada (Carrusel / Reel):
```json
{
  "tipo": "carousel",
  "internal_title": "Carrusel: Arquitectura vs Caos",
  "caption_post_general": "¿Notas que tu equipo pierde el doble de tiempo apagando incendios en lugar de programar? Desliza para entender dónde está el problema en la raíz.",
  "cta_text": "Agenda sesión",
  "hashtags": "#TechLeadership #CodeArchitecture #SoftDev",
  "scenes": [
    {
      "slide_number": 1,
      "text_overlay": "El 80% del tiempo de tu equipo se va en deuda técnica.",
      "visual_prompt": "A macro shot of tangled, glowing red cables in a dark server room. Tension, chaos."
    },
    {
      "slide_number": 2,
      "text_overlay": "Escalar sobre lodo sale caro.",
      "visual_prompt": "A minimalist, futuristic blueprint crumbling slowly. Cinematic dust particles."
    },
    {
      "slide_number": 3,
      "text_overlay": "Estructurar bien desde el día uno con VeZetaeLeA.",
      "visual_prompt": "A perfectly aligned grid of glowing blue optical fibers. Order, clarity, tech."
    }
  ]
}
```

---

## 4. Plan de Acción (Fases de Implementación)

1. **Fase 1: Backend & Motor IA 🧠**
   - Integrar un servicio de IA real (Client HTTP a la API elegida por el usuario) en `InstagramService.php`.
   - Modificar la BDD (`instagram_posts`) añadiendo una columna JSON `scenes_data` o crear una tabla relacionada `instagram_post_scenes`.
2. **Fase 2: UX/UI Refactor 🎨**
   - Limpiar el CSS: Estandarizar la paleta Glassmorphism y mejorar el contraste (Tipografía legible de Inter/Sora).
   - Sustituir el renderizado servidor de Modales por peticiones Asíncronas (AJAX).
3. **Fase 3: Visualización de Secuencias 🎞️**
   - Modificar la vista `view.php` para que, cuando el post sea Reel o Carrusel, muestre una "Línea de Tiempo" o "Grid de Slides" con los visual prompts separados para una fácil extracción.

> [!IMPORTANT]
> ## User Review Required
> Para proceder con éxito, confirma las siguientes definiciones de negocio:
> 
> 1. **Motor de IA:** ¿A qué API nos vamos a conectar para habilitar la generación dinámica? (Ej. OpenAI / Anthropic / El servidor de inferencia local del ecosistema).
> 2. **Cambios de Base de Datos:** ¿Me autorizas a crear la migración (crear la tabla `instagram_post_scenes` o añadir un campo JSON) para dar soporte al parseo estructurado de los Reels y Carruseles?
> 3. **Prioridad Visual:** ¿Deseas que remueva totalmente la recarga y pase la edición/regeneración a un formato 100% interactivo (Micro-interacciones In-Line + Esqueletos de carga) para mejorar dramáticamente la percepción *Premium*?
