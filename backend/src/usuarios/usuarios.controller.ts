import {
  Body,
  Controller,
  Get,
  Param,
  Patch,
  Put,
  Query,
  Req,
  UseGuards,
} from '@nestjs/common';
import { ApiBearerAuth, ApiTags } from '@nestjs/swagger';
import { UsuariosService } from './usuarios.service';
import { AuthGuard } from '@nestjs/passport';
import { UsuarioGuard } from '../auth/guards/usuario.guard';
import { AdminGuard } from '../auth/guards/admin.guard';
import { UpdatePerfilDto } from './dto/update-perfil.dto';
import { ChangePasswordDto } from './dto/change-password.dto';
import { EstadoUsuarioDto } from './dto/estado-usuario.dto';
import { AdminUpdateUsuarioDto } from './dto/admin-update-usuario.dto';

@Controller('usuarios')
@ApiTags('Usuarios')
@ApiBearerAuth()
export class UsuariosController {
  constructor(private usuariosService: UsuariosService) {}


  @Get('distritos-huancayo')
  async distritosHuancayo() {
    const result = await this.usuariosService.listarDistritosHuancayo();
    return { statusCode: result.status, ...result.body };
  }

  // Obtener perfil del usuario autenticado
  @Get('perfil')
  @UseGuards(AuthGuard('jwt'), UsuarioGuard)
  async perfil(@Req() req: any) {
    const result = await this.usuariosService.obtenerPerfil(req.user.id);
    return { statusCode: result.status, ...result.body };
  }

  // Actualizar perfil del usuario autenticado
  @Put('perfil')
  @UseGuards(AuthGuard('jwt'), UsuarioGuard)
  async actualizarPerfil(@Req() req: any, @Body() body: UpdatePerfilDto) {
    const result = await this.usuariosService.actualizarPerfil(
      req.user.id,
      body,
    );
    return { statusCode: result.status, ...result.body };
  }

  // Cambiar contraseña
  @Put('cambiar-password')
  @UseGuards(AuthGuard('jwt'), UsuarioGuard)
  async cambiarPassword(@Req() req: any, @Body() body: ChangePasswordDto) {
    if (body.confirmarPassword !== body.passwordNueva) {
      return {
        statusCode: 400,
        error: 'Datos inválidos',
        message: 'Las contraseñas no coinciden',
      };
    }
    const result = await this.usuariosService.cambiarPassword(
      req.user.id,
      body.passwordActual,
      body.passwordNueva,
    );
    return { statusCode: result.status, ...result.body };
  }

  // Estadísticas del usuario
  @Get('estadisticas')
  @UseGuards(AuthGuard('jwt'), UsuarioGuard)
  async estadisticas(@Req() req: any) {
    const result = await this.usuariosService.estadisticas(req.user.id);
    return { statusCode: result.status, ...result.body };
  }

  // ADMIN: listar usuarios
  @Get('admin/todos')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  async adminList(
    @Query('limite') limite?: string,
    @Query('pagina') pagina?: string,
    @Query('buscar') buscar?: string,
    @Query('activo') activo?: string,
  ) {
    const result = await this.usuariosService.adminList({
      limite: parseInt(limite || '20'),
      pagina: parseInt(pagina || '1'),
      buscar,
      activo: activo !== undefined ? activo === 'true' : undefined,
    });
    return { statusCode: result.status, ...result.body };
  }

  // ADMIN: obtener usuario por id
  @Get('admin/:id')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  async adminGet(@Param('id') id: string) {
    const result = await this.usuariosService.adminGet(parseInt(id));
    return { statusCode: result.status, ...result.body };
  }

  // ADMIN: activar/desactivar usuario
  @Patch('admin/:id/estado')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  async adminEstado(@Param('id') id: string, @Body() body: EstadoUsuarioDto) {
    const result = await this.usuariosService.adminActualizarEstado(
      parseInt(id),
      body.activo,
    );
    return { statusCode: result.status, ...result.body };
  }

  // ADMIN: actualizar datos del usuario
  @Put('admin/:id')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  async adminActualizar(
    @Param('id') id: string,
    @Body() body: AdminUpdateUsuarioDto,
  ) {
    const result = await this.usuariosService.adminActualizar(
      parseInt(id),
      body,
    );
    return { statusCode: result.status, ...result.body };
  }
}
