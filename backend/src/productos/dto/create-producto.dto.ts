import {
  IsBoolean,
  IsInt,
  IsNotEmpty,
  IsNumber,
  IsOptional,
  IsString,
  IsUrl,
  Length,
  Min,
} from 'class-validator';
import { Type, Transform } from 'class-transformer';
import { ApiProperty, ApiPropertyOptional } from '@nestjs/swagger';

export class CreateProductoDto {
  @ApiProperty({ example: 'Pan artesanal' })
  @IsString()
  @Length(2, 200)
  @IsNotEmpty()
  nombre: string;

  @ApiPropertyOptional({ example: 'Pan horneado diariamente con masa madre' })
  @IsOptional()
  @IsString()
  @Length(0, 2000)
  descripcion?: string;

  @ApiProperty({ example: 12.5 })
  @IsNumber()
  @Min(0)
  @Type(() => Number)
  precio: number;

  @ApiProperty({ example: 1 })
  @IsInt()
  @Min(1)
  @Type(() => Number)
  categoria_id: number;

  @ApiPropertyOptional({ example: 100 })
  @IsOptional()
  @IsInt()
  @Min(0)
  @Type(() => Number)
  stock?: number;

  @ApiPropertyOptional({ example: false })
  @IsOptional()
  @IsBoolean()
  @Transform(({ value }) => {
    if (typeof value === 'string') {
      return value.toLowerCase() === 'true';
    }
    return Boolean(value);
  })
  destacado?: boolean;

  @ApiPropertyOptional({ example: 'https://via.placeholder.com/300.png' })
  @IsOptional()
  @IsUrl()
  imagen_url?: string;
}
