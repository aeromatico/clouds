# API Scanner - Guía de Uso

## Mejoras Implementadas (Octubre 2025)

### ✅ Funcionalidades Mejoradas

1. **Búsqueda Mejorada**
   - Búsqueda funciona con Enter key
   - Loading indicator durante búsqueda
   - Scroll automático a resultados
   - Límite aumentado a 50 resultados

2. **Visualización de Resultados**
   - Tabla más clara con anchos optimizados
   - Descripciones más largas (150 caracteres)
   - Información de confirmación más detallada
   - Indicadores visuales claros

3. **Proceso de Importación**
   - Ver primero los resultados de búsqueda
   - Revisar cada API antes de importar
   - Importar selectivamente con botones individuales
   - Confirmación clara de lo que se va a importar

## Cómo Usar el Scanner

### Método 1: Búsqueda por Término

1. Ve a **Backend → API Hub → Scanner**
2. En el tab "APIs.guru (Free)"
3. Escribe un término de búsqueda (ej: "github", "stripe", "openai")
4. Presiona **Search** o Enter
5. Revisa los resultados en la tabla
6. Click **Import** en cada API que quieras agregar
7. Confirma la importación en el diálogo

### Método 2: Importación Rápida por Categoría

1. Click en los botones de categoría:
   - **Financial** - APIs financieras
   - **Social** - APIs de redes sociales
   - **Cloud** - APIs de cloud computing
2. Se importarán automáticamente los 5 más populares de esa categoría

### Método 3: Búsqueda en RapidAPI (via Apify)

1. Ve al tab "RapidAPI via Apify"
2. Ingresa término de búsqueda
3. Se procesará en background (30-60 segundos)
4. Revisa resultados en la lista de APIs

### Método 4: Entrada Manual

1. Ve al tab "Manual Entry"
2. Completa el formulario con los datos de tu API
3. Click **Create API**
4. Configura endpoints manualmente

## Tips

- **Búsqueda efectiva:** Usa términos específicos como nombres de servicios
- **Categorías populares:** financial, social, cloud, payment, messaging, ai, maps
- **Importación en background:** Los imports se procesan en queue, revisa logs si hay problemas
- **Duplicados:** El sistema detecta y avisa si una API ya existe

## Troubleshooting

### La búsqueda no retorna resultados
- Verifica la conexión a APIs.guru (debe mostrar "✅ Connected")
- Prueba con términos más genéricos
- Limpia cache: `php artisan cache:clear`

### Import falla
- Revisa logs: `storage/logs/system.log`
- Verifica que la queue esté corriendo
- Algunos specs OpenAPI muy grandes pueden fallar

### Resultados no se muestran
- Limpia cache del navegador
- Verifica que JavaScript esté habilitado
- Revisa consola del navegador por errores

## APIs Recomendadas para Probar

- **github** - GitHub API
- **stripe** - Stripe Payment API
- **openai** - OpenAI API
- **twitter** - Twitter/X API
- **google** - Google APIs (varios)
- **aws** - Amazon Web Services APIs
- **azure** - Microsoft Azure APIs
- **slack** - Slack API

## Notas Técnicas

- **Source:** APIs.guru mantiene ~2500+ APIs con especificaciones OpenAPI
- **Cache:** Resultados se cachean por 24 horas en Redis
- **Queue:** Imports se procesan en background via Laravel Queues
- **Parser:** OpenAPI 3.x y Swagger 2.0 son soportados
