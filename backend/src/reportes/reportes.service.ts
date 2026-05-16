import { Injectable } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class ReportesService {
  constructor(private prisma: PrismaService) {}

  private toFloat(n: any): number {
    if (n === null || typeof n === 'undefined') return 0;
    const s = String(n);
    const f = parseFloat(s);
    return isNaN(f) ? 0 : f;
  }

  async ventasDiarias(params: { desde?: string; hasta?: string }) {
    const where: any = {};
    if (params.desde || params.hasta) {
      where.created_at = {};
      if (params.desde) where.created_at.gte = new Date(params.desde);
      if (params.hasta) where.created_at.lte = new Date(params.hasta);
    }

    const pedidos = await this.prisma.pedido.findMany({
      where: { ...where, estado: { not: 'cancelado' } },
      select: { created_at: true, total: true },
      orderBy: { created_at: 'asc' },
    });

    const map = new Map<string, number>();
    for (const p of pedidos) {
      const key = p.created_at.toISOString().slice(0, 10); // YYYY-MM-DD
      const prev = map.get(key) ?? 0;
      map.set(key, prev + this.toFloat(p.total));
    }

    const data = Array.from(map.entries()).map(([fecha, total]) => ({
      fecha,
      total,
    }));
    return { status: 200, body: { data } };
  }

  async ventasSemanales(params: { desde?: string; hasta?: string }) {
    const where: any = {};
    if (params.desde || params.hasta) {
      where.created_at = {};
      if (params.desde) where.created_at.gte = new Date(params.desde);
      if (params.hasta) where.created_at.lte = new Date(params.hasta);
    }

    const pedidos = await this.prisma.pedido.findMany({
      where: { ...where, estado: { not: 'cancelado' } },
      select: { created_at: true, total: true },
      orderBy: { created_at: 'asc' },
    });

    const startOfWeek = (date: Date) => {
      const d = new Date(date);
      const day = d.getDay(); // 0=Dom, 1=Lun, ...
      const diff = (day + 6) % 7; // días desde lunes
      d.setDate(d.getDate() - diff);
      d.setHours(0, 0, 0, 0);
      return d;
    };

    const map = new Map<string, number>();
    for (const p of pedidos) {
      const sow = startOfWeek(p.created_at);
      const key = sow.toISOString().slice(0, 10); // YYYY-MM-DD inicio de semana
      const prev = map.get(key) ?? 0;
      map.set(key, prev + this.toFloat(p.total));
    }

    const data = Array.from(map.entries()).map(([semana, total]) => ({
      semana,
      total,
    }));
    return { status: 200, body: { data } };
  }

  // NUEVO: ventas mensuales (agrupa por inicio de mes)
  async ventasMensuales(params: { desde?: string; hasta?: string }) {
    const where: any = {};
    if (params.desde || params.hasta) {
      where.created_at = {};
      if (params.desde) where.created_at.gte = new Date(params.desde);
      if (params.hasta) where.created_at.lte = new Date(params.hasta);
    }

    const pedidos = await this.prisma.pedido.findMany({
      where: { ...where, estado: { not: 'cancelado' } },
      select: { created_at: true, total: true },
      orderBy: { created_at: 'asc' },
    });

    const startOfMonth = (date: Date) => {
      const d = new Date(date);
      d.setDate(1);
      d.setHours(0, 0, 0, 0);
      return d;
    };

    const map = new Map<string, number>();
    for (const p of pedidos) {
      const som = startOfMonth(p.created_at);
      const key = som.toISOString().slice(0, 7); // YYYY-MM
      const prev = map.get(key) ?? 0;
      map.set(key, prev + this.toFloat(p.total));
    }

    const data = Array.from(map.entries()).map(([mes, total]) => ({
      mes,
      total,
    }));
    return { status: 200, body: { data } };
  }

  async topProductos(params: {
    desde?: string;
    hasta?: string;
    limite?: number;
  }) {
    const whereDetalle: any = {
      producto_id: { not: null },
      pedido: { estado: { not: 'cancelado' } },
    };
    if (params.desde || params.hasta) {
      whereDetalle.pedido.created_at = {};
      if (params.desde)
        whereDetalle.pedido.created_at.gte = new Date(params.desde);
      if (params.hasta)
        whereDetalle.pedido.created_at.lte = new Date(params.hasta);
    }

    const detalles = await this.prisma.pedidoDetalle.findMany({
      where: whereDetalle,
      include: {
        producto: {
          select: { id: true, nombre: true, categoria_id: true, imagen: true },
        },
        pedido: { select: { id: true, created_at: true, estado: true } },
      },
    });

    const agg = new Map<
      number,
      {
        producto_id: number;
        nombre: string;
        imagen?: string | null;
        cantidad: number;
        subtotal: number;
      }
    >();
    for (const d of detalles) {
      const id = d.producto_id as number;
      if (!id) continue;
      const prev = agg.get(id) || {
        producto_id: id,
        nombre: d.producto?.nombre || `Producto ${id}`,
        imagen: d.producto?.imagen ?? null,
        cantidad: 0,
        subtotal: 0,
      };
      prev.cantidad += d.cantidad;
      prev.subtotal += this.toFloat(d.subtotal);
      agg.set(id, prev);
    }

    const items = Array.from(agg.values()).sort(
      (a, b) => b.cantidad - a.cantidad,
    );
    const limite = params.limite ?? 10;
    return { status: 200, body: { data: items.slice(0, limite) } };
  }

  async topCategorias(params: {
    desde?: string;
    hasta?: string;
    limite?: number;
  }) {
    const whereDetalle: any = {
      producto_id: { not: null },
      pedido: { estado: { not: 'cancelado' } },
    };
    if (params.desde || params.hasta) {
      whereDetalle.pedido.created_at = {};
      if (params.desde)
        whereDetalle.pedido.created_at.gte = new Date(params.desde);
      if (params.hasta)
        whereDetalle.pedido.created_at.lte = new Date(params.hasta);
    }

    const detalles = await this.prisma.pedidoDetalle.findMany({
      where: whereDetalle,
      include: {
        producto: { select: { id: true, nombre: true, categoria_id: true } },
        pedido: { select: { id: true, created_at: true, estado: true } },
      },
    });

    const agg = new Map<
      number,
      {
        categoria_id: number;
        nombre: string;
        cantidad: number;
        subtotal: number;
      }
    >();
    for (const d of detalles) {
      const catId = d.producto?.categoria_id as number | undefined;
      if (!catId) continue;
      const prev = agg.get(catId) || {
        categoria_id: catId,
        nombre: '',
        cantidad: 0,
        subtotal: 0,
      };
      prev.cantidad += d.cantidad;
      prev.subtotal += this.toFloat(d.subtotal);
      agg.set(catId, prev);
    }

    // Rellenar nombre de categoría
    const categoriasIds = Array.from(agg.keys());
    if (categoriasIds.length) {
      const cats = await this.prisma.categoria.findMany({
        where: { id: { in: categoriasIds } },
        select: { id: true, nombre: true },
      });
      const mapCats = new Map<number, string>(
        cats.map((c) => [c.id, c.nombre]),
      );
      for (const id of categoriasIds) {
        const item = agg.get(id)!;
        item.nombre = mapCats.get(id) || `Categoría ${id}`;
      }
    }

    const items = Array.from(agg.values()).sort(
      (a, b) => b.cantidad - a.cantidad,
    );
    const limite = params.limite ?? 10;
    return { status: 200, body: { data: items.slice(0, limite) } };
  }
}
