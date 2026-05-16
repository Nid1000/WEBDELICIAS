// Simple seeding script to create an admin if it doesn't exist
const { PrismaClient } = require('../generated/prisma');
const bcrypt = require('bcryptjs');

async function main() {
  const prisma = new PrismaClient();
  await prisma.$connect();
  try {
    const email = process.env.ADMIN_EMAIL || 'admin@delicias.com';
    const password = process.env.ADMIN_PASSWORD || 'admin123';
    const existing = await prisma.administrador.findUnique({ where: { email } });
    if (existing) {
      console.log('[seed-admin] Admin ya existe:', existing.email);
    } else {
      const hashed = await bcrypt.hash(password, 10);
      const admin = await prisma.administrador.create({
        data: {
          nombre: 'Administrador',
          email,
          password: hashed,
          rol: 'admin',
          activo: true,
        },
        select: { id: true, nombre: true, email: true, rol: true, activo: true },
      });
      console.log('[seed-admin] Admin creado:', admin);
    }
  } catch (e) {
    console.error('[seed-admin] Error:', e);
    process.exitCode = 1;
  } finally {
    await prisma.$disconnect();
  }
}

main();