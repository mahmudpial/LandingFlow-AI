#!/usr/bin/env bash
set -euo pipefail

# LandingFlow AI — S3 Media Bucket Hardening Script
# Requires explicit user confirmation before executing against a live AWS account.

BUCKET_NAME="${1:-landingflow-ai-media-prod}"
AWS_REGION="${AWS_DEFAULT_REGION:-us-east-1}"

echo "Target S3 Bucket: ${BUCKET_NAME} in region ${AWS_REGION}"
read -r -p "Confirm creation/hardening of S3 bucket '${BUCKET_NAME}'? (yes/NO): " CONFIRM
if [[ "${CONFIRM}" != "yes" ]]; then
  echo "Aborted."
  exit 0
fi

aws s3api create-bucket --bucket "${BUCKET_NAME}" --region "${AWS_REGION}"

# Block all public access by default
aws s3api put-public-access-block \
  --bucket "${BUCKET_NAME}" \
  --public-access-block-configuration "BlockPublicAcls=true,IgnorePublicAcls=true,BlockPublicPolicy=true,RestrictPublicBuckets=true"

# Enforce default AES256 Server-Side Encryption
aws s3api put-bucket-encryption \
  --bucket "${BUCKET_NAME}" \
  --server-side-encryption-configuration '{"Rules":[{"ApplyServerSideEncryptionByDefault":{"SSEAlgorithm":"AES256"}}]}'

echo "S3 bucket ${BUCKET_NAME} created and hardened successfully."
