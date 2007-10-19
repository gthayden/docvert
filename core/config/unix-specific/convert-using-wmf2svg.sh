#!/bin/bash
inputImagePath="$1"
outputImagePath="$2"

/usr/bin/wmf2svg "${inputImagePath}" -o "${outputImagePath}"
