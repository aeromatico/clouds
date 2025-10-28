# Quick Start: Multi-Source API Import

## 🚀 Setup (1 minuto)

### 1. Configurar Apify Token

**Backend → Settings → Api Hub Settings → Apify**

```
Apify API Token: apify_api_YOUR_TOKEN_HERE
```

Click **Save** (se encriptará automáticamente)

### 2. Iniciar Queue Worker

```bash
# En terminal (mantener corriendo)
cd /home/clouds/htdocs/clouds.com.bo
php artisan queue:work redis --queue=default --tries=3 --timeout=300
```

## 📥 Usar el Import

**Backend → Api Hub → Import**

### Opción 1: APIs.guru (RECOMENDADO - Gratis)

**Tab: "APIs.guru (Free)"**

1. **Buscar específico:**
   - Escribir: "github"
   - Click "Search"
   - Click "Import" en resultados

2. **Importar por categoría:**
   - Click botón "Financial" (importa top 5)
   - Click botón "Social" (importa top 5)
   - Click botón "Cloud" (importa top 5)

**Resultado:** APIs importadas en ~5-10 segundos

---

### Opción 2: Apify (Para APIs no en APIs.guru)

**Tab: "RapidAPI via Apify"**

⚠️ **SOLO** si el API NO está en APIs.guru
💰 Costo: ~$0.01-0.10 por búsqueda

1. Escribir: "weather api"
2. Seleccionar max items: 10
3. Click "Search & Import"
4. Esperar 30-60 segundos
5. Verificar en **Backend → Api Hub → APIs**

**Resultado:** APIs scraped de RapidAPI

---

### Opción 3: Manual (APIs internos/custom)

**Tab: "Manual Entry"**

1. Name: "My Internal API"
2. Category: "Internal"
3. Description: "Company internal API"
4. Click "Create API"
5. Agregar endpoints manualmente

## 📊 Ver Resultados

**Backend → Api Hub → APIs**

Lista muestra:
- **Source badge** (APIs.guru, Apify, Manual)
- Número de endpoints
- Categoría

## ⚙️ Configuración Avanzada

### Preferred Import Source

**Backend → Settings → Api Hub Settings → General**

```
Preferred Import Source: apis_guru
```

Opciones:
- `apis_guru` - Default (gratis)
- `apify` - Scraping (paid)
- `manual` - Entrada manual

### Cache Settings

```
Cache TTL: 3600 (1 hora)
APIs List Cache: 86400 (24 horas)
Import Limit: 20
```

## 🔍 Monitoring

### Ver Queue Jobs

```bash
# Ver trabajos en cola
redis-cli LLEN queues:default

# Ver logs en tiempo real
tail -f storage/logs/laravel.log | grep -i apihub
```

### Verificar Conexiones

**Backend → Api Hub → Import**

- ✅ Verde = Conectado
- ⚠️ Amarillo = Warning
- ❌ Rojo = Error

## 🎯 Flujo Recomendado

```
1. Buscar en APIs.guru primero (GRATIS)
   ↓
2. Si no está → Usar Apify (PAID)
   ↓
3. Si es interno → Manual Entry
```

## 💡 Tips

1. **APIs.guru es MUCHO más rápido** que Apify
2. **Apify cuesta dinero** - usar solo cuando necesario
3. **Bulk imports** (5-10 APIs) usan APIs.guru
4. **Specific searches** usan Apify si no están en APIs.guru
5. **Queue worker** debe estar corriendo para imports

## ❓ Troubleshooting

### "Apify Not Configured"
→ Configurar token en Settings

### "Queue not processing"
→ Iniciar queue worker: `php artisan queue:work redis`

### "Import taking too long"
→ Normal para Apify (~60s), verificar queue worker running

### "API already exists"
→ Ya está importado, verificar lista de APIs

## 📈 Estadísticas

**Backend → Api Hub → Import** (arriba)

```
Total: 25 | APIs.guru: 15 | Apify: 3 | Manual: 5
```

## ✅ Todo Listo!

Ahora puedes:
- ✅ Importar desde APIs.guru (gratis, 2500+ APIs)
- ✅ Scrapear RapidAPI vía Apify (paid, específicos)
- ✅ Crear APIs manualmente
- ✅ Ver source de cada API en la lista
- ✅ Endpoints auto-parsed con parámetros
