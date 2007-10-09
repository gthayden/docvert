<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:c="container"
	>

	<xsl:output method="xml" omit-xml-declaration="yes"/>

<xsl:template match="text()"/>

<xsl:template match="html:img">
	<xsl:text>image&#9;</xsl:text>
	<xsl:choose>
		<xsl:when test="ancestor::c:page/@baseUrl">
			<xsl:value-of select="ancestor::c:page/@baseUrl"/>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="ancestor::c:page/@url"/>
		</xsl:otherwise>
	</xsl:choose>
	<xsl:text>&#9;</xsl:text>
	<xsl:value-of select="@src"/>
	<xsl:text>&#10;</xsl:text>
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="html:a">
	<xsl:text>link&#9;</xsl:text>
	<xsl:choose>
		<xsl:when test="ancestor::c:page/@baseUrl">
			<xsl:value-of select="ancestor::c:page/@baseUrl"/>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="ancestor::c:page/@url"/>
		</xsl:otherwise>
	</xsl:choose>
	<xsl:text>&#9;</xsl:text>
	<xsl:value-of select="@href"/>
	<xsl:text>&#10;</xsl:text>
	<xsl:apply-templates/>
</xsl:template>

</xsl:stylesheet>
