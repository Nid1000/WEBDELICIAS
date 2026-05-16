import {
  IsBoolean,
  IsInt,
  IsNumber,
  IsOptional,
  IsString,
  IsUrl,
  Length,
  Min,
} from 'class-validator';
import { Type, Transform } from 'class-transformer';
import { ApiPropertyOptional } from '@nestjs/swagger';

export class UpdateProductoDto {
  @ApiPropertyOptional({ example: 'Pan artesanal' })
  @IsOptional()
  @IsString()
  @Length(2, 200)
  nombre?: string;

  @ApiPropertyOptional({ example: 'Pan horneado diariamente con masa madre' })
  @IsOptional()
  @IsString()
  @Length(0, 2000)
  descripcion?: string;

  @ApiPropertyOptional({ example: 12.5 })
  @IsOptional()
  @IsNumber()
  @Min(0)
  @Type(() => Number)
  precio?: number;

  @ApiPropertyOptional({ example: 1 })
  @IsOptional()
  @IsInt()
  @Min(1)
  @Type(() => Number)
  categoria_id?: number;

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
  imagen_url?: string | null;
}
