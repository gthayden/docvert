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
from os.path import exists as pathExists
import sys
from StringIO import StringIO

try:
	import uno
except ImportError: #probably a Fedora/Redhat/SuSE system
	possiblePaths = ['/usr/lib/openoffice.org/program/', '/usr/lib/openoffice.org2.0/program/', '/usr/lib/openoffice.org2.2/program/', '/usr/lib/openoffice.org2.3/program/', '/usr/lib/openoffice.org2.4/program/', '/usr/lib/openoffice.org3.0/program/', '/opt/lib/openoffice.org/program/', '/opt/lib/openoffice.org2.0/program/', '/opt/lib/openoffice.org2.2/program/', '/opt/lib/openoffice.org2.3/program/', '/opt/lib/openoffice.org2.4/program/', '/opt/lib/openoffice.org3.0/program/', '/opt/openoffice.org/program/', '/opt/openoffice.org2.0/program/', '/opt/openoffice.org2.2/program/', '/opt/openoffice.org2.3/program/', '/opt/openoffice.org2.4/program/', '/opt/openoffice.org3.0/program/']
	for possiblePath in possiblePaths:
		if pathExists(possiblePath):
			sys.path.append(possiblePath)
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
	filterPdf = "writer_pdf_Export"
	magicBytesPdf = "%PDF" 
	filterOdt = "writer8"
	magicBytesOdt = "PK"
	
	def __init__(self, port=DEFAULT_OPENOFFICE_PORT):
		self.localContext = uno.getComponentContext()
		self.serviceManager = self.localContext.ServiceManager
		resolver = self.serviceManager.createInstanceWithContext("com.sun.star.bridge.UnoUrlResolver", self.localContext)
		try:
			context = resolver.resolve("uno:socket,host=localhost,port=%s;urp;StarOffice.ComponentContext" % port)
		except NoConnectException, exception:
			raise Exception, "Failed to connect to OpenOffice.org on port %s. %s" % (port, exception)
		self.desktop = context.ServiceManager.createInstanceWithContext("com.sun.star.frame.Desktop", context)

	def convertByStream(self, stdinBytes, filterName):
		inputStream = self.serviceManager.createInstanceWithContext("com.sun.star.io.SequenceInputStream", self.localContext)
		inputStream.initialize((uno.ByteSequence(stdinBytes),)) 

		document = self.desktop.loadComponentFromURL('private:stream', "_blank", 0, self._toProperties(
			InputStream=inputStream,
			Hidden=False))

		if not document:
			raise Exception, "Error making document"
		try:
			document.refresh()
		except AttributeError:
			pass
		outputStream = OutputStreamWrapper(False)
		properties = dict(
			Overwrite=True,
			OutputStream=outputStream,
			FilterName=filterName)
		anyException = False
		if "pdf_" in filterName:
			properties["FilterData"] = self._addPdf()
		try:
			document.storeToURL('private:stream', self._toProperties(**properties))
		except Exception, e:
			anyException = e
	        	pass 
		finally:
			document.close(True)
		responseBytes = outputStream.data.getvalue()
		outputStream.close()
		if filterName == DocumentConverter.filterOdt and responseBytes[0:len(self.magicBytesOdt)] == self.magicBytesOdt:
			return responseBytes
		elif filterName == DocumentConverter.filterPdf and responseBytes[0:len(self.magicBytesPdf)] == self.magicBytesPdf:
			return responseBytes

		if len(responseBytes) == 0:
			sys.stderr.write("No response from OpenOffice after the conversion. E.g. response length=0")
		else:
			fileHeaderLength = 10
			if len(responseBytes) < 10:
				fileHeaderLength = len(responseBytes)
			sys.stderr.write("Although there was a response it didn't appear to be in the expected format. E.g. The first %i bytes of the response '%s'  weren't in the format of %s" % (fileHeaderLength, responseBytes[0:fileHeaderLength],filterName))
		if anyException != False:
			raise anyException
		raise Exception("The conversion wasn't successful but no exception was raised by OpenOffice, therefore PyODConverter is now raising a generic exception.")

	def convertByPath(self, inputFile, outputFile, filterName):
		inputUrl = self._toFileUrl(inputFile)
		outputUrl = self._toFileUrl(outputFile)
		print inputUrl
		print outputUrl

		document = self.desktop.loadComponentFromURL(inputUrl, "_blank", 0, self._toProperties(Hidden=False))
		properties = dict(FilterName=filterName, Overwrite=True)
		if "pdf_" in filterName:
			properties["FilterData"] = self._addPdf()
		try:
			document.refresh()
		except AttributeError:
			pass
		try:
			document.storeToURL(outputUrl, self._toProperties(**properties))
		except Exception: 
	        	pass 
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

	def _addPdf(self):
		return self._toProperties(
			CompressMode="1",
			PageRange="1-")		

if __name__ == "__main__":
	try:
		args = sys.argv
		filterName = DocumentConverter.filterOdt
		pdfFlag = "--pdf"
		if pdfFlag in args:
			filterName = DocumentConverter.filterPdf
			#filterName = "writer_globaldocument_pdf_Export"
			args.remove(pdfFlag)
		if len(args) == 2 and args[1] == '--stream':
			stdinBytes = sys.stdin.read()
			converter = DocumentConverter()
			responseBytes = converter.convertByStream(stdinBytes, filterName)
			sys.stdout.write(responseBytes)
			sys.stderr.write("(Success)\n")
			sys.exit(0)
		elif len(args) == 3 or len(args) == 4:
			converter = DocumentConverter()
			if not isfile(args[1]):
				sys.stderr.write("No such input file: %s\n" % args[1])
				sys.exit(1)
			converter.convertByPath(args[1], args[2], filterName)
		else:
			helpText = "USAGE: " + args[0] + " <input-path> <output-path>\n"
			helpText += "USAGE: " + args[0] + " --stream  (accepts binary document on stdin and outputs on stdout)\n"
			sys.stderr.write(helpText)
			sys.exit(2)
	except Exception, exception:
		sys.stderr.write("Error: %s" % exception)
		raise exception
		sys.exit(1)

