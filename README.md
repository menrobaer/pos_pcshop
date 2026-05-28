Computer shop POS

## S3 Image Upload Setup

This project now supports uploading product images to Amazon S3.

### 1) Install dependency

The AWS SDK is added in Composer:

- `aws/aws-sdk-php`

### 2) Set environment variables

Set these variables in your web server or runtime environment:

- `AWS_S3_ENABLED=true`
- `AWS_DEFAULT_REGION=ap-southeast-1`
- `AWS_BUCKET=your-bucket-name`
- `AWS_ACCESS_KEY_ID=your-access-key`
- `AWS_SECRET_ACCESS_KEY=your-secret-key`

Optional:

- `AWS_S3_PREFIX=products`
- `AWS_S3_BASE_URL=https://cdn.example.com` (or your bucket public URL)
- `AWS_S3_ACL=public-read` (leave empty if bucket ACLs are disabled)

If S3 is disabled or misconfigured, image upload falls back to local storage under `web/uploads/products`.
