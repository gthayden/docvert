#!/bin/bash
#NOTE: If wmf2gd isn't sufficient then read the docs in /doc/wmf-or-emf.txt
inputImagePath="$1"
outputImagePath="$2"

/usr/bin/wmf2svg "${inputImagePath}" -o "${outputImagePath}"
