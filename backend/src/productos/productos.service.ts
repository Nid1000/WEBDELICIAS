import { Injectable, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import * as path from 'path';
import * as fs from 'fs';
import { CreateProductoDto } from './dto/create-producto.dto';
import { UpdateProductoDto } from './dto/update-producto.dto';
import type { Express } from 'express';
import { NotificacionesService } from '../notificaciones/notificaciones.service';

@Injectable()
export class ProductosService {
  constructor(
    private prisma: PrismaService,
    private notificaciones: NotificacionesService,
  ) {}

  async listarProductos(params: {
    categoria?: string;
    destacado?: string;
    buscar?: string;
    limite?: string;
    pagina?: string;
  }) {
    const categoriaId = params.categoria
      ? parseInt(params.categoria, 10)
      : undefined;
    const destacado = params.destacado === 'true' ? true : undefined;
    const buscar = params.buscar?.trim();
    const limite = params.limite ? parseInt(params.limite, 10) : 50;
    const pagina = params.pagina ? parseInt(params.pagina, 10) : 1;
    const skip = (pagina - 1) * limite;

    const where: any = { activo: true };
    if (categoriaId) where.categoria_id = categoriaId;
    if (destacado !== undefined) where.destacado = true;
    if (buscar) {
      where.OR = [
        { nombre: { contains: buscar } },
        { descripcion: { contains: buscar } },
      ];
    }

    const [productos, total] = await Promise.all([
      this.prisma.producto.findMany({
        where,
        include: { categoria: { select: { nombre: true } } },
        orderBy: { created_at: 'desc' },
        skip,
        take: limite,
      }),
      this.prisma.producto.count({ where }),
    ]);

    const productosMap = productos.map((p) => ({
      ...p,
      precio: parseFloat(p.precio.toString()),
      categoria_nombre: p.categoria?.nombre ?? null,
    }));

    return {
      productos: productosMap,
      pagination: {
        total,
        pagina,
        limite,
        totalPaginas: Math.ceil(total / limite),
      },
    };
  }

  async obtenerProducto(id: number) {
    const p = await this.prisma.producto.findFirst({
      where: { id, activo: true },
      include: { categoria: { select: { nombre: true } } },
    });
    if (!p) throw new NotFoundException('Producto no encontrado');
    return {
      ...p,
      precio: parseFloat(p.precio.toString()),
      categoria_nombre: p.categoria?.nombre ?? null,
    };
  }

  async crearProducto(params: {
    file?: Express.Multer.File | null;
    body: CreateProductoDto;
  }) {
    const { file, body } = params;
    try {
      const {
        nombre,
        descripcion,
        precio,
        categoria_id,
        stock = 0,
        destacado = false,
        imagen_url,
      } = body;
      // Validaciones básicas
      if (!nombre || nombre.trim().length < 2) {
        if (file) this.safeUnlink(file.path);
        return {
          status: 400,
          body: {
            error: 'Datos inválidos',
            message: 'El nombre debe tener al menos 2 caracteres',
          },
        };
      }
      if (
        precio === undefined ||
        isNaN(parseFloat(String(precio))) ||
        parseFloat(String(precio)) < 0
      ) {
        if (file) this.safeUnlink(file.path);
        return {
          status: 400,
          body: {
            error: 'Datos inválidos',
            message: 'El precio debe ser un número positivo',
          },
        };
      }
      const categoriaId = parseInt(String(categoria_id), 10);
      if (!categoriaId || categoriaId < 1) {
        if (file) this.safeUnlink(file.path);
        return {
          status: 400,
          body: {
            error: 'Categoría inválida',
            message: 'La categoría seleccionada no existe',
          },
        };
      }

      // Verificar categoría
      const categoria = await this.prisma.categoria.findFirst({
        where: { id: categoriaId, activo: true },
        select: { id: true },
      });
      if (!categoria) {
        if (file) this.safeUnlink(file.path);
        return {
          status: 400,
          body: {
            error: 'Categoría inválida',
            message: 'La categoría seleccionada no existe',
          },
        };
      }

      let imagen: string | null = null;
      if (file) {
        imagen = `productos/${file.filename}`;
      } else if (
        imagen_url &&
        (String(imagen_url).startsWith('http://') ||
          String(imagen_url).startsWith('https://'))
      ) {
        imagen = imagen_url;
      }

      const creado = await this.prisma.producto.create({
        data: {
          nombre: nombre.trim(),
          descripcion: descripcion || null,
          precio: String(precio),
          categoria_id: categoriaId,
          imagen,
          stock: parseInt(String(stock || 0), 10) || 0,
          destacado: Boolean(destacado),
        },
        include: { categoria: { select: { nombre: true } } },
      });

      await this.notificaciones.broadcastNewProduct(creado.id, creado.nombre);

      return {
        status: 201,
        body: {
          message: 'Producto creado exitosamente',
          producto: {
            ...creado,
            precio: parseFloat(creado.precio.toString()),
            categoria_nombre: creado.categoria?.nombre ?? null,
          },
        },
      };
    } catch (error) {
      if (params.file) this.safeUnlink(params.file.path);
      return {
        status: 500,
        body: { error: 'Error interno', message: 'Error al crear producto' },
      };
    }
  }

  async actualizarProducto(
    id: number,
    params: { file?: Express.Multer.File | null; body: UpdateProductoDto },
  ) {
    const { file, body } = params;
    try {
      const existente = await this.prisma.producto.findUnique({
        where: { id },
      });
      if (!existente) {
        if (file) this.safeUnlink(file.path);
        return {
          status: 404,
          body: {
            error: 'Producto no encontrado',
            message: 'El producto a actualizar no existe',
          },
        };
      }

      const updateData: any = {};
      const {
        nombre,
        descripcion,
        precio,
        categoria_id,
        stock,
        destacado,
        imagen_url,
      } = body;
      if (nombre !== undefined) {
        if (String(nombre).trim().length < 2) {
          if (file) this.safeUnlink(file.path);
          return {
            status: 400,
            body: {
              error: 'Datos inválidos',
              message: 'El nombre debe tener al menos 2 caracteres',
            },
          };
        }
        updateData.nombre = String(nombre).trim();
      }
      if (descripcion !== undefined) updateData.descripcion = descripcion;
      if (precio !== undefined) {
        if (
          isNaN(parseFloat(String(precio))) ||
          parseFloat(String(precio)) < 0
        ) {
          if (file) this.safeUnlink(file.path);
          return {
            status: 400,
            body: {
              error: 'Datos inválidos',
              message: 'El precio debe ser un número positivo',
            },
          };
        }
        updateData.precio = String(precio);
      }
      if (categoria_id !== undefined) {
        const categoriaId = parseInt(String(categoria_id), 10);
        if (!categoriaId || categoriaId < 1) {
          if (file) this.safeUnlink(file.path);
          return {
            status: 400,
            body: {
              error: 'Categoría inválida',
              message: 'La categoría seleccionada no existe',
            },
          };
        }
        const categoria = await this.prisma.categoria.findFirst({
          where: { id: categoriaId, activo: true },
          select: { id: true },
        });
        if (!categoria) {
          if (file) this.safeUnlink(file.path);
          return {
            status: 400,
            body: {
              error: 'Categoría inválida',
              message: 'La categoría seleccionada no existe',
            },
          };
        }
        updateData.categoria_id = categoriaId;
      }
      if (stock !== undefined) updateData.stock = parseInt(String(stock), 10);
      if (destacado !== undefined) updateData.destacado = Boolean(destacado);

      // Manejar imagen
      if (file) {
        const nuevaImagen = `productos/${file.filename}`;
        updateData.imagen = nuevaImagen;
        // Eliminar imagen anterior si era local
        if (existente.imagen && !String(existente.imagen).startsWith('http')) {
          const rutaAnterior = path.join(
            process.cwd(),
            'uploads',
            String(existente.imagen),
          );
          this.safeUnlink(rutaAnterior);
        }
      } else if (imagen_url !== undefined) {
        const nuevaImagen = imagen_url || null;
        updateData.imagen = nuevaImagen;
        if (existente.imagen && !String(existente.imagen).startsWith('http')) {
          const rutaAnterior = path.join(
            process.cwd(),
            'uploads',
            String(existente.imagen),
          );
          this.safeUnlink(rutaAnterior);
        }
      }

      if (Object.keys(updateData).length === 0) {
        if (file) this.safeUnlink(file.path);
        return {
          status: 400,
          body: {
            error: 'Sin cambios',
            message: 'No se proporcionaron datos para actualizar',
          },
        };
      }

      const actualizado = await this.prisma.producto.update({
        where: { id },
        data: updateData,
        include: { categoria: { select: { nombre: true } } },
      });
      return {
        status: 200,
        body: {
          message: 'Producto actualizado exitosamente',
          producto: {
            ...actualizado,
            precio: parseFloat(actualizado.precio.toString()),
            categoria_nombre: actualizado.categoria?.nombre ?? null,
          },
        },
      };
    } catch (error) {
      if (params.file) this.safeUnlink(params.file.path);
      return {
        status: 500,
        body: {
          error: 'Error interno',
          message: 'Error al actualizar producto',
        },
      };
    }
  }

  async eliminarProducto(id: number) {
    const existente = await this.prisma.producto.findUnique({ where: { id } });
    if (!existente) {
      return {
        status: 404,
        body: {
          error: 'Producto no encontrado',
          message: 'El producto a eliminar no existe',
        },
      };
    }
    await this.prisma.producto.update({
      where: { id },
      data: { activo: false },
    });
    return {
      status: 200,
      body: { message: 'Producto eliminado exitosamente' },
    };
  }

  private safeUnlink(p: string) {
    try {
      if (p && fs.existsSync(p)) fs.unlinkSync(p);
    } catch (e) {}
  }
}
