import {
  IsEmail,
  IsOptional,
  IsString,
  Length,
  Matches,
} from 'class-validator';
import { ApiProperty, ApiPropertyOptional } from '@nestjs/swagger';

export class RegisterUserDto {
  @ApiProperty({ example: 'Juan' })
  @IsString()
  @Length(2)
  nombre: string;

  @ApiProperty({ example: 'Pérez' })
  @IsString()
  @Length(2)
  apellido: string;

  @ApiProperty({ example: 'juan.perez@example.com' })
  @IsEmail({}, { message: 'Email inválido' })
  email: string;

  @ApiProperty({
    example: 'Secreta123',
    description: 'Al menos una mayúscula, una minúscula y un número',
  })
  @Matches(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/, {
    message:
      'La contraseña debe contener al menos una mayúscula, una minúscula y un número',
  })
  password: string;

  @ApiPropertyOptional({ example: '600123456' })
  @IsOptional()
  @IsString()
  telefono?: string;

  @ApiPropertyOptional({ example: 'Calle Falsa 123, Ciudad' })
  @IsOptional()
  @IsString()
  @Length(5)
  direccion?: string;

  @ApiPropertyOptional({ example: 'Huancayo' })
  @IsOptional()
  @IsString()
  @Length(2)
  distrito?: string;

  @ApiPropertyOptional({ example: '350' })
  @IsOptional()
  @IsString()
  @Length(1)
  numero_casa?: string;
}
