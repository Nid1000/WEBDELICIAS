import { Injectable } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import * as bcrypt from 'bcryptjs';
import { PrismaService } from '../prisma/prisma.service';

export type UserTokenPayload = {
  id: number;
  email: string;
  tipo: 'usuario' | 'admin';
};

@Injectable()
export class AuthService {
  constructor(
    private prisma: PrismaService,
    private jwt: JwtService,
  ) {}

  private generateToken(payload: UserTokenPayload) {
    return this.jwt.sign(payload);
  }

  async registerUsuario(data: {
    nombre: string;
    apellido: string;
    email: string;
    password: string;
    telefono?: string | null;
    direccion?: string | null;
    distrito?: string | null;
    numero_casa?: string | null;
    ip?: string;
    userAgent?: string;
  }) {
    const existing = await this.prisma.usuario.findUnique({
      where: { email: data.email },
    });
    if (existing) {
      return {
        status: 400,
        body: {
          error: 'Email ya registrado',
          message: 'Ya existe una cuenta con este email',
        },
      };
    }

    const hashed = await bcrypt.hash(data.password, 10);
    const nuevo = await this.prisma.usuario.create({
      data: {
        nombre: data.nombre,
        apellido: data.apellido,
        email: data.email,
        password: hashed,
        telefono: data.telefono || null,
        direccion: data.direccion || null,
        distrito: data.distrito || null,
        numero_casa: data.numero_casa || null,
      },
      select: { id: true, nombre: true, apellido: true, email: true, telefono: true, direccion: true, distrito: true, numero_casa: true },
    });

    const token = this.generateToken({
      id: nuevo.id,
      email: nuevo.email,
      tipo: 'usuario',
    });
    return {
      status: 201,
      body: { message: 'Usuario registrado exitosamente', user: nuevo, token },
    };
  }

  async loginUsuario(
    email: string,
    password: string,
    ip?: string,
    userAgent?: string,
  ) {
    const user = await this.prisma.usuario.findUnique({ where: { email } });
    if (!user) {
      await this.logLogin(null, null, 'usuario', ip, userAgent, false);
      return {
        status: 401,
        body: {
          error: 'Credenciales inválidas',
          message: 'Email o contraseña incorrectos',
        },
      };
    }
    if (!user.activo) {
      await this.logLogin(user.id, null, 'usuario', ip, userAgent, false);
      return {
        status: 401,
        body: {
          error: 'Cuenta inactiva',
          message: 'Tu cuenta ha sido desactivada',
        },
      };
    }
    const valid = await bcrypt.compare(password, user.password);
    if (!valid) {
      await this.logLogin(user.id, null, 'usuario', ip, userAgent, false);
      return {
        status: 401,
        body: {
          error: 'Credenciales inválidas',
          message: 'Email o contraseña incorrectos',
        },
      };
    }
    await this.logLogin(user.id, null, 'usuario', ip, userAgent, true);
    const token = this.generateToken({
      id: user.id,
      email: user.email,
      tipo: 'usuario',
    });
    return {
      status: 200,
      body: {
        message: 'Login exitoso',
        user: {
          id: user.id,
          nombre: user.nombre,
          apellido: user.apellido,
          email: user.email,
          telefono: user.telefono,
          direccion: user.direccion,
          distrito: user.distrito,
          numero_casa: user.numero_casa,
        },
        token,
      },
    };
  }

  async loginAdmin(
    email: string,
    password: string,
    ip?: string,
    userAgent?: string,
  ) {
    const admin = await this.prisma.administrador.findUnique({
      where: { email },
    });
    if (!admin) {
      await this.logLogin(null, null, 'admin', ip, userAgent, false);
      return {
        status: 401,
        body: {
          error: 'Credenciales inválidas',
          message: 'Email o contraseña incorrectos',
        },
      };
    }
    if (!admin.activo) {
      await this.logLogin(null, admin.id, 'admin', ip, userAgent, false);
      return {
        status: 401,
        body: {
          error: 'Cuenta inactiva',
          message: 'Tu cuenta de administrador ha sido desactivada',
        },
      };
    }
    const valid = await bcrypt.compare(password, admin.password);
    if (!valid) {
      await this.logLogin(null, admin.id, 'admin', ip, userAgent, false);
      return {
        status: 401,
        body: {
          error: 'Credenciales inválidas',
          message: 'Email o contraseña incorrectos',
        },
      };
    }
    await this.logLogin(null, admin.id, 'admin', ip, userAgent, true);
    const token = this.generateToken({
      id: admin.id,
      email: admin.email,
      tipo: 'admin',
    });
    return {
      status: 200,
      body: {
        message: 'Login de administrador exitoso',
        admin: {
          id: admin.id,
          nombre: admin.nombre,
          email: admin.email,
          rol: admin.rol,
        },
        token,
      },
    };
  }

  async logLogin(
    userId: number | null,
    adminId: number | null,
    tipo: 'usuario' | 'admin',
    ip?: string,
    userAgent?: string,
    exitoso = true,
  ) {
    try {
      await this.prisma.loginLog.create({
        data: {
          usuario_id: userId ?? null,
          admin_id: adminId ?? null,
          tipo_usuario: tipo,
          ip_address: ip ?? null,
          user_agent: userAgent ?? null,
          exitoso,
        },
      });
    } catch (e) {
      // Logueo no crítico
      // console.error('Error registrando login', e);
    }
  }

  async verify(payload: {
    id: number;
    email: string;
    tipo: 'usuario' | 'admin';
  }) {
    if (payload.tipo === 'admin') {
      const admin = await this.prisma.administrador.findUnique({
        where: { id: payload.id },
        select: {
          id: true,
          nombre: true,
          email: true,
          rol: true,
          activo: true,
        },
      });
      if (!admin || !admin.activo) {
        return {
          status: 401,
          body: {
            error: 'Token inválido',
            message: 'Administrador no encontrado o inactivo',
          },
        };
      }
      return {
        status: 200,
        body: {
          message: 'Token válido',
          tipo: 'admin',
          admin: {
            id: admin.id,
            nombre: admin.nombre,
            email: admin.email,
            rol: admin.rol,
          },
        },
      };
    } else {
      const user = await this.prisma.usuario.findUnique({
        where: { id: payload.id },
        select: {
          id: true,
          nombre: true,
          apellido: true,
          email: true,
          telefono: true,
          direccion: true,
          distrito: true,
          numero_casa: true,
          activo: true,
        },
      });
      if (!user || !user.activo) {
        return {
          status: 401,
          body: {
            error: 'Token inválido',
            message: 'Usuario no encontrado o inactivo',
          },
        };
      }
      return {
        status: 200,
        body: {
          message: 'Token válido',
          tipo: 'usuario',
          user: {
            id: user.id,
            nombre: user.nombre,
            apellido: user.apellido,
            email: user.email,
            telefono: user.telefono,
            direccion: user.direccion,
            distrito: user.distrito,
            numero_casa: user.numero_casa,
          },
        },
      };
    }
  }
}
