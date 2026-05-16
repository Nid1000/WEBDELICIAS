import * as path from 'path';

const configuredBase = process.env.UPLOAD_PATH?.trim();

export function getUploadsRoot() {
  return configuredBase
    ? path.resolve(configuredBase)
    : path.join(process.cwd(), 'uploads');
}

export function getUploadSubdir(...segments: string[]) {
  return path.join(getUploadsRoot(), ...segments);
}
