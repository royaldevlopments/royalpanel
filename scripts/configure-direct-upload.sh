#!/bin/bash
# RoyalPanel Direct Upload Configuration Script
# Run this once after installing the panel to enable large file uploads
# via the SFTP client page (bypasses Wings upload limit).

set -e

echo "==> Adding www-data to wings group..."
usermod -aG wings www-data
echo "    Done."

echo "==> Setting group write permissions on volumes directory..."
chgrp -R wings /var/lib/royalpanel/volumes/
chmod -R g+w /var/lib/royalpanel/volumes/
chmod g+s /var/lib/royalpanel/volumes/
echo "    Done."

echo ""
echo "==> Next step: Update nginx config"
echo ""
echo "    Add or update these lines in your panel's nginx site config"
echo "    (inside the server block, usually /etc/nginx/sites-enabled/pterodactyl.conf):"
echo ""
echo "        client_max_body_size 0;"
echo "        client_body_timeout 120s;"
echo ""
echo "    And in the PHP location block, update the PHP_VALUE:"
echo ""
echo "        fastcgi_param PHP_VALUE \"upload_max_filesize = 20480M \\\\n post_max_size=20480M\";"
echo ""
echo "    Then reload nginx and restart PHP:"
echo ""
echo "        nginx -t && systemctl reload nginx && systemctl restart php8.3-fpm"
echo ""