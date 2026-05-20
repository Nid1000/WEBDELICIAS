import { Injectable } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

type AppNotificationRow = {
  id: number;
  titulo: string;
  mensaje: string;
  tipo: string | null;
  target_route: string | null;
  target_id: string | null;
  created_at: Date;
};

type NotificationChannel = 'web' | 'mobile';
type NotificationAudience = 'web' | 'mobile' | 'both';

@Injectable()
export class NotificacionesService {
  constructor(private prisma: PrismaService) {}

  private _ready = false;

  private async ensureTable() {
    if (this._ready) return;
    await this.prisma.$executeRawUnsafe(`
      CREATE TABLE IF NOT EXISTS notificaciones_app (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        titulo VARCHAR(160) NOT NULL,
        mensaje TEXT NOT NULL,
        tipo VARCHAR(40) NULL,
        audience VARCHAR(16) NOT NULL DEFAULT 'both',
        target_route VARCHAR(40) NULL,
        target_id VARCHAR(80) NULL,
        mostrada_mobile TINYINT(1) NOT NULL DEFAULT 0,
        mostrada_web TINYINT(1) NOT NULL DEFAULT 0,
        leida TINYINT(1) NOT NULL DEFAULT 0,
        shown_mobile_at DATETIME NULL,
        shown_web_at DATETIME NULL,
        read_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_notif_usuario_mobile (usuario_id, mostrada_mobile, created_at),
        INDEX idx_notif_usuario_web (usuario_id, mostrada_web, created_at)
      )
    `);
    await this.prisma.$executeRawUnsafe(`
      ALTER TABLE notificaciones_app
      ADD COLUMN IF NOT EXISTS audience VARCHAR(16) NOT NULL DEFAULT 'both'
    `);
    await this.prisma.$executeRawUnsafe(`
      ALTER TABLE notificaciones_app
      ADD COLUMN IF NOT EXISTS mostrada_mobile TINYINT(1) NOT NULL DEFAULT 0
    `);
    await this.prisma.$executeRawUnsafe(`
      ALTER TABLE notificaciones_app
      ADD COLUMN IF NOT EXISTS mostrada_web TINYINT(1) NOT NULL DEFAULT 0
    `);
    await this.prisma.$executeRawUnsafe(`
      ALTER TABLE notificaciones_app
      ADD COLUMN IF NOT EXISTS shown_mobile_at DATETIME NULL
    `);
    await this.prisma.$executeRawUnsafe(`
      ALTER TABLE notificaciones_app
      ADD COLUMN IF NOT EXISTS shown_web_at DATETIME NULL
    `);
    this._ready = true;
  }

  async createForUser(params: {
    userId: number;
    title: string;
    body: string;
    type?: string;
    route?: string | null;
    targetId?: string | number | null;
    audience?: NotificationAudience;
  }) {
    await this.ensureTable();
    await this.prisma.$executeRawUnsafe(
      `
        INSERT INTO notificaciones_app
          (usuario_id, titulo, mensaje, tipo, audience, target_route, target_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
      `,
      params.userId,
      params.title,
      params.body,
      params.type ?? null,
      params.audience ?? 'both',
      params.route ?? null,
      params.targetId?.toString() ?? null,
    );
  }

  async broadcastNewProduct(productId: number, productName: string) {
    await this.broadcastManual({
      title: 'Nuevo producto',
      body: `Ya esta disponible: ${productName}`,
      type: 'new_product',
      route: 'store',
      targetId: productId,
      audience: 'both',
    });
  }

  async broadcastManual(params: {
    title: string;
    body: string;
    route?: string | null;
    type?: string;
    targetId?: string | number | null;
    audience?: NotificationAudience;
  }) {
    await this.ensureTable();
    await this.prisma.$executeRawUnsafe(
      `
        INSERT INTO notificaciones_app
          (usuario_id, titulo, mensaje, tipo, audience, target_route, target_id)
        SELECT id, ?, ?, ?, ?, ?, ?
        FROM usuarios
        WHERE activo = 1
      `,
      params.title,
      params.body,
      params.type ?? 'manual',
      params.audience ?? 'both',
      params.route ?? null,
      params.targetId?.toString() ?? null,
    );
  }

  async getPendingForUser(userId: number, channel: NotificationChannel) {
    await this.ensureTable();
    const shownColumn = channel === 'web' ? 'mostrada_web' : 'mostrada_mobile';
    const rows = await this.prisma.$queryRawUnsafe<AppNotificationRow[]>(
      `
        SELECT id, titulo, mensaje, tipo, target_route, target_id, created_at
        FROM notificaciones_app
        WHERE usuario_id = ?
          AND ${shownColumn} = 0
          AND audience IN (?, 'both')
        ORDER BY created_at DESC
        LIMIT 20
      `,
      userId,
      channel,
    );

    return rows.map((row) => ({
      id: row.id,
      title: row.titulo,
      body: row.mensaje,
      type: row.tipo ?? '',
      route: row.target_route ?? '',
      targetId: row.target_id ?? '',
      createdAt: row.created_at,
    }));
  }

  async markShown(userId: number, ids: number[], channel: NotificationChannel) {
    await this.ensureTable();
    if (ids.length === 0) return;

    const safeIds = ids
      .map((id) => Number(id))
      .filter((id) => !Number.isNaN(id) && id > 0)
      .join(',');
    if (safeIds.length === 0) return;

    const shownColumn = channel === 'web' ? 'mostrada_web' : 'mostrada_mobile';
    const shownAtColumn = channel === 'web' ? 'shown_web_at' : 'shown_mobile_at';

    await this.prisma.$executeRawUnsafe(`
      UPDATE notificaciones_app
      SET ${shownColumn} = 1, ${shownAtColumn} = NOW()
      WHERE usuario_id = ${Number(userId)} AND id IN (${safeIds})
    `);
  }
}
