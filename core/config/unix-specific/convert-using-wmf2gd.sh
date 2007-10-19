#!/bin/bash
insideDirectory="$1"
inputImagePath="$2"
outputImagePath="$3"

cd "${insideDirectory}"
/usr/bin/wmf2gd "${inputImagePath}" -o "${outputImagePath}" --maxpect --maxwidth=600 --maxheight=400
