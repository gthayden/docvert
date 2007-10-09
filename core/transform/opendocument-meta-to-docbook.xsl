<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"

	xmlns:db="http://docbook.org/ns/docbook"

	xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"
	xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
	xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
	xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
	xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0"
	xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0"
	xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0"
	xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"
	xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0"
	xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0"
	xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0"
	xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0"

	xmlns:xlink="http://www.w3.org/1999/xlink"
	xmlns:math="http://www.w3.org/1998/Math/MathML"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:dom="http://www.w3.org/2001/xml-events"
	xmlns:xforms="http://www.w3.org/2002/xforms"
	xmlns:xsd="http://www.w3.org/2001/XMLSchema"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xmlns:ooo="http://openoffice.org/2004/office"
	xmlns:ooow="http://openoffice.org/2004/writer"
	xmlns:oooc="http://openoffice.org/2004/calc"
	
	xmlns:config="http://openoffice.org/2001/config"

	office:class="text"
	>
	<xsl:output method="xml" omit-xml-declaration="yes" version="1.0" encoding="UTF-8"/>

<xsl:template match="office:document-meta">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="office:meta">
	<db:info>
		<xsl:if test="meta:initial-creator | meta">
			<db:authorgroup>
				<xsl:if test="meta:initial-creator"><db:author><xsl:value-of select="meta:initial-creator"/></db:author></xsl:if>
				<xsl:if test="dc:creator"><db:author><xsl:value-of select="dc:creator"/></db:author></xsl:if>
			</db:authorgroup>
		</xsl:if>
		<xsl:if test="dc:date | meta:creation-date">
			<db:date>
				<xsl:choose>	
					<xsl:when test="dc:date"><xsl:value-of select="dc:date"/></xsl:when>
					<xsl:when test="meta:creation-date"><xsl:value-of select="meta:creation-date"/></xsl:when>
				</xsl:choose>
			</db:date>
		</xsl:if>
		<xsl:apply-templates/>
	</db:info>
</xsl:template>

<xsl:template match="dc:title | title | meta:initial-creator | dc:creator"/>

<xsl:template match="@* | node()">
	<xsl:copy>
		<xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
</xsl:template>



</xsl:stylesheet>
