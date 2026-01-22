import {
  S3Client,
  PutObjectCommand,
  GetObjectCommand,
  DeleteObjectCommand,
} from '@aws-sdk/client-s3';
import { getSignedUrl } from '@aws-sdk/s3-request-presigner';
import { config } from '../config';
import { logger } from '../utils/logger';
import { v4 as uuidv4 } from 'uuid';
import path from 'path';

export interface UploadResult {
  key: string;
  url: string;
  size: number;
}

export class StorageService {
  private client: S3Client;
  private bucket: string;

  constructor() {
    this.client = new S3Client({
      region: config.aws.region,
      credentials: {
        accessKeyId: config.aws.accessKeyId,
        secretAccessKey: config.aws.secretAccessKey,
      },
    });
    this.bucket = config.aws.s3Bucket;
  }

  async uploadDocument(
    file: Buffer,
    originalFilename: string,
    mimeType: string,
    organizationId: string
  ): Promise<UploadResult> {
    const ext = path.extname(originalFilename);
    const key = `documents/${organizationId}/${uuidv4()}${ext}`;

    const command = new PutObjectCommand({
      Bucket: this.bucket,
      Key: key,
      Body: file,
      ContentType: mimeType,
      Metadata: {
        originalFilename,
        organizationId,
      },
    });

    await this.client.send(command);

    logger.info(`Document uploaded: ${key}`);

    return {
      key,
      url: `s3://${this.bucket}/${key}`,
      size: file.length,
    };
  }

  async getDocument(key: string): Promise<Buffer> {
    const command = new GetObjectCommand({
      Bucket: this.bucket,
      Key: key,
    });

    const response = await this.client.send(command);

    if (!response.Body) {
      throw new Error('Empty response body');
    }

    const chunks: Uint8Array[] = [];
    for await (const chunk of response.Body as AsyncIterable<Uint8Array>) {
      chunks.push(chunk);
    }

    return Buffer.concat(chunks);
  }

  async getSignedDownloadUrl(key: string, expiresIn: number = 3600): Promise<string> {
    const command = new GetObjectCommand({
      Bucket: this.bucket,
      Key: key,
    });

    return getSignedUrl(this.client, command, { expiresIn });
  }

  async getSignedUploadUrl(
    filename: string,
    mimeType: string,
    organizationId: string,
    expiresIn: number = 3600
  ): Promise<{ url: string; key: string }> {
    const ext = path.extname(filename);
    const key = `documents/${organizationId}/${uuidv4()}${ext}`;

    const command = new PutObjectCommand({
      Bucket: this.bucket,
      Key: key,
      ContentType: mimeType,
    });

    const url = await getSignedUrl(this.client, command, { expiresIn });

    return { url, key };
  }

  async deleteDocument(key: string): Promise<void> {
    const command = new DeleteObjectCommand({
      Bucket: this.bucket,
      Key: key,
    });

    await this.client.send(command);

    logger.info(`Document deleted: ${key}`);
  }
}

export const storageService = new StorageService();
