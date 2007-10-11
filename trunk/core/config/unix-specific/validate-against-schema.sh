#!/bin/bash
schemaPath=$1
htmlDocument=$2

xmllint --noout --relaxng ${schemaPath} ${htmlDocument}
