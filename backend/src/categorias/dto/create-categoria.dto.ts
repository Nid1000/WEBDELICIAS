import { IsOptional, IsString, Length } from 'class-validator';
import { ApiProperty, ApiPropertyOptional } from '@nestjs/swagger';

export class CreateCategoriaDto {
  @ApiProperty({ example: 'Galletas' })
  @IsString()
  @Length(2, 200)
  nombre!: string;

  @ApiPropertyOptional({
    example: 'Deliciosas galletas caseras',
    required: false,
  })
  @IsOptional()
  @IsString()
  @Length(0, 2000)
  descripcion?: string;

  @ApiPropertyOptional({
    example: 'categorias/categoria-12345.png',
    description: 'Ruta local en uploads o URL completa',
    required: false,
  })
  @IsOptional()
  @IsString()
  imagen?: string | null;
}
