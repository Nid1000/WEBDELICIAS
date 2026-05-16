import { Injectable, Logger } from '@nestjs/common';
import { CreateContactoDto } from './dto/create-contacto.dto';

@Injectable()
export class ContactoService {
  private readonly logger = new Logger(ContactoService.name);

  async create(dto: CreateContactoDto): Promise<string> {
    // Por ahora, solo registramos el mensaje y devolvemos un ID ficticio.
    this.logger.log(
      `Nuevo contacto: ${dto.nombre} <${dto.email}> tel:${dto.telefono ?? '-'} - ${dto.mensaje}`,
    );
    // Si más adelante se desea persistir, integrar Prisma o enviar correo.
    const id = `${Date.now()}`;
    return id;
  }
}
