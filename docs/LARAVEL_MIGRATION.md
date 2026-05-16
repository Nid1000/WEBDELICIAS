# ¿Se puede migrar a Laravel manteniendo “todo igual”?

Sí, **se puede**, pero lo importante es definir qué significa “igual”:

- **Igual para clientes (Next.js + Flutter)**: mismas rutas (`/api/...`), misma auth (Bearer JWT), mismos nombres de campos y respuestas.
- **Igual en BD**: mismas tablas/campos (MySQL/MariaDB).
- **Igual en validaciones**: mismas reglas (y ojalá además **HTTP status codes correctos**).

## Lo que ya tienes hoy

- Backend actual: NestJS + Prisma (MySQL) + JWT + Swagger (`/api/docs` y `/api/docs-yaml`).
- Frontend web: Next.js reescribe `"/api/*"` y `"/uploads/*"` al backend.
- Flutter: consume `baseUrl/api` y ya usa los endpoints actuales (ver `lib/services/api_endpoints.dart`).

## Ojo: el dump SQL tiene más tablas que Prisma

En `WEBNIDA/delicias_bakery.sql` aparecen tablas que **no están en** `WEBNIDA/backend/prisma/schema.prisma` (por ejemplo varias `*_app`, `seguimiento`, `pagos`, `direcciones`, `calificaciones`, etc.).

Para migrar “todo”, primero hay que decidir:

- **A)** Migrar solo lo que usa el backend actual (lo más rápido y seguro).
- **B)** Migrar todas las tablas del dump, aunque hoy no se usen (más trabajo).

Tablas que el backend Nest actual usa directamente (por Prisma o SQL):

- `usuarios`, `administradores`, `login_logs`
- `categorias`, `productos`
- `pedidos`, `pedido_detalles`
- `comprobantes`, `comprobante_series`
- `notificaciones_app`
- `catalogo_distritos_huancayo`

## Recomendación de arquitectura (Laravel)

Mantén **una sola API** (Laravel) y que tanto Next como Flutter apunten a esa API:

- API: Laravel (rutas bajo `/api/*`)
- Auth: JWT Bearer con el mismo `JWT_SECRET` y payload `{id,email,tipo}`
- Uploads: servir `/uploads/*` desde `public/uploads` (o `storage` con symlink)
- BD: MySQL/MariaDB usando Eloquent + migraciones (o importar el dump y luego modelar)

## Mapeo de módulos (Nest → Laravel)

1) `auth`
- Laravel: `AuthController` + middleware `jwt` + middlewares `isAdmin` / `isUsuario`.
- Para “igual”: conservar claims `tipo=admin|usuario` y expiración ~24h.

2) `usuarios`, `categorias`, `productos`, `pedidos`
- Laravel: controllers REST + `FormRequest` para validaciones.
- Reglas que hoy están en “service” (ej. “categoría existe y está activa”, “email único”) deben pasar a `rules()` o a validaciones de dominio.

3) `facturacion`
- Laravel: servicio que:
  - verifica DNI/RUC (si se sigue usando Decolecta)
  - genera PDF/XML/PNG en `uploads/comprobantes`
  - registra `comprobantes` + `comprobante_series` (correlativo transaccional)

4) `notificaciones`
- Hoy se crean tablas con SQL “on the fly”.
- En Laravel: crear migración para `notificaciones_app` y usar Eloquent/Query Builder.

5) `reportes`
- Laravel: queries agregadas (Eloquent/DB) similares a las del `ReportesService`.

## Validaciones (cómo mantenerlas “igual”)

En Nest se usa `ValidationPipe` + `class-validator`. En Laravel se replica con:

- `FormRequest` por endpoint (ej. `CreatePedidoRequest`, `EmitirComprobanteRequest`)
- `$request->validated()` para comportarse como “whitelist”
- Reglas equivalentes:
  - archivos: `mimes:jpeg,jpg,png,gif,webp` + `max:5120`
  - strings: `min`, `max`, `email`, `regex`
  - ints: `integer`, `min`
  - exists/unique: `exists:categorias,id`, `unique:usuarios,email`

## Peso / performance (“que pese menos”)

Antes de migrar, suele ser más barato optimizar lo actual:

- NestJS:
  - desplegar solo `dist/` + `node_modules` de producción (`npm ci --omit=dev`)
  - habilitar compresión + cache headers para `/uploads`
- Next:
  - revisar imágenes remotas, `next/image`, y evitar bundles grandes en admin

Laravel puede ser liviano en runtime (PHP-FPM + OPcache), pero el cambio cuesta: reescribir endpoints, auth, facturación, uploads y reportes.

## Plan de migración seguro (sin romper Next/Flutter)

1) Congelar contrato:
   - tomar el OpenAPI desde `GET /api/docs-yaml` y guardarlo como referencia.
2) Levantar Laravel en paralelo:
   - mismo `DATABASE_URL` / credenciales MySQL.
3) Implementar auth + 2–3 endpoints primero:
   - `auth/login`, `auth/verify`, `productos` (lectura).
4) Comparar respuestas (golden tests):
   - mismos campos, mismos errores, mismos status.
5) Migrar el resto por módulos.
6) Switch de rewrites/baseUrl a Laravel.

Backend Laravel ya está en `WEBNIDA/backend/` (el backend Nest anterior quedó en `WEBNIDA/backend-nestjs/`).
