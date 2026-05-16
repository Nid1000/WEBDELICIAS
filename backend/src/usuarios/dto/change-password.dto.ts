import { IsNotEmpty, MinLength } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';

export class ChangePasswordDto {
  @ApiProperty({ example: 'Secreta123' })
  @IsNotEmpty({ message: 'Contraseña actual requerida' })
  passwordActual: string;

  @ApiProperty({ example: 'NuevaSecreta123' })
  @MinLength(6, {
    message: 'La nueva contraseña debe tener al menos 6 caracteres',
  })
  passwordNueva: string;

  @ApiProperty({ example: 'NuevaSecreta123' })
  @IsNotEmpty({ message: 'Confirmar contraseña es requerido' })
  confirmarPassword: string;
}
