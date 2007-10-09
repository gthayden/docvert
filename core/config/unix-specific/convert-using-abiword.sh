#!/bin/sh
export PATH=$PATH:/usr/bin/X11
export LANG=en_US
export HOME=/var/www/vhosts/docvert #does not have a trailing slash

inputDocumentUrl=$1

abiword ${inputDocumentUrl} --to=odt
