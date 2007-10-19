#!/bin/bash
mockConversion="$1"
docvertMacrosDocumentPath="$2"
inputDocumentUrl="$3"
outputDocumentUrl="$4"

if [ $mockConversion = "true" ]
then
	XDisplayToUse="-display :0.0"
	export localXPort=":0.0"
else
	xvfbRun="/usr/bin/xvfb-run"
fi

${xvfbRun} /usr/lib/openoffice/program/soffice -writer -norestore "${XDisplayToUse}" "${docvertMacrosDocumentPath}" macro://macros/Standard.convert.toOasisOpenDocumentFormat\(${inputDocumentUrl},${outputDocumentUrl}\)
