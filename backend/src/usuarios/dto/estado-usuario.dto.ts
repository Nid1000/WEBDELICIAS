import { IsBoolean } from 'class-validator';
import { ApiProperty } from '@nestjs/swagger';

export class EstadoUsuarioDto {
  @ApiProperty({ example: true })
  @IsBoolean({ message: 'Estado activo debe ser verdadero o falso' })
  activo: boolean;
}
