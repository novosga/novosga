#!/bin/sh

cp -av /home/d327387/workspace/sart/src/* /home/d327387/workspace/sart/pkt/sartsolvertray/usr/share/sartsolvertray

chown -R root:root /home/d327387/workspace/sart/pkt/*

dpkg-deb -b sartsolvertray sartsolvertray_1.1_noarch.deb
cp -a sartsolvertray_1.1_noarch.deb /var/www/







