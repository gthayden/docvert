#!/bin/bash
mockConversion="$1"
docvertMacrosDocumentPath="$2"
inputDocumentUrl="$3"
outputDocumentUrl="$4"
HOME=/tmp/


if [ $mockConversion = "true" ]
then
	export DISPLAY=":0.0"
else
	xvfbRun="/usr/bin/xvfb-run"
fi


cd /tmp
${xvfbRun} /usr/lib/openoffice/program/soffice -writer -norestore "${docvertMacrosDocumentPath}" macro://macros/Standard.convert.toOasisOpenDocumentFormat\(${inputDocumentUrl},${outputDocumentUrl}\)
