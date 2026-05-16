import {
  Body,
  Controller,
  Delete,
  Get,
  Param,
  ParseIntPipe,
  Patch,
  Post,
  Put,
  Query,
  UploadedFile,
  UseGuards,
  UseInterceptors,
} from '@nestjs/common';
import { ApiBearerAuth, ApiTags } from '@nestjs/swagger';
import { CategoriasService } from './categorias.service';
import { AuthGuard } from '@nestjs/passport';
import { AdminGuard } from '../auth/guards/admin.guard';
import { CreateCategoriaDto } from './dto/create-categoria.dto';
import { UpdateCategoriaDto } from './dto/update-categoria.dto';
import { FileInterceptor } from '@nestjs/platform-express';
import { diskStorage, FileFilterCallback } from 'multer';
import type { Express } from 'express';
import * as path from 'path';
import * as fs from 'fs';
import { getUploadSubdir } from '../common/upload-path';

@Controller('categorias')
@ApiTags('Categorias')
export class CategoriasController {
  constructor(private service: CategoriasService) {}

  @Get()
  async getCategorias(@Query('activo') activo?: string) {
    return this.service.findAll(activo);
  }

  @Get(':id')
  async getCategoria(@Param('id', ParseIntPipe) id: number) {
    return this.service.findById(id);
  }

  @Get(':id/productos')
  async getProductosCategoria(
    @Param('id', ParseIntPipe) id: number,
    @Query('pagina') pagina = '1',
    @Query('limite') limite = '20',
  ) {
    return this.service.findProductosByCategoria(
      id,
      parseInt(pagina, 10),
      parseInt(limite, 10),
    );
  }

  // ADMIN: listar categorias con paginación y búsqueda
  @Get('admin/todos')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  async adminList(
    @Query('limite') limite?: string,
    @Query('pagina') pagina?: string,
    @Query('buscar') buscar?: string,
    @Query('activo') activo?: string,
  ) {
    const result = await this.service.adminList({
      limite: parseInt(limite || '20', 10),
      pagina: parseInt(pagina || '1', 10),
      buscar,
      activo: activo !== undefined ? activo === 'true' : undefined,
    });
    return { statusCode: result.status, ...result.body };
  }

  // ADMIN: obtener categoria por id (incluye inactivas)
  @Get('admin/:id')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  async adminGet(@Param('id', ParseIntPipe) id: number) {
    const result = await this.service.adminGet(id);
    return { statusCode: result.status, ...result.body };
  }

  // Multer options para imágenes de categorías
  private static getMulterOptions() {
    return {
      storage: diskStorage({
        destination: (req, file, cb) => {
          const uploadPath = getUploadSubdir('categorias');
          if (!fs.existsSync(uploadPath)) {
            fs.mkdirSync(uploadPath, { recursive: true });
          }
          cb(null, uploadPath);
        },
        filename: (req, file, cb) => {
          const uniqueSuffix =
            Date.now() + '-' + Math.round(Math.random() * 1e9);
          const ext = path.extname(file.originalname);
          cb(null, 'categoria-' + uniqueSuffix + ext);
        },
      }),
      limits: { fileSize: parseInt(process.env.MAX_FILE_SIZE || '5242880') },
      fileFilter: (
        req: any,
        file: Express.Multer.File,
        cb: FileFilterCallback,
      ) => {
        const allowedTypes = /jpeg|jpg|png|gif|webp/;
        const extname = allowedTypes.test(
          path.extname(file.originalname).toLowerCase(),
        );
        const mimetype = allowedTypes.test(file.mimetype);
        if (mimetype && extname) return cb(null, true);
        cb(
          Object.assign(
            new Error('Solo se permiten imágenes (jpeg, jpg, png, gif, webp)'),
            { status: 400 },
          ),
        );
      },
    };
  }

  // ADMIN: crear categoría
  @Post('admin')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  async adminCrear(@Body() body: CreateCategoriaDto) {
    const result = await this.service.adminCrear(body);
    return { statusCode: result.status, ...result.body };
  }

  // ADMIN: actualizar categoría (sin archivo)
  @Put('admin/:id')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  async adminActualizar(
    @Param('id', ParseIntPipe) id: number,
    @Body() body: UpdateCategoriaDto,
  ) {
    const result = await this.service.adminActualizar(id, body);
    return { statusCode: result.status, ...result.body };
  }

  // ADMIN: actualizar imagen de categoría (subida local)
  @Put('admin/:id/imagen')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  @ApiBearerAuth()
  @UseInterceptors(
    FileInterceptor('imagen', CategoriasController.getMulterOptions()),
  )
  async adminActualizarImagen(
    @Param('id', ParseIntPipe) id: number,
    @UploadedFile() file: Express.Multer.File,
  ) {
    const result = await this.service.adminActualizarImagen(id, file);
    return { statusCode: result.status, ...result.body };
  }

  // ADMIN: activar/desactivar categoría
  @Patch('admin/:id/estado')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  async adminEstado(
    @Param('id', ParseIntPipe) id: number,
    @Body() body: { activo: boolean },
  ) {
    const result = await this.service.adminActualizarEstado(id, body.activo);
    return { statusCode: result.status, ...result.body };
  }

  // ADMIN: eliminar (soft delete = activo: false)
  @Delete('admin/:id')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  async adminEliminar(@Param('id', ParseIntPipe) id: number) {
    const result = await this.service.adminEliminar(id);
    return { statusCode: result.status, ...result.body };
  }
}
