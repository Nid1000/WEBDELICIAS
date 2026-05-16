import { ApiProperty } from '@nestjs/swagger';
import { IsEmail, IsOptional, IsString, Length } from 'class-validator';

export class CreateContactoDto {
  @ApiProperty({ example: 'Juan Pérez' })
  @IsString()
  @Length(2, 80)
  nombre: string;

  @ApiProperty({ example: 'juan@example.com' })
  @IsEmail()
  email: string;

  @ApiProperty({ example: '993560096', required: false })
  @IsOptional()
  @IsString()
  @Length(6, 20)
  telefono?: string;

  @ApiProperty({ example: 'Quiero cotizar una torta personalizada' })
  @IsString()
  @Length(5, 1000)
  mensaje: string;
}
