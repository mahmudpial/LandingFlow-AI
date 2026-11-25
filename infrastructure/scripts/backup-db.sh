#!/usr/bin/env bash
set -euo pipefail

# LandingFlow AI — Daily PostgreSQL Backup Script
BACKUP_DIR="${BACKUP_DIR:-/var/backups/landingflow-ai}"
DB_NAME="${DB_DATABASE:-landingflow}"
DB_USER="${DB_USERNAME:-landingflow}"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
BACKUP_FILE="${BACKUP_DIR}/landingflow_${TIMESTAMP}.sql.gz"

mkdir -p "${BACKUP_DIR}"
pg_dump -U "${DB_USER}" "${DB_NAME}" | gzip > "${BACKUP_FILE}"

# Prune local backups older than 14 days
find "${BACKUP_DIR}" -type f -name "landingflow_*.sql.gz" -mtime +14 -delete

echo "Database backup written to ${BACKUP_FILE}"
