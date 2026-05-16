import {
  CanActivate,
  ExecutionContext,
  Injectable,
  ForbiddenException,
} from '@nestjs/common';

@Injectable()
export class UsuarioGuard implements CanActivate {
  canActivate(context: ExecutionContext): boolean {
    const req = context.switchToHttp().getRequest();
    const user = req.user;
    if (!user || user.tipo !== 'usuario') {
      throw new ForbiddenException(
        'Acceso denegado: se requiere usuario autenticado',
      );
    }
    return true;
  }
}
