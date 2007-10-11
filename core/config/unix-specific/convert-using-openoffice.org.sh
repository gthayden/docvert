#!/bin/bash
export PATH=$PATH:/usr/bin/X11
export LANG=en_US
export LC_ALL=C
export localXPort=:0.0
#export HOME=/var/www/vhosts/docvert

mockConversion=$1
docvertMacrosDocumentPath=$2
inputDocumentUrl=$3
outputDocumentUrl=$4

if [ $mockConversion = "true" ]
then
	XDisplayToUse="-display :0.0"
else
	xvfbRun="xvfb-run"
fi

${xvfbRun} oowriter -norestore ${XDisplayToUse} ${docvertMacrosDocumentPath} macro://macros/Standard.convert.toOasisOpenDocumentFormat\(${inputDocumentUrl},${outputDocumentUrl}\)
