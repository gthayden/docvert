#!/bin/sh
### BEGIN INIT INFO
# Provides:          openoffice.org-server
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Required-Start:    $local_fs $network_fs $network $syslog
# Required-Stop:     $local_fs $network_fs $network $syslog
# Short-Description: OpenOffice.org Server Daemon
# Description:       Start/stop OpenOffice.org in server mode
### END INIT INFO

ooocommand="/var/www/docvert/core/config/unix-specific/openoffice.org-server.sh"
export HOME=/tmp/
pidfile="/tmp/openoffice.org-server.pid"
username="$2"
groupname="$3"
startStopDaemon="/sbin/start-stop-daemon"

if [ -n "$username" ]
then
	username="-c $username "
fi
if [ -n "$groupname" ]
then
	groupname="-g $groupname "
fi
if [ -s "$ooocommand" ]
then
	sleep 0
else
	echo "Comand not found at $ooocommand"
	exit 1
fi
ooo_start()
{
	if [ -s "$pidfile" ]
	then
		echo "Already running? pid file exists at $pidfile"
	else
		rm -f "$pidfile"
		$startStopDaemon "$groupname" "$username" --pidfile $pidfile --start --exec "$ooocommand"
		sleep 2
		pgrep "soffice" > "$pidfile"
		if [ -s "$pidfile" ]
		then
			sleep 0
		else
			echo "Unable to start OpenOffice.org (empty pid file at $pidfile)"
		fi
		return 0
	fi
}

ooo_stop()
{
	if [ -s "$pidfile" ]
	then
		$startStopDaemon "$groupname" "$username" --stop --quiet --pidfile "$pidfile"
		rm -f "$pidfile"
		return 0
	else
		echo "Stopped. Warning: No pid file found at $pidfile so I'm assuming it was never running."
	fi
}

case "$1" in
	start)
		ooo_start
	;;
	stop)
		ooo_stop
	;;
	restart)
		ooo_stop
		ooo_start
	;;
	*)
		echo "Usage: openoffice.org-server.init.sh {start|stop|restart}"
		exit 1
	;;
esac
