import { Controller, Get, Query, UseGuards } from '@nestjs/common';
import { AuthGuard } from '@nestjs/passport';
import { ReportesService } from './reportes.service';
import { AdminGuard } from '../auth/guards/admin.guard';

@Controller('reportes')
export class ReportesController {
  constructor(private readonly service: ReportesService) {}

  @UseGuards(AuthGuard('jwt'), AdminGuard)
  @Get('admin/ventas-diarias')
  async ventasDiarias(
    @Query('desde') desde?: string,
    @Query('hasta') hasta?: string,
  ) {
    const result = await this.service.ventasDiarias({ desde, hasta });
    return result.body;
  }

  @UseGuards(AuthGuard('jwt'), AdminGuard)
  @Get('admin/ventas-semanales')
  async ventasSemanales(
    @Query('desde') desde?: string,
    @Query('hasta') hasta?: string,
  ) {
    const result = await this.service.ventasSemanales({ desde, hasta });
    return result.body;
  }

  // NUEVO: ventas mensuales
  @UseGuards(AuthGuard('jwt'), AdminGuard)
  @Get('admin/ventas-mensuales')
  async ventasMensuales(
    @Query('desde') desde?: string,
    @Query('hasta') hasta?: string,
  ) {
    const result = await this.service.ventasMensuales({ desde, hasta });
    return result.body;
  }

  @UseGuards(AuthGuard('jwt'), AdminGuard)
  @Get('admin/top-productos')
  async topProductos(
    @Query('desde') desde?: string,
    @Query('hasta') hasta?: string,
    @Query('limite') limite?: string,
  ) {
    const result = await this.service.topProductos({
      desde,
      hasta,
      limite: limite ? parseInt(limite, 10) : undefined,
    });
    return result.body;
  }

  @UseGuards(AuthGuard('jwt'), AdminGuard)
  @Get('admin/top-categorias')
  async topCategorias(
    @Query('desde') desde?: string,
    @Query('hasta') hasta?: string,
    @Query('limite') limite?: string,
  ) {
    const result = await this.service.topCategorias({
      desde,
      hasta,
      limite: limite ? parseInt(limite, 10) : undefined,
    });
    return result.body;
  }
}
