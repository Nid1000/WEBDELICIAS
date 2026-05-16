import { Module } from '@nestjs/common';
import { UsuariosService } from './usuarios.service';
import { UsuariosController } from './usuarios.controller';
import { UsuarioGuard } from '../auth/guards/usuario.guard';
import { AdminGuard } from '../auth/guards/admin.guard';

@Module({
  controllers: [UsuariosController],
  providers: [UsuariosService, UsuarioGuard, AdminGuard],
})
export class UsuariosModule {}
