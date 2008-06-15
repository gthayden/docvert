#!/usr/bin/env python
#
# setuid wrapper around the similarly named bash script
#

import os
import sys
import pwd
import commands
import random

debug = True

def displayUsage():
	helpText = "USAGE: " + sys.argv[0] + "--setuid=(username) --stream  (accepts binary document on stdin and outputs opendocument on stdout)\n"
	sys.stderr.write(helpText)
	sys.exit(255)

def displayDebug(message):
	sys.stderr.write(message + "\n")

if __name__ == "__main__":
	if len(sys.argv) >= 2 and (sys.argv[1] == '--stream' or sys.argv[2] == '--stream'):
		setuidFlag = '--setuid='
		setuidUsername = None
		if len(sys.argv) == 3:
			if sys.argv[1].startswith(setuidFlag):
				setuidUsername = sys.argv[1][len(setuidFlag):]
			elif sys.argv[2].startswith(setuidFlag):
				setuidUsername = sys.argv[2][len(setuidFlag):]		
			else:
				displayUsage()

		if setuidUsername is not None:
			targetUid = pwd.getpwnam(setuidUsername)[2]
			if debug: displayDebug("targetUid %s" % targetUid)
			runTimeUid = os.getuid()
			if debug: displayDebug("currentUid %s" % runTimeUid)

			try:
				os.setuid(targetUid)
				currentUid = os.getuid()
				if debug: displayDebug("Sucessfully changed Uid to %s" % currentUid)
			except OSError, exception:
				if debug: displayDebug("Error, unable to change username.")
				raise exception
		else:
			if debug: displayDebug("Not elevating privledges via setuid because no --setuid flag was given.")
		temporaryDocPath = None
		numberOfTries = 0
		while True:
			temporaryDocPath = '/tmp/docvert-%s.doc' % random.randint(0, 9999999)
			if debug: displayDebug("Writing to new path: %s" % temporaryDocPath)
			if os.path.exists(temporaryDocPath) is False:
				break
			numberOfTries += 1
			if numberOfTries > 10:
				if debug: displayDebug("Error, unable find an available temporary path after 10 tries. Check permissions. Exiting...")
				sys.exit(255)

		if debug: displayDebug("Reading file from stdin...")
		stdinBytes = sys.stdin.read()
		if debug: displayDebug("...finished reading file from stdin.")
		fileHandler = open(temporaryDocPath, 'w')
		fileHandler.write(stdinBytes)
		fileHandler.close()
		bashScript = "%s/convert-using-abiword.sh" % os.path.dirname(os.path.abspath(__file__))
		commandLine = "%s %s" % (bashScript, temporaryDocPath)
		
		if debug: displayDebug("CommandLine: %s" % commandLine)
		response = commands.getstatusoutput(commandLine)
		if debug: displayDebug("Response from command: %s" % response[1])
		temporaryOdtPath = "%s.odt" % temporaryDocPath[0:-4]
		if not os.path.exists(temporaryOdtPath):
			displayDebug("ODF file not generated at %s" % temporaryOdtPath)
			sys.exit(response[0])

		#file converted sucessfully
		os.unlink(temporaryDocPath)

		fileHandler = open(temporaryOdtPath, 'r')
		openDocumentBytes = fileHandler.read()
		fileHandler.close()
		os.unlink(temporaryOdtPath)
		if debug: displayDebug("(Success: streaming ODF file back now on STDOUT...)")
		sys.stdout.write(openDocumentBytes)

	else:
		displayUsage()


