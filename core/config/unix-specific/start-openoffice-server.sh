#!/bin/bash
xvfb="$1"

if [ $xvfb = "true" ]
then
	xvfbCommand="/usr/bin/xvfb-run"
else
	XDisplayToUse="-display :0.0"
	export localXPort=:0.0
fi

nohup ${xvfbCommand} /usr/bin/oowriter -norestore "${XDisplayToUse}" -accept="socket,port=8100;urp;" &
