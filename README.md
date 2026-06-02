Computer shop POS

## Tigris Image Upload Setup

This project supports uploading product images to Tigris object storage using the S3-compatible API.

### 1) Install dependency

The AWS SDK is used for S3-compatible uploads:

- `aws/aws-sdk-php`

### 2) Set environment variables

Set these values in your runtime config:

- `AWS_S3_ENABLED=true`
- `AWS_DEFAULT_REGION=ap-southeast-1`
- `AWS_BUCKET=vlc-computer`
- `AWS_ACCESS_KEY_ID=your-access-key`
- `AWS_SECRET_ACCESS_KEY=your-secret-key`

Optional:

- `AWS_S3_PREFIX=vlc`
- `AWS_S3_BASE_URL=https://vlc-computer.t3.tigrisfiles.io`
- `AWS_S3_ENDPOINT=https://t3.tigrisfiles.io` (optional when endpoint can be derived from base URL)
- `AWS_S3_ACL=public-read` (leave empty if bucket ACLs are disabled)

Expected public image URL shape:

- `https://vlc-computer.t3.tigrisfiles.io/vlc/product/<product-id>/<filename>.jpg`

If storage is disabled or misconfigured, image upload falls back to local storage under `web/uploads/products`.
