import { NestFactory } from '@nestjs/core';
import { AppModule } from './app.module';
import { ValidationPipe, BadRequestException } from '@nestjs/common';
import { SwaggerModule, DocumentBuilder } from '@nestjs/swagger';
import { Response } from 'express';
import * as yaml from 'js-yaml';

// eslint-disable-next-line @typescript-eslint/no-explicit-any
function exceptionFactory(errors: any): any {
  // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access,@typescript-eslint/no-unsafe-call
  const mensajes = errors.flatMap((err: any) => {
    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
    if (err.constraints) {
      // eslint-disable-next-line @typescript-eslint/no-unsafe-argument,@typescript-eslint/no-unsafe-member-access
      return Object.values(err.constraints).map((m: any) =>
        (m as string)
          .replace('should not exist', 'no debe existir')
          .replace('must be', 'debe ser')
          .replace('must be a string', 'debe ser un texto')
          .replace('must be a number', 'debe ser un número'),
      );
    }
    return [];
  });
  return new BadRequestException(mensajes);
}

async function bootstrap() {
  const app = await NestFactory.create(AppModule);
  app.enableCors({
    origin: '*',
    credentials: true,
  });

  app.useGlobalPipes(
    new ValidationPipe({
      whitelist: true,
      forbidNonWhitelisted: false,
      transform: true,
      skipMissingProperties: false,

      exceptionFactory,
    }),
  );

  // Prefijo global para mantener rutas /api/*
  app.setGlobalPrefix('api');

  // Swagger (OpenAPI) configuration
  const config = new DocumentBuilder()
    .setTitle('Delicias API')
    .setDescription(
      'API de Delicias - Autenticación, Usuarios, Productos y Categorías',
    )
    .setVersion('1.0.0')
    .addBearerAuth()
    .build();
  const document = SwaggerModule.createDocument(app, config);
  SwaggerModule.setup('api/docs', app, document);

  // Exponer también el contrato en YAML
  const yamlDoc = yaml.dump(document);
  app.use('/api/docs-yaml', (req: any, res: Response) => {
    res.type('text/yaml').send(yamlDoc);
  });

  await app.listen(process.env.PORT ?? 5001);
}
bootstrap().catch((err) => {
  console.error('Error starting application:', err);
  process.exit(1);
});
