import { Module } from '@nestjs/common';
import { FacturacionController } from './facturacion.controller';
import { FacturacionService } from './facturacion.service';
import { PrismaModule } from '../prisma/prisma.module';
import { AdminGuard } from '../auth/guards/admin.guard';

@Module({
  imports: [PrismaModule],
  controllers: [FacturacionController],
  providers: [FacturacionService, AdminGuard],
})
export class FacturacionModule {}
