#!/bin/bash
set -euo pipefail

# Defer to the upstream image's entrypoint to install Joomla on first boot
# (it copies sources into /var/www/html and runs the silent installer if the
# DB is empty). Once Joomla is in place we hand control back to apache.
#
# We don't auto-install the plugin/module — keep that explicit via
# `make install` so developers see what they're shipping.

if [ -f /entrypoint.sh ] && [ "${1:-}" = "apache2-foreground" ]; then
    exec /entrypoint.sh "$@"
fi

if [ -f /usr/local/bin/docker-php-entrypoint ]; then
    exec /usr/local/bin/docker-php-entrypoint "$@"
fi

exec "$@"
