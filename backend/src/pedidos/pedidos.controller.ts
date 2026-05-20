import {
  Body,
  Controller,
  Get,
  Param,
  ParseIntPipe,
  Post,
  Put,
  Query,
  UseGuards,
  Req,
  Patch,
} from '@nestjs/common';
import { ApiBearerAuth, ApiTags } from '@nestjs/swagger';
import { AuthGuard } from '@nestjs/passport';
import { UsuarioGuard } from '../auth/guards/usuario.guard';
import { AdminGuard } from '../auth/guards/admin.guard';
import { PedidosService } from './pedidos.service';
import { CreatePedidoDto } from './dto/create-pedido.dto';

@Controller('pedidos')
@ApiTags('Pedidos')
export class PedidosController {
  constructor(private service: PedidosService) {}

  @Post()
  @UseGuards(AuthGuard('jwt'), UsuarioGuard)
  @ApiBearerAuth()
  async crear(@Body() body: CreatePedidoDto, @Req() req: any) {
    const result = await this.service.crearPedido(req.user.id, body);
    return { statusCode: result.status, ...result.body };
  }

  @Get('mis-pedidos')
  @UseGuards(AuthGuard('jwt'), UsuarioGuard)
  @ApiBearerAuth()
  async misPedidos(
    @Query('pagina') pagina?: string,
    @Query('limite') limite?: string,
    @Req() req?: any,
  ) {
    const p = pagina ? parseInt(pagina, 10) : undefined;
    const l = limite ? parseInt(limite, 10) : undefined;
    const result = await this.service.misPedidos(req!.user.id, {
      pagina: p,
      limite: l,
    });
    return { statusCode: result.status, ...result.body };
  }

  @Get(':id')
  @UseGuards(AuthGuard('jwt'), UsuarioGuard)
  @ApiBearerAuth()
  async obtener(@Param('id', ParseIntPipe) id: number, @Req() req: any) {
    const result = await this.service.obtenerPedido(req.user.id, id);
    return { statusCode: result.status, ...result.body };
  }

  @Put(':id/cancelar')
  @UseGuards(AuthGuard('jwt'), UsuarioGuard)
  @ApiBearerAuth()
  async cancelar(@Param('id', ParseIntPipe) id: number, @Req() req: any) {
    const result = await this.service.cancelarPedido(req.user.id, id);
    return { statusCode: result.status, ...result.body };
  }

  // ADMIN: listar todos los pedidos con filtros
  @Get('admin/todos')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  @ApiBearerAuth()
  async adminList(
    @Query('pagina') pagina?: string,
    @Query('limite') limite?: string,
    @Query('estado') estado?: string,
    @Query('desde') desde?: string,
    @Query('hasta') hasta?: string,
    @Query('buscar') buscar?: string,
  ) {
    const p = pagina ? parseInt(pagina, 10) : undefined;
    const l = limite ? parseInt(limite, 10) : undefined;
    const result = await this.service.adminList({
      pagina: p,
      limite: l,
      estado,
      desde,
      hasta,
      buscar,
    });
    return { statusCode: result.status, ...result.body };
  }

  // ADMIN: obtener detalle de un pedido por id
  @Get('admin/:id')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  @ApiBearerAuth()
  async adminGet(@Param('id', ParseIntPipe) id: number) {
    const result = await this.service.adminGet(id);
    return { statusCode: result.status, ...result.body };
  }

  // ADMIN: actualizar estado del pedido
  @Patch('admin/:id/estado')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  @ApiBearerAuth()
  async adminEstado(
    @Param('id', ParseIntPipe) id: number,
    @Body() body: { estado: 'pendiente' | 'listo' | 'entregado' | 'cancelado' },
  ) {
    const result = await this.service.adminActualizarEstado(id, body.estado);
    return { statusCode: result.status, ...result.body };
  }

  // ADMIN: actualizar fecha de entrega
  @Put('admin/:id/fecha-entrega')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  @ApiBearerAuth()
  async adminFechaEntrega(
    @Param('id', ParseIntPipe) id: number,
    @Body() body: { fecha_entrega: string | null },
  ) {
    const result = await this.service.adminActualizarFechaEntrega(
      id,
      body.fecha_entrega,
    );
    return { statusCode: result.status, ...result.body };
  }
}
