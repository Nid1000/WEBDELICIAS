# Contrato de API (actual) — NestJS (`/api/*`)

Este documento resume lo que hoy consumen **Next.js** (`WEBNIDA/frontend`) y **Flutter** (raíz del repo) desde el backend **NestJS** (`WEBNIDA/backend`).

## Base URL y prefijo

- El backend expone todo bajo el prefijo: `/{prefix}` = `/api`
- Archivos estáticos (imágenes y comprobantes): `/uploads/*`

En Next.js, las llamadas a `"/api/..."` y `"/uploads/..."` pasan por los *rewrites* definidos en `WEBNIDA/frontend/next.config.ts`.

## Autenticación

- Header: `Authorization: Bearer <token>`
- JWT payload: `{ id: number, email: string, tipo: "usuario" | "admin" }`
- Expiración configurada: `24h`
- Guards:
  - `UsuarioGuard`: requiere `tipo === "usuario"`
  - `AdminGuard`: requiere `tipo === "admin"`

## Endpoints

### Auth (`/api/auth/*`)

- `POST /auth/register` (público)
  - Body (DTO): `RegisterUserDto`
    - `nombre` string (min 2)
    - `apellido` string (min 2)
    - `email` email
    - `password` regex: min 6 + (1 mayúscula, 1 minúscula, 1 número)
    - opcionales: `telefono`, `direccion` (min 5), `distrito` (min 2), `numero_casa` (min 1)
  - Respuesta: `{ user, token }` o error `{ error, message }`

- `POST /auth/login` (público)
  - Body (DTO): `LoginDto` (`email`, `password`)
  - Respuesta: `{ user, token }` o error `{ error, message }`

- `POST /auth/admin/login` (público)
  - Body (DTO): `LoginDto` (`email`, `password`)
  - Respuesta: `{ admin, token }` o error `{ error, message }`

- `GET /auth/verify` (Bearer JWT)
  - Respuesta: `{ tipo: "admin", admin: ... }` o `{ tipo: "usuario", user: ... }`

### Categorías (`/api/categorias/*`)

- `GET /categorias` (público)
  - Query: `activo=true|false` (si se omite, devuelve solo activas)

- `GET /categorias/:id` (público)

- `GET /categorias/:id/productos` (público)
  - Query: `pagina` (default 1), `limite` (default 20)

Admin (Bearer JWT + admin):

- `GET /categorias/admin/todos`
  - Query: `pagina`, `limite`, `buscar`, `activo`
- `GET /categorias/admin/:id`
- `POST /categorias/admin`
  - Body (DTO): `CreateCategoriaDto` (`nombre` min 2, opcional `descripcion`, `imagen`)
- `PUT /categorias/admin/:id`
  - Body (DTO): `UpdateCategoriaDto` (opcionales `nombre`, `descripcion`, `imagen`)
- `PUT /categorias/admin/:id/imagen` (multipart)
  - FormData: `imagen` (jpeg/jpg/png/gif/webp)
  - Límite: `MAX_FILE_SIZE` (default 5MB)
- `PATCH /categorias/admin/:id/estado`
  - Body: `{ activo: boolean }`
- `DELETE /categorias/admin/:id`
  - Soft delete: `activo = false`

### Productos (`/api/productos/*`)

- `GET /productos` (público)
  - Query:
    - `categoria` (id)
    - `destacado=true`
    - `buscar` (texto)
    - `pagina` (default 1)
    - `limite` (default 50)

- `GET /productos/:id` (público)

Admin (Bearer JWT + admin):

- `POST /productos` (multipart opcional)
  - FormData:
    - `imagen` (opcional, jpeg/jpg/png/gif/webp)
    - fields (DTO): `CreateProductoDto`
      - `nombre` string (2..200)
      - `descripcion` opcional (0..2000)
      - `precio` number min 0
      - `categoria_id` int min 1
      - `stock` opcional int min 0
      - `destacado` opcional boolean
      - `imagen_url` opcional URL

- `PUT /productos/:id` (multipart opcional)
  - fields (DTO): `UpdateProductoDto` (todo opcional)

- `DELETE /productos/:id`
  - Soft delete: `activo = false`

### Usuarios (`/api/usuarios/*`)

- `GET /usuarios/distritos-huancayo` (público)

Usuario (Bearer JWT + usuario):

- `GET /usuarios/perfil`
- `PUT /usuarios/perfil`
  - Body (DTO): `UpdatePerfilDto` (todo opcional)
- `PUT /usuarios/cambiar-password`
  - Body (DTO): `ChangePasswordDto` + validación extra: `passwordNueva === confirmarPassword`
- `GET /usuarios/estadisticas`

Admin (Bearer JWT + admin):

- `GET /usuarios/admin/todos` (paginación/filtros)
- `GET /usuarios/admin/:id`
- `PATCH /usuarios/admin/:id/estado`
  - Body (DTO): `EstadoUsuarioDto` (`activo: boolean`)
- `PUT /usuarios/admin/:id`
  - Body: `AdminUpdateUsuarioDto` (validaciones aplicadas en service)

### Pedidos (`/api/pedidos/*`)

Usuario (Bearer JWT + usuario):

- `POST /pedidos`
  - Body (DTO): `CreatePedidoDto`
    - `productos`: array `{ id, cantidad }` (obligatorio, no vacío)
    - opcional: `fecha_entrega` (ISO date), `direccion_entrega`, `distrito_entrega`, `numero_casa_entrega`, `direccion_id`, `telefono_contacto`, `notas`, `pago`

- `GET /pedidos/mis-pedidos` (paginación)
- `GET /pedidos/:id`
- `PUT /pedidos/:id/cancelar`

Admin (Bearer JWT + admin):

- `GET /pedidos/admin/todos` (filtros por estado/fechas/búsqueda)
- `GET /pedidos/admin/:id`
- `PATCH /pedidos/admin/:id/estado`
  - Body: `{ estado: "pendiente" | "listo" | "entregado" | "cancelado" }`
- `PUT /pedidos/admin/:id/fecha-entrega`
  - Body: `{ fecha_entrega: string | null }`

### Facturación (`/api/facturacion/*`)

Usuario (Bearer JWT + usuario):

- `POST /facturacion/emitir`
  - Body (DTO): `EmitirDto` (`pedido_id`, `comprobante_tipo`, `tipo_documento`, `numero_documento`)
  - Validaciones adicionales:
    - `factura` requiere `tipo_documento = RUC`
    - DNI: 8 dígitos, RUC: 11 dígitos
  - Genera archivos (PDF/XML/PNG) en `/uploads/comprobantes/*`
  - Header opcional: `X-Decolecta-Token` (fallback a `DECOLECTA_TOKEN`)

- `GET /facturacion/mis-comprobantes`

Admin (Bearer JWT + admin):

- `GET /facturacion/admin/comprobantes`

Usuario (Bearer JWT + usuario):

- `GET /facturacion/consulta-dni?numero=...`
- `GET /facturacion/consulta-ruc?numero=...`

### Notificaciones (`/api/notificaciones/*`)

Usuario (Bearer JWT + usuario):

- `GET /notificaciones/pendientes?canal=web|mobile` (default: `mobile`)
- `POST /notificaciones/marcar-mostradas`
  - Body: `{ ids?: number[], canal?: "web" | "mobile" }`

Admin (Bearer JWT + admin):

- `POST /notificaciones/admin/enviar`
  - Body: `{ title, message, route?, targetId?, userId?, audience? }`

### Reportes (`/api/reportes/*`) — Admin

Admin (Bearer JWT + admin):

- `GET /reportes/admin/ventas-diarias?desde&hasta`
- `GET /reportes/admin/ventas-semanales?desde&hasta`
- `GET /reportes/admin/ventas-mensuales?desde&hasta`
- `GET /reportes/admin/top-productos?desde&hasta&limite`
- `GET /reportes/admin/top-categorias?desde&hasta&limite`

### Contacto (`/api/contacto`)

- `POST /contacto` (público)
  - Body (DTO): `CreateContactoDto` (`nombre`, `email`, `mensaje`, `telefono?`)

