#!/bin/bash
# Bash script to create databases and run migrations for all tenants

php="php"
artisan="artisan"
config_file="config/tenants.php"

# Get tenant names from config/tenants.php
TENANTS=$(grep "'name' =>" $config_file | awk -F"'" '{print $4}')

for tenant in $TENANTS; do
  echo "Creating and migrating for tenant: $tenant"
  $php $artisan tenant:create $tenant
  if [ $? -ne 0 ]; then
    echo "Error for tenant: $tenant"
    exit 1
  fi
done

echo "All tenants processed."
