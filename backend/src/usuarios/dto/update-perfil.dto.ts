import {
  IsOptional,
  IsString,
  MinLength,
  Matches,
} from 'class-validator';
import { ApiPropertyOptional } from '@nestjs/swagger';

export class UpdatePerfilDto {
  @ApiPropertyOptional({ example: 'Luis' })
  @IsOptional()
  @IsString()
  @MinLength(2, { message: 'El nombre debe tener al menos 2 caracteres' })
  nombre?: string;

  @ApiPropertyOptional({ example: 'García' })
  @IsOptional()
  @IsString()
  @MinLength(2, { message: 'El apellido debe tener al menos 2 caracteres' })
  apellido?: string;

  @ApiPropertyOptional({ example: '987654321' })
  @IsOptional()
  @Matches(/^9\d{8}$/, {
    message: 'El teléfono debe tener 9 dígitos y empezar con 9',
  })
  telefono?: string;

  @ApiPropertyOptional({ example: 'Av. Siempre Viva 742' })
  @IsOptional()
  @IsString()
  @MinLength(5, { message: 'La dirección debe tener al menos 5 caracteres' })
  direccion?: string;

  @ApiPropertyOptional({ example: 'El Tambo' })
  @IsOptional()
  @IsString()
  @MinLength(2, { message: 'El distrito debe tener al menos 2 caracteres' })
  distrito?: string;

  @ApiPropertyOptional({ example: '350' })
  @IsOptional()
  @IsString()
  @MinLength(1, { message: 'El número de casa es obligatorio' })
  numero_casa?: string;
}
