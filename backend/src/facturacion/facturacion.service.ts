import { Injectable } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import * as fs from 'fs';
import * as path from 'path';
import PDFDocument from 'pdfkit';
import sharp from 'sharp';
import { getUploadSubdir } from '../common/upload-path';

@Injectable()
export class FacturacionService {
  constructor(private prisma: PrismaService) {}

  private readonly decolectaBaseUrl =
    process.env.DECOLECTA_BASE_URL || 'https://api.decolecta.com/v1';

  private ensureFolder() {
    const folder = getUploadSubdir('comprobantes');
    if (!fs.existsSync(folder)) fs.mkdirSync(folder, { recursive: true });
    return folder;
  }

  private toFloat(n: any): number {
    try {
      return parseFloat(String(n));
    } catch {
      return 0;
    }
  }

  private async fetchReniecDni(
    dni: string,
    token?: string,
  ): Promise<any | null> {
    if (!token) return null;
    const url = `${this.decolectaBaseUrl}/reniec/dni?numero=${encodeURIComponent(dni)}`;
    try {
      const resp = await fetch(url, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
      });
      if (!resp.ok) return null;
      return await resp.json();
    } catch {
      return null;
    }
  }

  private async fetchSunatRuc(
    ruc: string,
    token?: string,
  ): Promise<any | null> {
    if (!token) return null;
    const url = `${this.decolectaBaseUrl}/sunat/ruc/full?numero=${encodeURIComponent(ruc)}`;
    try {
      const resp = await fetch(url, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
      });
      if (!resp.ok) return null;
      return await resp.json();
    } catch {
      return null;
    }
  }

  private async nextNumeroComprobante(
    tipo: 'boleta' | 'factura',
    serie: string,
  ) {
    const row = await this.prisma.$transaction(async (tx) => {
      await tx.comprobanteSerie.upsert({
        where: { uk_tipo_serie: { tipo, serie } },
        update: {},
        create: { tipo, serie, correlativo: 0 },
      });

      return tx.comprobanteSerie.update({
        where: { uk_tipo_serie: { tipo, serie } },
        data: { correlativo: { increment: 1 } },
      });
    });

    return {
      numero: row.correlativo,
      numeroFormateado: `${serie}-${String(row.correlativo).padStart(8, '0')}`,
    };
  }

  async consultaReniecDni(numero: string, decolectaToken?: string) {
    if (!numero || numero.length !== 8) {
      return { status: 400, body: { message: 'El DNI debe tener 8 dígitos' } };
    }
    const data = await this.fetchReniecDni(numero, decolectaToken);
    if (!data) {
      return {
        status: 404,
        body: { message: 'No se encontró información del DNI en RENIEC' },
      };
    }
    return { status: 200, body: { dni: numero, data } };
  }

  async consultaSunatRuc(numero: string, decolectaToken?: string) {
    if (!numero || numero.length !== 11) {
      return { status: 400, body: { message: 'El RUC debe tener 11 dígitos' } };
    }
    const data = await this.fetchSunatRuc(numero, decolectaToken);
    if (!data) {
      return {
        status: 404,
        body: { message: 'No se encontró información del RUC en SUNAT' },
      };
    }
    return { status: 200, body: { ruc: numero, data } };
  }

  async emitir(
    usuarioId: number,
    params: {
      pedido_id: number;
      comprobante_tipo: 'boleta' | 'factura';
      tipo_documento: 'DNI' | 'RUC';
      numero_documento: string;
    },
    decolectaToken?: string,
  ) {
    const pedido = await this.prisma.pedido.findFirst({
      where: { id: params.pedido_id, usuario_id: usuarioId },
      include: { detalles: true, usuario: true, comprobantes: true },
    });
    if (!pedido) {
      return {
        status: 404,
        body: {
          error: 'Pedido no encontrado',
          message: 'No se encontró el pedido para emitir comprobante',
        },
      };
    }

    const comprobanteExistente = pedido.comprobantes.find(
      (c) => c.tipo === params.comprobante_tipo,
    );
    if (comprobanteExistente) {
      return {
        status: 200,
        body: {
          message: 'El comprobante ya fue emitido previamente',
          comprobante: {
            id: comprobanteExistente.id,
            tipo: comprobanteExistente.tipo,
            serie: comprobanteExistente.serie,
            numero: comprobanteExistente.numero,
            numero_formateado: comprobanteExistente.numero_formateado,
            estado: 'emitido',
            pedido_id: pedido.id,
            total: this.toFloat(pedido.total),
            created_at: comprobanteExistente.created_at,
          },
          archivos: {
            pdf: comprobanteExistente.mime === 'application/pdf'
              ? `/${comprobanteExistente.archivo_ruta.replace(/\\/g, '/')}`
              : `/uploads/${path.join('comprobantes', `pedido-${pedido.id}-${comprobanteExistente.serie}-${String(comprobanteExistente.numero).padStart(8, '0')}.pdf`).replace(/\\/g, '/')}`,
            xml: `/uploads/${path.join('comprobantes', `pedido-${pedido.id}-${comprobanteExistente.serie}-${String(comprobanteExistente.numero).padStart(8, '0')}.xml`).replace(/\\/g, '/')}`,
            img: `/uploads/${path.join('comprobantes', `pedido-${pedido.id}-${comprobanteExistente.serie}-${String(comprobanteExistente.numero).padStart(8, '0')}.png`).replace(/\\/g, '/')}`,
          },
        },
      };
    }

    if (
      params.comprobante_tipo === 'factura' &&
      params.tipo_documento !== 'RUC'
    ) {
      return {
        status: 400,
        body: {
          error: 'Documento inválido',
          message: 'Para FACTURA, el documento debe ser RUC',
        },
      };
    }
    if (
      params.tipo_documento === 'DNI' &&
      params.numero_documento.length !== 8
    ) {
      return {
        status: 400,
        body: {
          error: 'Documento inválido',
          message: 'El DNI debe tener 8 dígitos',
        },
      };
    }
    if (
      params.tipo_documento === 'RUC' &&
      params.numero_documento.length !== 11
    ) {
      return {
        status: 400,
        body: {
          error: 'Documento inválido',
          message: 'El RUC debe tener 11 dígitos',
        },
      };
    }

    const folder = this.ensureFolder();
    const serie = params.comprobante_tipo === 'boleta' ? 'B001' : 'F001';
    const correlativo = await this.nextNumeroComprobante(
      params.comprobante_tipo,
      serie,
    );
    const numero = correlativo.numero;
    const numeroStr = String(numero).padStart(8, '0');
    const numeroFormateado = correlativo.numeroFormateado;

    const fileBase = `pedido-${pedido.id}-${serie}-${numeroStr}`;
    const pdfRel = path.join('comprobantes', `${fileBase}.pdf`);
    const xmlRel = path.join('comprobantes', `${fileBase}.xml`);
    const imgRel = path.join('comprobantes', `${fileBase}.png`);
    const pdfAbs = path.join(folder, `${fileBase}.pdf`);
    const xmlAbs = path.join(folder, `${fileBase}.xml`);
    const imgAbs = path.join(folder, `${fileBase}.png`);

    let identidad: any | null = null;
    let identidadVerificada = false;
    if (params.tipo_documento === 'DNI') {
      identidad = await this.fetchReniecDni(
        params.numero_documento,
        decolectaToken,
      );
      identidadVerificada = !!identidad;
    } else {
      identidad = await this.fetchSunatRuc(
        params.numero_documento,
        decolectaToken,
      );
      identidadVerificada = !!identidad;
    }

    const total = this.toFloat(pedido.total);
    const opGravada = Number(total.toFixed(2));
    const igv = 0;

    try {
      const doc = new PDFDocument({ size: 'A4', margin: 50 });
      const pdfStream = fs.createWriteStream(pdfAbs);
      doc.pipe(pdfStream);

      doc.fontSize(18).text('Comprobante electrónico', { align: 'right' });
      doc.moveDown(0.5);
      doc.fontSize(12).text(`Tipo: ${params.comprobante_tipo.toUpperCase()}`);
      doc.text(`Serie: ${serie}`);
      doc.text(`Número: ${numeroStr}`);
      doc.text(`Correlativo: ${numeroFormateado}`);
      doc.moveDown(0.5);

      if (params.tipo_documento === 'DNI') {
        doc.fontSize(12).text(`Documento: DNI ${params.numero_documento}`);
        doc.text(
          `Cliente: ${[identidad?.first_name, identidad?.first_last_name, identidad?.second_last_name].filter(Boolean).join(' ') || `${pedido.usuario?.nombre ?? ''} ${pedido.usuario?.apellido ?? ''}`.trim() || 'N/A'}`,
        );
        doc.text(`Verificado en RENIEC: ${identidad ? 'Sí' : 'No'}`);
      } else {
        doc.fontSize(12).text(`Documento: RUC ${params.numero_documento}`);
        doc.text(
          `Razón Social: ${identidad?.razon_social ?? identidad?.nombre_o_razon_social ?? 'N/A'}`,
        );
        doc.text(`Nombre Comercial: ${identidad?.nombre_comercial ?? 'N/A'}`);
        doc.text(
          `Estado/Condición SUNAT: ${identidad?.estado ?? identidad?.condicion ?? 'N/A'}`,
        );
      }

      doc.moveDown(0.5);
      doc.fontSize(12).text(`Fecha de emisión: ${new Date().toLocaleString('es-PE')}`);
      doc.moveDown(0.5);
      doc.fontSize(12).text(`Subtotal: S/ ${opGravada.toFixed(2)}`);
      doc.text(`Total: S/ ${total.toFixed(2)}`);
      doc.moveDown(1);
      doc
        .fontSize(10)
        .fillColor('gray')
        .text(
          `Esta es una representación impresa de la ${params.comprobante_tipo} electrónica generada por el sistema.`,
          { align: 'left' },
        );
      doc.fillColor('black');
      doc.end();

      await new Promise<void>((resolve, reject) => {
        pdfStream.on('finish', () => resolve());
        pdfStream.on('error', (err) => reject(err));
      });

      const xmlSkeleton =
        `<?xml version="1.0" encoding="UTF-8"?>
<Comprobante tipo="${params.comprobante_tipo}" serie="${serie}" numero="${numeroStr}">
` +
        `  <NumeroFormateado>${numeroFormateado}</NumeroFormateado>
` +
        `  <Documento tipo="${params.tipo_documento}">${params.numero_documento}</Documento>
` +
        `  <Totales>
    <OpGravada>${opGravada.toFixed(2)}</OpGravada>
    <IGV>${igv.toFixed(2)}</IGV>
    <Total>${total.toFixed(2)}</Total>
  </Totales>
` +
        `</Comprobante>`;
      fs.writeFileSync(xmlAbs, Buffer.from(xmlSkeleton, 'utf-8'));

      const nombreCliente =
        params.tipo_documento === 'DNI'
          ? [identidad?.first_name, identidad?.first_last_name, identidad?.second_last_name]
              .filter(Boolean)
              .join(' ')
          : (identidad?.razon_social ?? identidad?.nombre_o_razon_social ?? identidad?.nombre_comercial ?? '');
      const direccionCliente =
        params.tipo_documento === 'RUC'
          ? (identidad?.direccion ?? identidad?.domicilio_fiscal ?? identidad?.domicilio ?? '')
          : '';
      const svg = `<?xml version="1.0" encoding="UTF-8"?>
<svg width="1000" height="700" viewBox="0 0 1000 700" xmlns="http://www.w3.org/2000/svg">
  <style>
    .title { font: 700 28px sans-serif; }
    .h2 { font: 600 20px sans-serif; }
    .label { font: 400 16px sans-serif; fill: #333 }
    .val { font: 600 16px sans-serif; }
    .muted { font: 400 14px sans-serif; fill: #666 }
    .box { fill: #f6f7f9; stroke: #ddd }
  </style>
  <rect x="20" y="20" width="960" height="660" class="box"/>
  <text x="40" y="60" class="title">Comprobante electrónico</text>
  <text x="40" y="100" class="label">Tipo:</text><text x="140" y="100" class="val">${params.comprobante_tipo.toUpperCase()}</text>
  <text x="40" y="130" class="label">Serie:</text><text x="140" y="130" class="val">${serie}</text>
  <text x="40" y="160" class="label">Número:</text><text x="140" y="160" class="val">${numeroStr}</text>
  <text x="40" y="190" class="label">Correlativo:</text><text x="180" y="190" class="val">${numeroFormateado}</text>
  <text x="40" y="240" class="label">Documento:</text><text x="180" y="240" class="val">${params.tipo_documento} ${params.numero_documento}</text>
  <text x="40" y="270" class="label">Nombre/Razón social:</text><text x="260" y="270" class="val">${(nombreCliente || 'N/A').replace(/&/g, '&amp;')}</text>
  ${params.tipo_documento === 'RUC' ? `<text x="40" y="300" class="label">Dirección:</text><text x="160" y="300" class="val">${(direccionCliente || '').replace(/&/g, '&amp;')}</text>` : ''}
  <text x="40" y="330" class="label">Verificado:</text><text x="160" y="330" class="val">${identidad ? 'Sí' : 'No'}</text>
  <text x="40" y="410" class="label">Subtotal:</text><text x="260" y="410" class="val">S/ ${opGravada.toFixed(2)}</text>
  <text x="40" y="440" class="label">Total:</text><text x="260" y="440" class="val">S/ ${total.toFixed(2)}</text>
  <text x="40" y="530" class="label">Fecha de emisión:</text><text x="260" y="530" class="val">${new Date().toLocaleString('es-PE')}</text>
</svg>`;
      await sharp(Buffer.from(svg)).png().toFile(imgAbs);
    } catch {
      // No bloquear la emisión si algún archivo falla.
    }

    const dbComprobante = await this.prisma.comprobante.create({
      data: {
        pedido_id: pedido.id,
        tipo: params.comprobante_tipo,
        serie,
        numero,
        numero_formateado: numeroFormateado,
        archivo_nombre: `${fileBase}.pdf`,
        archivo_ruta: pdfRel.replace(/\\/g, '/'),
        mime: 'application/pdf',
        size_bytes: fs.existsSync(pdfAbs) ? fs.statSync(pdfAbs).size : null,
      },
    });

    const pdfPublic = `/uploads/${pdfRel.replace(/\\/g, '/')}`;
    const xmlPublic = `/uploads/${xmlRel.replace(/\\/g, '/')}`;
    const imgPublic = `/uploads/${imgRel.replace(/\\/g, '/')}`;

    const comprobante = {
      id: dbComprobante.id,
      tipo: params.comprobante_tipo,
      serie,
      numero,
      numero_formateado: numeroFormateado,
      estado: 'emitido',
      pedido_id: pedido.id,
      total,
      cliente:
        params.tipo_documento === 'DNI'
          ? {
              nombre:
                [identidad?.first_name, identidad?.first_last_name, identidad?.second_last_name]
                  .filter(Boolean)
                  .join(' ') || `${pedido.usuario?.nombre ?? ''} ${pedido.usuario?.apellido ?? ''}`.trim() || 'Cliente',
              dni: params.numero_documento,
            }
          : {
              razon_social:
                identidad?.razon_social ?? identidad?.nombre_o_razon_social ?? 'Cliente',
              ruc: params.numero_documento,
            },
      created_at: dbComprobante.created_at,
    };

    return {
      status: 200,
      body: {
        message: 'Comprobante emitido exitosamente',
        comprobante,
        archivos: { pdf: pdfPublic, xml: xmlPublic, img: imgPublic },
      },
    };
  }

  async misComprobantes(usuarioId: number) {
    const comprobantes = await this.prisma.comprobante.findMany({
      where: { pedido: { usuario_id: usuarioId } },
      orderBy: { created_at: 'desc' },
      include: { pedido: { include: { usuario: true } } },
    });

    return {
      status: 200,
      body: {
        comprobantes: comprobantes.map((c) => ({
          id: c.id,
          tipo: c.tipo,
          serie: c.serie,
          numero: c.numero_formateado,
          numero_formateado: c.numero_formateado,
          estado: 'emitido',
          total: this.toFloat(c.pedido.total),
          created_at: c.created_at,
          archivos: {
            pdf: `/uploads/${String(c.archivo_ruta).replace(/\\/g, '/')}`,
            xml: `/uploads/comprobantes/pedido-${c.pedido_id}-${c.serie}-${String(c.numero).padStart(8, '0')}.xml`,
            img: `/uploads/comprobantes/pedido-${c.pedido_id}-${c.serie}-${String(c.numero).padStart(8, '0')}.png`,
          },
          cliente: {
            nombre: `${c.pedido.usuario?.nombre ?? ''} ${c.pedido.usuario?.apellido ?? ''}`.trim() || 'Cliente',
          },
        })),
      },
    };
  }

  async adminComprobantes(params: {
    pagina?: number;
    limite?: number;
    tipo?: 'boleta' | 'factura';
    estado?: string;
  }) {
    const pagina = params.pagina && params.pagina > 0 ? params.pagina : 1;
    const limite = params.limite && params.limite > 0 ? params.limite : 20;
    const skip = (pagina - 1) * limite;
    const where: any = {};

    if (params.tipo) where.tipo = params.tipo;

    const [items, total] = await this.prisma.$transaction([
      this.prisma.comprobante.findMany({
        where,
        orderBy: { created_at: 'desc' },
        skip,
        take: limite,
        include: { pedido: { include: { usuario: true } } },
      }),
      this.prisma.comprobante.count({ where }),
    ]);

    const comprobantes = items.map((c) => ({
      id: c.id,
      tipo: c.tipo,
      serie: c.serie,
      numero: c.numero_formateado,
      numero_formateado: c.numero_formateado,
      estado: 'emitido',
      total: this.toFloat(c.pedido.total),
      created_at: c.created_at,
      archivos: {
        pdf: `/uploads/${String(c.archivo_ruta).replace(/\\/g, '/')}`,
        xml: `/uploads/comprobantes/pedido-${c.pedido_id}-${c.serie}-${String(c.numero).padStart(8, '0')}.xml`,
        img: `/uploads/comprobantes/pedido-${c.pedido_id}-${c.serie}-${String(c.numero).padStart(8, '0')}.png`,
      },
      cliente: {
        nombre: `${c.pedido.usuario?.nombre ?? ''} ${c.pedido.usuario?.apellido ?? ''}`.trim() || 'Cliente',
        razon_social: null,
        dni: null,
        ruc: null,
      },
    }));

    return {
      status: 200,
      body: {
        comprobantes,
        total,
        pagina,
        totalPaginas: Math.max(1, Math.ceil(total / limite)),
      },
    };
  }
}
