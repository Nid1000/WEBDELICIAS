import { Module } from '@nestjs/common';
import { ProductosController } from './productos.controller';
import { ProductosService } from './productos.service';
import { AdminGuard } from '../auth/guards/admin.guard';
import { PrismaModule } from '../prisma/prisma.module';
import { NotificacionesModule } from '../notificaciones/notificaciones.module';

@Module({
  imports: [PrismaModule, NotificacionesModule],
  controllers: [ProductosController],
  providers: [ProductosService, AdminGuard],
})
export class ProductosModule {}
