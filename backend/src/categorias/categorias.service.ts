import { Injectable, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import { CreateCategoriaDto } from './dto/create-categoria.dto';
import { UpdateCategoriaDto } from './dto/update-categoria.dto';
import * as path from 'path';
import * as fs from 'fs';
import type { Express } from 'express';

@Injectable()
export class CategoriasService {
  constructor(private prisma: PrismaService) {}

  async findAll(activo: string | undefined) {
    const onlyActive = activo === undefined ? true : activo === 'true';
    return this.prisma.categoria.findMany({
      where: onlyActive ? { activo: true } : undefined,
      orderBy: { nombre: 'asc' },
    });
  }

  async findById(id: number) {
    const categoria = await this.prisma.categoria.findFirst({
      where: { id, activo: true },
    });
    if (!categoria) throw new NotFoundException('Categoría no encontrada');
    return categoria;
  }

  async findProductosByCategoria(id: number, pagina = 1, limite = 20) {
    const categoria = await this.prisma.categoria.findFirst({
      where: { id, activo: true },
    });
    if (!categoria) throw new NotFoundException('Categoría no encontrada');

    const skip = (pagina - 1) * limite;
    const [productos, total] = await Promise.all([
      this.prisma.producto.findMany({
        where: { categoria_id: id, activo: true },
        include: { categoria: { select: { nombre: true } } },
        orderBy: { created_at: 'desc' },
        skip,
        take: limite,
      }),
      this.prisma.producto.count({ where: { categoria_id: id, activo: true } }),
    ]);

    const productosMap = productos.map((p) => ({
      ...p,
      precio: parseFloat(p.precio.toString()),
      categoria_nombre: p.categoria?.nombre ?? null,
    }));

    return {
      categoria,
      productos: productosMap,
      pagination: {
        total,
        pagina,
        limite,
        totalPaginas: Math.ceil(total / limite),
      },
    };
  }

  // ADMIN: listar con paginación y búsqueda
  async adminList(params: {
    limite?: number;
    pagina?: number;
    buscar?: string;
    activo?: boolean | undefined;
  }) {
    const limite = params.limite ?? 20;
    const pagina = params.pagina ?? 1;
    const skip = (pagina - 1) * limite;

    const where: any = {};
    if (params.buscar) {
      where.OR = [
        { nombre: { contains: params.buscar, mode: 'insensitive' } },
        { descripcion: { contains: params.buscar, mode: 'insensitive' } },
      ];
    }
    if (params.activo !== undefined) where.activo = params.activo;

    const [categorias, total] = await Promise.all([
      this.prisma.categoria.findMany({
        where,
        orderBy: { nombre: 'asc' },
        skip,
        take: limite,
      }),
      this.prisma.categoria.count({ where }),
    ]);

    return {
      status: 200,
      body: {
        categorias,
        pagination: {
          total,
          pagina,
          limite,
          totalPaginas: Math.ceil(total / limite),
        },
      },
    };
  }

  // ADMIN: obtener categoria
  async adminGet(id: number) {
    const categoria = await this.prisma.categoria.findUnique({ where: { id } });
    if (!categoria)
      return { status: 404, body: { error: 'Categoría no encontrada' } };
    return { status: 200, body: { categoria } };
  }

  // ADMIN: crear categoría
  async adminCrear(body: CreateCategoriaDto) {
    const nombre = String(body.nombre || '').trim();
    if (!nombre || nombre.length < 2) {
      return {
        status: 400,
        body: {
          error: 'Datos inválidos',
          message: 'El nombre debe tener al menos 2 caracteres',
        },
      };
    }
    const existente = await this.prisma.categoria.findUnique({
      where: { nombre },
    });
    if (existente) {
      return {
        status: 400,
        body: {
          error: 'Duplicado',
          message: 'Ya existe una categoría con ese nombre',
        },
      };
    }
    const creada = await this.prisma.categoria.create({
      data: {
        nombre,
        descripcion: body.descripcion || null,
        imagen: body.imagen || null,
      },
    });
    return {
      status: 201,
      body: { message: 'Categoría creada', categoria: creada },
    };
  }

  // ADMIN: actualizar categoría
  async adminActualizar(id: number, body: UpdateCategoriaDto) {
    const existente = await this.prisma.categoria.findUnique({ where: { id } });
    if (!existente)
      return { status: 404, body: { error: 'Categoría no encontrada' } };

    const data: any = {};
    if (body.nombre !== undefined) {
      const nombre = String(body.nombre).trim();
      if (!nombre || nombre.length < 2) {
        return {
          status: 400,
          body: {
            error: 'Datos inválidos',
            message: 'El nombre debe tener al menos 2 caracteres',
          },
        };
      }
      if (nombre !== existente.nombre) {
        const dup = await this.prisma.categoria.findUnique({
          where: { nombre },
        });
        if (dup)
          return {
            status: 400,
            body: {
              error: 'Duplicado',
              message: 'Ya existe una categoría con ese nombre',
            },
          };
      }
      data.nombre = nombre;
    }
    if (body.descripcion !== undefined)
      data.descripcion = body.descripcion || null;
    if (body.imagen !== undefined) data.imagen = body.imagen || null;

    if (Object.keys(data).length === 0)
      return { status: 400, body: { error: 'Sin cambios' } };

    const actualizada = await this.prisma.categoria.update({
      where: { id },
      data,
    });
    return {
      status: 200,
      body: { message: 'Categoría actualizada', categoria: actualizada },
    };
  }

  // ADMIN: actualizar imagen subida local
  async adminActualizarImagen(id: number, file: Express.Multer.File) {
    try {
      const existente = await this.prisma.categoria.findUnique({
        where: { id },
      });
      if (!existente) {
        // limpiar archivo subido si no existe categoría
        if (file?.path) this.safeUnlink(file.path);
        return { status: 404, body: { error: 'Categoría no encontrada' } };
      }
      if (!file)
        return {
          status: 400,
          body: {
            error: 'Archivo requerido',
            message: 'Debe enviar una imagen',
          },
        };

      const nuevaImagen = `categorias/${file.filename}`;
      // eliminar imagen anterior si era local
      if (existente.imagen && !String(existente.imagen).startsWith('http')) {
        const anterior = path.join(
          process.cwd(),
          'uploads',
          String(existente.imagen),
        );
        this.safeUnlink(anterior);
      }
      const actualizada = await this.prisma.categoria.update({
        where: { id },
        data: { imagen: nuevaImagen },
      });
      return {
        status: 200,
        body: { message: 'Imagen actualizada', categoria: actualizada },
      };
    } catch (e) {
      if (file?.path) this.safeUnlink(file.path);
      return {
        status: 500,
        body: {
          error: 'Error interno',
          message: 'No se pudo actualizar la imagen',
        },
      };
    }
  }

  // ADMIN: actualizar estado
  async adminActualizarEstado(id: number, activo: boolean) {
    const existente = await this.prisma.categoria.findUnique({ where: { id } });
    if (!existente)
      return { status: 404, body: { error: 'Categoría no encontrada' } };
    const actualizada = await this.prisma.categoria.update({
      where: { id },
      data: { activo: Boolean(activo) },
    });
    return {
      status: 200,
      body: { message: 'Estado actualizado', categoria: actualizada },
    };
  }

  // ADMIN: eliminar (soft delete)
  async adminEliminar(id: number) {
    const existente = await this.prisma.categoria.findUnique({ where: { id } });
    if (!existente)
      return { status: 404, body: { error: 'Categoría no encontrada' } };
    const actualizada = await this.prisma.categoria.update({
      where: { id },
      data: { activo: false },
    });
    return {
      status: 200,
      body: { message: 'Categoría eliminada', categoria: actualizada },
    };
  }

  private safeUnlink(p: string) {
    try {
      if (p && fs.existsSync(p)) fs.unlinkSync(p);
    } catch (e) {}
  }
}
