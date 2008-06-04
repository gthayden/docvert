#!/usr/bin/env python
#
# setuid wrapper around the similarly named bash script
#

import os
import sys
import pwd
import commands

script = "openoffice.org-server-init.py"
bashscript = "%s/openoffice.org-server-init.sh" % os.path.dirname(os.path.abspath(__file__))

if __name__ == "__main__":
	if len(sys.argv) != 4:
		sys.stderr.write("USAGE: %s (start|stop) (username) (debug mode True|False)  \n" % script)
		sys.exit(0)
	#try:
	debug = sys.argv[3].lower() != "false"

	targetUsername = sys.argv[2]
	targetUid = pwd.getpwnam(targetUsername)[2]
	if debug: print "targetUid %s" % targetUid

	runTimUid = os.getuid()
	if debug: print "currentUid %s" % runTimUid
	try:
		os.setuid(targetUid)
		currentUid = os.getuid()
		if debug: print "Sucessfully changed Uid to %s" % currentUid
	except OSError, exception:
		raise exception
	commandLine = "%s %s %s" % (bashscript, sys.argv[1], sys.argv[2])
	print commandLine
	response = commands.getstatusoutput(commandLine)
	print response[1]
	sys.exit(response[0])

