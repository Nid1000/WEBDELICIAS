import {
  Body,
  Controller,
  Delete,
  Get,
  Param,
  ParseIntPipe,
  Post,
  Put,
  Query,
  UploadedFile,
  UseGuards,
  UseInterceptors,
} from '@nestjs/common';
import { ApiBearerAuth, ApiTags } from '@nestjs/swagger';
import { ProductosService } from './productos.service';
import { AuthGuard } from '@nestjs/passport';
import { AdminGuard } from '../auth/guards/admin.guard';
import { FileInterceptor } from '@nestjs/platform-express';
import { diskStorage, FileFilterCallback } from 'multer';
import type { Express } from 'express';
import * as path from 'path';
import * as fs from 'fs';
import { CreateProductoDto } from './dto/create-producto.dto';
import { UpdateProductoDto } from './dto/update-producto.dto';
import { getUploadSubdir } from '../common/upload-path';

@Controller('productos')
@ApiTags('Productos')
export class ProductosController {
  constructor(private service: ProductosService) {}

  @Get()
  async getProductos(
    @Query('categoria') categoria?: string,
    @Query('destacado') destacado?: string,
    @Query('buscar') buscar?: string,
    @Query('limite') limite?: string,
    @Query('pagina') pagina?: string,
  ) {
    return this.service.listarProductos({
      categoria,
      destacado,
      buscar,
      limite,
      pagina,
    });
  }

  @Get(':id')
  async getProducto(@Param('id', ParseIntPipe) id: number) {
    return this.service.obtenerProducto(id);
  }

  // Configuración de Multer para subida de imágenes
  private static getMulterOptions() {
    return {
      storage: diskStorage({
        destination: (req, file, cb) => {
          const uploadPath = getUploadSubdir('productos');
          if (!fs.existsSync(uploadPath)) {
            fs.mkdirSync(uploadPath, { recursive: true });
          }
          cb(null, uploadPath);
        },
        filename: (req, file, cb) => {
          const uniqueSuffix =
            Date.now() + '-' + Math.round(Math.random() * 1e9);
          const ext = path.extname(file.originalname);
          cb(null, 'producto-' + uniqueSuffix + ext);
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

  @Post()
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  @ApiBearerAuth()
  @UseInterceptors(
    FileInterceptor('imagen', ProductosController.getMulterOptions()),
  )
  async crearProducto(
    @UploadedFile() file: Express.Multer.File,
    @Body() body: CreateProductoDto,
  ) {
    const result = await this.service.crearProducto({ file, body });
    return { statusCode: result.status, ...result.body };
  }

  @Put(':id')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  @ApiBearerAuth()
  @UseInterceptors(
    FileInterceptor('imagen', ProductosController.getMulterOptions()),
  )
  async actualizarProducto(
    @Param('id', ParseIntPipe) id: number,
    @UploadedFile() file: Express.Multer.File,
    @Body() body: UpdateProductoDto,
  ) {
    const result = await this.service.actualizarProducto(id, { file, body });
    return { statusCode: result.status, ...result.body };
  }

  @Delete(':id')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  @ApiBearerAuth()
  async eliminarProducto(@Param('id', ParseIntPipe) id: number) {
    const result = await this.service.eliminarProducto(id);
    return { statusCode: result.status, ...result.body };
  }
}
