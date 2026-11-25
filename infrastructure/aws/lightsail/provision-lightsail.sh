#!/usr/bin/env bash
set -euo pipefail

# LandingFlow AI — Amazon Lightsail Provisioning Script
# Estimated Cost: ~$12/month (small_3_0: 2GB RAM, 1 vCPU, 60GB SSD)
# Never run without explicit user approval.

INSTANCE_NAME="${1:-landingflow-ai-prod}"
AVAILABILITY_ZONE="${2:-us-east-1a}"
BUNDLE_ID="${3:-small_3_0}"
BLUEPRINT_ID="ubuntu_24_04"

echo "================================================================"
echo "LandingFlow AI — AWS Lightsail Provisioning Plan"
echo "Instance Name : ${INSTANCE_NAME}"
echo "Blueprint     : ${BLUEPRINT_ID}"
echo "Bundle Plan   : ${BUNDLE_ID} (~$12.00/month)"
echo "Zone          : ${AVAILABILITY_ZONE}"
echo "================================================================"

read -r -p "Do you approve creating this paid AWS Lightsail instance? (yes/NO): " CONFIRM
if [[ "${CONFIRM}" != "yes" ]]; then
  echo "Cancelled. No AWS resources were created."
  exit 0
fi

aws lightsail create-instances \
  --instance-names "${INSTANCE_NAME}" \
  --availability-zone "${AVAILABILITY_ZONE}" \
  --blueprint-id "${BLUEPRINT_ID}" \
  --bundle-id "${BUNDLE_ID}"

aws lightsail open-instance-public-ports \
  --port-info fromPort=443,toPort=443,protocol=TCP \
  --instance-name "${INSTANCE_NAME}"

echo "Lightsail instance ${INSTANCE_NAME} provisioning initiated."
