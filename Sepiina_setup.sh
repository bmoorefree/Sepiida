#!/bin/bash

# Exit script on any error
set -e

# Ensure script is run as root
if [[ $EUID -ne 0 ]]; then
    echo "This script must be run as root. Exiting."
    exit 1
fi

# Update system and install necessary dependencies
echo "Updating package list and upgrading system..."
apt update && apt upgrade -y
echo "Installing SQUID proxy server..."
apt install -y squid

# Configure SQUID with a custom block list
echo "Configuring SQUID proxy server..."
cat <<EOF > /etc/squid/squid.conf
http_port 3128
acl blocked_domains dstdomain "/etc/squid/blocked_domains.txt"
http_access deny blocked_domains
http_access allow all

# Logging settings
access_log /var/log/squid/access.log squid
cache_log /var/log/squid/cache.log
EOF

# Create a default block list
echo "Creating default blocked domains list..."
cat <<EOF > /etc/squid/blocked_domains.txt
example.com
ads.example.net
EOF

# Restart SQUID to apply the configuration
echo "Restarting SQUID proxy server..."
systemctl restart squid

# Rebrand the system - NOT ENABLED BY DEFAULT -uncomment to change system name to Sepiina
#echo "Rebranding the operating system to Sepiina..."
#echo "Sepiina" > /etc/hostname
#hostnamectl set-hostname Sepiina

# Copy custom logo (download and places logo is placed in /root/sepiina.png)
wget https://github.com/bmoorefree/Sepiida/new/main/sepiida.png /root/sepiida.png
if [ -f /root/sepiina.png ]; then
    echo "Copying custom logo..."
    cp /root/sepiina.png /usr/share/pixmaps/sepiina.png
else
    echo "Custom logo not found at /root/sepiina.png. Skipping logo copy."
fi

# Notify user of completion
echo "Setup completed successfully! SQUID is configured and the system is using Sepiina."

# Suggest a reboot
echo "It's recommended to reboot the system for all changes to take effect."
