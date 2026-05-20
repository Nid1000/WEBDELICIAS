import {
  Body,
  Controller,
  Get,
  Post,
  UseGuards,
  Req,
  Query,
} from '@nestjs/common';
import { ApiBearerAuth, ApiTags } from '@nestjs/swagger';
import { AuthGuard } from '@nestjs/passport';
import { UsuarioGuard } from '../auth/guards/usuario.guard';
import { AdminGuard } from '../auth/guards/admin.guard';
import { FacturacionService } from './facturacion.service';
import { EmitirDto } from './dto/emitir.dto';

@Controller('facturacion')
@ApiTags('Facturación')
export class FacturacionController {
  constructor(private service: FacturacionService) {}

  @Post('emitir')
  @UseGuards(AuthGuard('jwt'), UsuarioGuard)
  @ApiBearerAuth()
  async emitir(@Body() body: EmitirDto, @Req() req: any) {
    const decolectaToken =
      (req.headers['x-decolecta-token'] as string | undefined) ||
      process.env.DECOLECTA_TOKEN ||
      undefined;
    const result = await this.service.emitir(req.user.id, body, decolectaToken);
    return { statusCode: result.status, ...result.body };
  }

  @Get('mis-comprobantes')
  @UseGuards(AuthGuard('jwt'), UsuarioGuard)
  @ApiBearerAuth()
  async misComprobantes(@Req() req: any) {
    const result = await this.service.misComprobantes(req.user.id);
    return { statusCode: result.status, ...result.body };
  }

  // ADMIN: listar todos los comprobantes emitidos
  @Get('admin/comprobantes')
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  @ApiBearerAuth()
  async adminComprobantes(
    @Query('pagina') pagina?: string,
    @Query('limite') limite?: string,
    @Query('tipo') tipo?: 'boleta' | 'factura',
    @Query('estado') estado?: string,
  ) {
    const p = pagina ? parseInt(pagina, 10) : undefined;
    const l = limite ? parseInt(limite, 10) : undefined;
    const result = await this.service.adminComprobantes({
      pagina: p,
      limite: l,
      tipo,
      estado,
    });
    return { statusCode: result.status, ...result.body };
  }

  // Consultas para autocompletado/validación
  @Get('consulta-dni')
  @UseGuards(AuthGuard('jwt'), UsuarioGuard)
  @ApiBearerAuth()
  async consultaDni(@Query('numero') numero: string, @Req() req: any) {
    const decolectaToken =
      (req.headers['x-decolecta-token'] as string | undefined) ||
      process.env.DECOLECTA_TOKEN ||
      undefined;
    const result = await this.service.consultaReniecDni(numero, decolectaToken);
    return { statusCode: result.status, ...result.body };
  }

  @Get('consulta-ruc')
  @UseGuards(AuthGuard('jwt'), UsuarioGuard)
  @ApiBearerAuth()
  async consultaRuc(@Query('numero') numero: string, @Req() req: any) {
    const decolectaToken =
      (req.headers['x-decolecta-token'] as string | undefined) ||
      process.env.DECOLECTA_TOKEN ||
      undefined;
    const result = await this.service.consultaSunatRuc(numero, decolectaToken);
    return { statusCode: result.status, ...result.body };
  }
}
