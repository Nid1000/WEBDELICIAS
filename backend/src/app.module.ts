import { Module } from '@nestjs/common';
import { ConfigModule } from '@nestjs/config';
import { ServeStaticModule } from '@nestjs/serve-static';
import { AppController } from './app.controller';
import { AppService } from './app.service';
import { PrismaModule } from './prisma/prisma.module';
import { AuthModule } from './auth/auth.module';
import { CategoriasModule } from './categorias/categorias.module';
import { ProductosModule } from './productos/productos.module';
import { UsuariosModule } from './usuarios/usuarios.module';
import { PedidosModule } from './pedidos/pedidos.module';
import { FacturacionModule } from './facturacion/facturacion.module';
import { ContactoModule } from './contacto/contacto.module';
import { ReportesModule } from './reportes/reportes.module';
import { NotificacionesModule } from './notificaciones/notificaciones.module';
import { getUploadsRoot } from './common/upload-path';

@Module({
  imports: [
    ConfigModule.forRoot({ isGlobal: true }),
    // Servir archivos estáticos (ej. imágenes) desde /uploads
    ServeStaticModule.forRoot({
      rootPath: getUploadsRoot(),
      serveRoot: '/uploads',
    }),
    PrismaModule,
    AuthModule,
    CategoriasModule,
    ProductosModule,
    UsuariosModule,
    PedidosModule,
    FacturacionModule,
    ContactoModule,
    ReportesModule,
    NotificacionesModule,
  ],
  controllers: [AppController],
  providers: [AppService],
})
export class AppModule {}
