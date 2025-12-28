#!/bin/bash
echo "Checking PHP sessions..."
echo "Session save path:"
php -r "echo session_save_path(); echo PHP_EOL;"

echo -e "\nListing session files:"
ls -la /tmp/ | grep sess_

echo -e "\nChecking PHP info:"
php -r "phpinfo();" | grep -A5 -B5 session