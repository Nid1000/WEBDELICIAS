# Optimización de “peso” (web + backend + móvil)

## Backend NestJS (Node)

- Producción: instalar solo dependencias de producción.
  - `npm ci --omit=dev`
- Compilar y ejecutar desde `dist/`:
  - `npm run build`
  - `node dist/main.js`
- Uploads:
  - servir `/uploads` con cache headers (CDN o reverse proxy) si es posible.

## Frontend Next.js

- Ya usa rewrites para evitar CORS (bien).
- Para deploy liviano:
  - habilitar output standalone (opcional) y ejecutar solo el servidor necesario.
- Revisar bundles grandes (admin):
  - imports dinámicos donde aplique
  - evitar dependencias pesadas en rutas públicas

## API única para Next + Flutter

Ya está unificada: ambos consumen `/api/*`. La regla para no romper nada es:

- mantener prefijo `/api`
- mantener Bearer JWT
- mantener respuestas con los mismos campos (idealmente además con status HTTP correcto)

## Flutter

- El tamaño final depende sobre todo de:
  - assets (imágenes) en `assets/`
  - modo release + tree shaking
- Construir siempre en release:
  - Android: `flutter build apk --release` o `flutter build appbundle --release`

