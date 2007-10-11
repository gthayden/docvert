#!/bin/bash
inputImagePath=$1
outputImagePath=$2

wmf2svg "${inputImagePath}" -o "${outputImagePath}"
