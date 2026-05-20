import {
  CanActivate,
  ExecutionContext,
  Injectable,
  ForbiddenException,
} from '@nestjs/common';

@Injectable()
export class AdminGuard implements CanActivate {
  canActivate(context: ExecutionContext): boolean {
    const req = context.switchToHttp().getRequest();
    const user = req.user;
    if (!user || user.tipo !== 'admin') {
      throw new ForbiddenException(
        'Acceso denegado: se requiere rol administrador',
      );
    }
    return true;
  }
}
