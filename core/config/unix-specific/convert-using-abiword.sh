#!/bin/bash
inputDocumentUrl="$1"

/usr/bin/abiword "${inputDocumentUrl}" --to=odt
