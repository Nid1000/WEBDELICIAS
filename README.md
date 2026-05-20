# Delicias Bakery — Monorepo Next.js + Laravel

Aplicación web profesional para una panadería, consolidada en un monorepo con:
- Frontend: Next.js 15 (App Router, SSR/SSG), Tailwind CSS.
- Backend: Laravel (JWT Bearer compatible), MySQL/MariaDB como base de datos.

## 🚀 Características

### Frontend (Next.js)
- Ruteo por archivos (App Router) y metadata por página.
- SSR/SSG donde aporta rendimiento (catálogo, páginas públicas).
- Autenticación con token JWT almacenado en cookie.
- Protección de rutas mediante `src/middleware.ts` (usuario y admin).
- Rewrites de `/api/*` y `/uploads/*` hacia el backend Laravel.
- Componentes de UI y diseño admin en `src/design/admin/`.

### Backend (Laravel)
- Endpoints compatibles con la API anterior bajo `/api/*`.
- JWT Bearer con payload `{ id, email, tipo }`.
- Validaciones en cada endpoint (HTTP status codes correctos).
- Archivos estáticos en `/uploads/*` (public).

## 📁 Estructura del Proyecto

```
delicias/
├── backend/               # Laravel API (MySQL/MariaDB)
│   ├── app/               # Controladores, middlewares, servicios
│   ├── routes/            # Rutas (API en routes/api.php)
│   └── public/uploads/    # Archivos servidos en /uploads
├── frontend/              # Next.js (App Router)
│   ├── src/app/           # Rutas y layouts
│   ├── src/components/    # Componentes UI
│   ├── src/context/       # Contextos (Auth, Cart)
│   └── next.config.ts     # Rewrites hacia backend
└── package.json           # Scripts del monorepo
```

## 📋 Requisitos Previos

- Node.js 18+ (recomendado 20+).
- PHP 8.3+ (incluido portable en `WEBNIDA/tools/` en este repo).
- Composer (incluido portable en `WEBNIDA/tools/` en este repo).
- MySQL 8.x / MariaDB en local o servicio gestionado.
- npm.

## ⚙️ Configuración

1) Instalar dependencias del monorepo (frontend):

```bash
npm run install-all
```

2) Backend — Variables de entorno (`backend/.env`):

```env
APP_URL="http://localhost:5001"
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=delicias_bakery
DB_USERNAME=root
DB_PASSWORD=
JWT_SECRET="tu_jwt_secret_seguro"
DECOLECTA_TOKEN="tu_token"
```

## ▶️ Ejecución en Desarrollo

Desde la raíz del monorepo:

```bash
npm run dev
```

- Frontend (Next): http://localhost:3000
- Backend (Laravel): http://localhost:5001

El frontend reescribe `/api/*` y `/uploads/*` hacia el backend. Además, el frontend usa `NEXT_PUBLIC_API_BASE_URL` para apuntar directo al backend cuando es necesario (por ejemplo, uploads).

Si estás dentro de `backend/` y quieres levantar solo la API:
```bash
npm run backend
```

En Windows también puedes usar:
- `WEBNIDA/run-backend.ps1`
- `WEBNIDA/run-frontend.ps1`
- `WEBNIDA/run-all.ps1`

## 🧪 Pruebas

- Frontend (Next): pendiente de integrar pruebas (p. ej. Vitest/Playwright).

## 🔐 Usuarios por Defecto

Si ejecutaste el seed:
- Admin: `admin@delicias.com` / `admin123` (puedes cambiarlo en `.env`).

## 🧰 Tecnologías

### Frontend
- Next.js 15, React 19, Tailwind CSS 4, Framer Motion, Lucide React, Swiper.

### Backend
- Laravel, JWT (firebase/php-jwt), Dompdf (PDF), MySQL/MariaDB.

## 📖 Notas de Migración

- Se eliminó la app independiente de React (CRA/Vite/React Router DOM). El frontend ahora es 100% Next.js con App Router.
- Las rutas protegidas se manejan vía `middleware.ts` y verificación en backend.
- Las llamadas a API se realizan contra `/api/*`, y son atendidas por Laravel mediante rewrites definidos en `next.config.ts`.

## 📄 Licencia

MIT.

## 📞 Soporte

Para soporte o preguntas, contacta a: soporte@delicias.com

---

¡Gracias por usar Delicias Bakery! 🥖🧁
