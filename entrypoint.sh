#!/bin/sh
rm -f /etc/nginx/conf.d/default.conf
nginx -g 'daemon off;'
