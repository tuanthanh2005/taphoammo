#!/bin/bash

# Script upload lên Hostinger
# Sử dụng: bash upload_to_hostinger.sh

echo "==================================="
echo "Upload MMO Marketplace to Hostinger"
echo "==================================="

# Cấu hình FTP
FTP_HOST="ftp.yourdomain.com"
FTP_USER="u828928906"
FTP_PASS="your_password"
HOME_DIR="/home/u828928906"
PUBLIC_DIR="/domains/yourdomain.com/public_html"

echo ""
echo "1. Upload app, config, routes, storage..."
lftp -u $FTP_USER,$FTP_PASS $FTP_HOST <<EOF
mirror -R app $HOME_DIR/app
mirror -R config $HOME_DIR/config
mirror -R routes $HOME_DIR/routes
mirror -R storage $HOME_DIR/storage
put .env -o $HOME_DIR/.env
put database.sql -o $HOME_DIR/database.sql
bye
EOF

echo ""
echo "2. Upload public files to public_html..."
lftp -u $FTP_USER,$FTP_PASS $FTP_HOST <<EOF
cd $PUBLIC_DIR
put public/index.php -o index.php
put public/.htaccess -o .htaccess
mirror -R public/assets assets
bye
EOF

echo ""
echo "3. Set permissions..."
lftp -u $FTP_USER,$FTP_PASS $FTP_HOST <<EOF
chmod 755 $HOME_DIR/storage
chmod 755 $HOME_DIR/storage/logs
chmod 755 $HOME_DIR/storage/cache
chmod 755 $HOME_DIR/storage/backups
chmod 755 $PUBLIC_DIR/assets/uploads
bye
EOF

echo ""
echo "==================================="
echo "Upload completed!"
echo "==================================="
echo ""
echo "Next steps:"
echo "1. Import database.sql via phpMyAdmin"
echo "2. Update .env with database credentials"
echo "3. Visit https://yourdomain.com"
echo ""
