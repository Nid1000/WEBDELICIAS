import { Body, Controller, HttpCode, Post } from '@nestjs/common';
import { ApiTags, ApiOperation, ApiCreatedResponse } from '@nestjs/swagger';
import { ContactoService } from './contacto.service';
import { CreateContactoDto } from './dto/create-contacto.dto';

@ApiTags('contacto')
@Controller('contacto')
export class ContactoController {
  constructor(private readonly contactoService: ContactoService) {}

  @Post()
  @HttpCode(201)
  @ApiOperation({ summary: 'Enviar mensaje de contacto' })
  @ApiCreatedResponse({ description: 'Mensaje de contacto recibido' })
  async create(@Body() dto: CreateContactoDto) {
    const id = await this.contactoService.create(dto);
    return {
      ok: true,
      id,
      message: 'Mensaje recibido. ¡Gracias por contactarnos!',
    };
  }
}
