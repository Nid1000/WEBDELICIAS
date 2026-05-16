import { Module } from '@nestjs/common';
import { ReportesController } from './reportes.controller';
import { ReportesService } from './reportes.service';
import { PrismaModule } from '../prisma/prisma.module';
import { AdminGuard } from '../auth/guards/admin.guard';

@Module({
  imports: [PrismaModule],
  controllers: [ReportesController],
  providers: [ReportesService, AdminGuard],
})
export class ReportesModule {}
