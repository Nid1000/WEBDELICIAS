import { Body, Controller, Get, Post, Query, Req, UseGuards } from '@nestjs/common';
import { ApiBearerAuth, ApiTags } from '@nestjs/swagger';
import { AuthGuard } from '@nestjs/passport';
import { UsuarioGuard } from '../auth/guards/usuario.guard';
import { AdminGuard } from '../auth/guards/admin.guard';
import { NotificacionesService } from './notificaciones.service';

@Controller('notificaciones')
@ApiTags('Notificaciones')
export class NotificacionesController {
  constructor(private service: NotificacionesService) {}

  @Get('pendientes')
  @UseGuards(AuthGuard('jwt'), UsuarioGuard)
  @ApiBearerAuth()
  async pendientes(
    @Req() req: any,
    @Query('canal') canal?: 'web' | 'mobile',
  ) {
    const channel = canal === 'web' ? 'web' : 'mobile';
    const items = await this.service.getPendingForUser(req.user.id, channel);
    return { statusCode: 200, notificaciones: items };
  }

  @Post('marcar-mostradas')
  @UseGuards(AuthGuard('jwt'), UsuarioGuard)
  @ApiBearerAuth()
  async marcarMostradas(
    @Req() req: any,
    @Body() body: { ids?: number[]; canal?: 'web' | 'mobile' },
  ) {
    const channel = body.canal === 'web' ? 'web' : 'mobile';
    await this.service.markShown(req.user.id, body.ids ?? [], channel);
    return { statusCode: 200, ok: true };
  }

  @Post('admin/enviar')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  @ApiBearerAuth()
  async enviarManual(
    @Body()
    body: {
      title?: string;
      message?: string;
      route?: string;
      targetId?: string | number | null;
      userId?: number | null;
      audience?: 'web' | 'mobile' | 'both';
    },
  ) {
    const title = body.title?.trim() ?? '';
    const message = body.message?.trim() ?? '';

    if (!title || !message) {
      return { statusCode: 400, message: 'Titulo y mensaje son obligatorios' };
    }

    if (body.userId && Number(body.userId) > 0) {
      await this.service.createForUser({
        userId: Number(body.userId),
        title,
        body: message,
        type: 'manual',
        audience: body.audience ?? 'both',
        route: body.route?.trim() || null,
        targetId: body.targetId ?? null,
      });
      return { statusCode: 200, ok: true, scope: 'user' };
    }

    await this.service.broadcastManual({
      title,
      body: message,
      route: body.route?.trim() || null,
      type: 'manual',
      audience: body.audience ?? 'both',
      targetId: body.targetId ?? null,
    });
    return { statusCode: 200, ok: true, scope: 'all' };
  }
}
