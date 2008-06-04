#!/bin/bash
cat /var/www/docvert/doc/sample/sample-document.doc | /var/www/docvert/core/lib/pyodconverter/pyodconverter2.py --stream > /tmp/outer.odt

