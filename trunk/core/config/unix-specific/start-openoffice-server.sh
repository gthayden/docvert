#!/bin/sh
export PATH=$PATH:/usr/bin/X11
export LANG=en_US
export LC_ALL=C
export localXPort=:0.0
#export HOME=/var/www/vhosts/docvert

xvfb=$1

if [ $xvfb = "true" ]
then
	xvfbCommand="xvfb-run"
else
	XDisplayToUse="-display :0.0"
fi

nohup ${xvfbCommand} oowriter -norestore ${XDisplayToUse} -accept="socket,port=8100;urp;" &
