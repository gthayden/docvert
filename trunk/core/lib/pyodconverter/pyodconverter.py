#!/usr/bin/env python
#
# PyODConverter (Python OpenDocument Converter) v1.0.0 - 2008-05-05
#
# This script converts a document from one office format to another by
# connecting to an OpenOffice.org instance via Python-UNO bridge.
#
# Copyright (C) 2008 Mirko Nasato <mirko@artofsolving.com>
#                    Matthew Holloway <matthew@holloway.co.nz>
# Licensed under the GNU LGPL v2.1 - http://www.gnu.org/licenses/lgpl-2.1.html
# - or any later version.
#

DEFAULT_OPENOFFICE_PORT = 2002

from os.path import abspath
from os.path import isfile
from os.path import splitext
import sys
from StringIO import StringIO

try:
	import uno
except ImportError: #probably a Fedora/Redhat/SuSE system
	sys.path.append('/usr/lib/openoffice.org/program/')
	sys.path.append('/usr/lib/openoffice.org2.0/program/')
	try:
		import uno
	except ImportError: #unable to find Python UNO libraries, exiting
		sys.stderr.write("Error: Unable to find Python UNO libraries in %s. Exiting..." % sys.path)		
		sys.exit(0)
		
import unohelper
from com.sun.star.beans import PropertyValue
from com.sun.star.task import ErrorCodeIOException
from com.sun.star.uno import Exception as UnoException
from com.sun.star.connection import NoConnectException
from com.sun.star.io import XOutputStream

class OutputStreamWrapper(unohelper.Base, XOutputStream):
	""" Minimal Implementation of XOutputStream """
	def __init__(self, debug=True):
		self.debug = debug
		self.data = StringIO()
		self.position = 0
		if self.debug:
			sys.stderr.write("__init__ OutputStreamWrapper.\n")

	def writeBytes(self, bytes):
		if self.debug:
			sys.stderr.write("writeBytes %i bytes.\n" % len(bytes.value))
		self.data.write(bytes.value)
		self.position += len(bytes.value)

	def close(self):
		if self.debug:
			sys.stderr.write("Closing output. %i bytes written.\n" % self.position)
		self.data.close()

	def flush(self):
		if self.debug:
			sys.stderr.write("Flushing output.\n")
		pass


class DocumentConverter:
	
	def __init__(self, port=DEFAULT_OPENOFFICE_PORT):
		self.localContext = uno.getComponentContext()
		self.serviceManager = self.localContext.ServiceManager
		resolver = self.serviceManager.createInstanceWithContext("com.sun.star.bridge.UnoUrlResolver", self.localContext)
		try:
			context = resolver.resolve("uno:socket,host=localhost,port=%s;urp;StarOffice.ComponentContext" % port)
		except NoConnectException, exception:
			raise Exception, "Failed to connect to OpenOffice.org on port %s. %s" % (port, exception)
		self.desktop = context.ServiceManager.createInstanceWithContext("com.sun.star.frame.Desktop", context)

	def convertByStream(self, stdinBytes):
		inputStream = self.serviceManager.createInstanceWithContext("com.sun.star.io.SequenceInputStream", self.localContext)
		inputStream.initialize((uno.ByteSequence(stdinBytes),)) 

		document = self.desktop.loadComponentFromURL('private:stream', "_blank", 0, self._toProperties(
			InputStream=inputStream,
			ReadOnly=True))

		if not document:
			raise Exception, "Error making document"
		try:
			document.refresh()
		except AttributeError:
			pass
		outputStream = OutputStreamWrapper(False)
		try:
			document.storeToURL('private:stream', self._toProperties(
				OutputStream=outputStream,
				FilterName="writer8"))
		finally:
			document.close(True)
		openDocumentBytes = outputStream.data.getvalue()
		outputStream.close()
		return openDocumentBytes


	def convertByPath(self, inputFile, outputFile):
		inputUrl = self._toFileUrl(inputFile)
		outputUrl = self._toFileUrl(outputFile)
		document = self.desktop.loadComponentFromURL(inputUrl, "_blank", 0, self._toProperties(Hidden=True))
		try:
			document.refresh()
		except AttributeError:
			pass
		try:
			document.storeToURL(outputUrl, self._toProperties(FilterName="writer8"))
		finally:
			document.close(True)

	def _toFileUrl(self, path):
		return uno.systemPathToFileUrl(abspath(path))

	def _toProperties(self, **args):
		props = []
		for key in args:
			prop = PropertyValue()
			prop.Name = key
			prop.Value = args[key]
			props.append(prop)
		return tuple(props)

if __name__ == "__main__":
	try:
		if len(sys.argv) == 2 and sys.argv[1] == '--stream':
			stdinBytes = sys.stdin.read()
			converter = DocumentConverter()
			openDocumentBytes = converter.convertByStream(stdinBytes)
			sys.stdout.write(openDocumentBytes)
			sys.stderr.write("(Success)\n")
			sys.exit(0)
		elif len(sys.argv) == 3:
			converter = DocumentConverter()
			if not isfile(sys.argv[1]):
				sys.stderr.write("No such input file: %s\n" % sys.argv[1])
				sys.exit(1)
			converter.convertByPath(sys.argv[1], sys.argv[2])
		else:
			helpText = "USAGE: " + sys.argv[0] + " <input-path> <output-path>\n"
			helpText += "USAGE: " + sys.argv[0] + " --stream  (accepts binary document on stdin and outputs on stdout)\n"
			sys.stderr.write(helpText)
			sys.exit(2)
	except Exception, exception:
		sys.stderr.write("Error: %s" % exception)
		sys.exit(1)

