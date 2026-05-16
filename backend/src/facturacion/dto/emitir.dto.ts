import { IsEnum, IsNotEmpty, IsNumber, IsString } from 'class-validator';

export class EmitirDto {
  @IsNumber()
  pedido_id: number;

  @IsEnum(['boleta', 'factura'])
  comprobante_tipo: 'boleta' | 'factura';

  @IsEnum(['DNI', 'RUC'])
  tipo_documento: 'DNI' | 'RUC';

  @IsString()
  @IsNotEmpty()
  numero_documento: string;
}
