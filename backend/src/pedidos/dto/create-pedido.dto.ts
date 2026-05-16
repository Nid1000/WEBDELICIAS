import {
  IsArray,
  IsDateString,
  IsEnum,
  IsNotEmpty,
  IsOptional,
  IsString,
  ValidateNested,
} from 'class-validator';
import { Type } from 'class-transformer';

class ProductoItemDto {
  @IsNotEmpty()
  id: number;

  @IsNotEmpty()
  cantidad: number;
}

export class CreatePedidoDto {
  @IsArray({ message: 'Los productos deben llegar en un arreglo' })
  @ValidateNested({ each: true })
  @Type(() => ProductoItemDto)
  productos: ProductoItemDto[];

  @IsOptional()
  @IsDateString(
    {},
    { message: 'La fecha de entrega debe tener formato ISO (YYYY-MM-DD)' },
  )
  fecha_entrega?: string;

  @IsOptional()
  @IsString({ message: 'La dirección debe ser un texto' })
  direccion_entrega?: string;

  @IsOptional()
  @IsString({ message: 'El distrito debe ser un texto' })
  distrito_entrega?: string;

  @IsOptional()
  @IsString({ message: 'El número de casa debe ser un texto' })
  numero_casa_entrega?: string;

  @IsOptional()
  direccion_id?: number;

  @IsOptional()
  @IsString({ message: 'El teléfono de contacto debe ser un texto' })
  telefono_contacto?: string;

  @IsOptional()
  @IsString({ message: 'Las notas deben ser un texto' })
  notas?: string;

  @IsOptional()
  pago?: {
    metodo?: 'card' | 'cash';
    tarjeta?: { numero: string; nombre: string; exp: string; cvv: string };
  };
}
