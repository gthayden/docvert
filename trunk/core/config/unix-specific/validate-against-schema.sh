#!/bin/bash
schemaPath="$1"
htmlDocument="$2"

/usr/bin/xmllint --noout --relaxng "${schemaPath}" "${htmlDocument}"
