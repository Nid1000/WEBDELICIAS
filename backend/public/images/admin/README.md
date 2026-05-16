# Imágenes del Panel de Administración

Esta carpeta contiene los assets estáticos (optimizados) usados por la interfaz de administración.

Estructura sugerida:
- `icons/`: iconos específicos del panel (SVG preferido).
- `backgrounds/`: fondos y patrones.
- `banners/`: banners informativos del panel.
- `illustrations/`: ilustraciones usadas en páginas del admin.

Ejemplo de uso en Next.js:

```tsx
import Image from "next/image";

export default function AdminLogo() {
  return (
    <Image src="/images/admin/icons/logo-admin.svg" alt="Admin" width={120} height={40} />
  );
}
```