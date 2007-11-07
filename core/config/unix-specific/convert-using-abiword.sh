#!/bin/bash
inputDocumentUrl="$1"
HOME=/var/www/docvert

/usr/bin/abiword "${inputDocumentUrl}" --to=odt
