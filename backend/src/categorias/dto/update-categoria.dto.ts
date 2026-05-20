import { IsOptional, IsString, Length } from 'class-validator';
import { ApiPropertyOptional } from '@nestjs/swagger';

export class UpdateCategoriaDto {
  @ApiPropertyOptional({ example: 'Galletas' })
  @IsOptional()
  @IsString()
  @Length(2, 200)
  nombre?: string;

  @ApiPropertyOptional({ example: 'Deliciosas galletas caseras' })
  @IsOptional()
  @IsString()
  @Length(0, 2000)
  descripcion?: string | null;

  @ApiPropertyOptional({
    example: 'categorias/categoria-12345.png',
    description: 'Ruta local en uploads o URL completa',
  })
  @IsOptional()
  @IsString()
  imagen?: string | null;
}
