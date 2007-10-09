<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"

	xmlns:db="http://docbook.org/ns/docbook"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.w3.org/1999/xhtml"

	exclude-result-prefixes="html"
	>

	<xsl:output method="xml" omit-xml-declaration="no"/>

<xsl:template match="db:book">
	<xsl:copy>
		<xsl:apply-templates/>
		{{body}}
	</xsl:copy>
</xsl:template>

<xsl:template match="db:book/db:title | db:book/db:info/db:title">
	<db:title>
		{{title}}
	</db:title>
</xsl:template>

<xsl:template match="db:preface | db:chapter"/>

<xsl:template match="@*|node()">
	<xsl:copy>
		<xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
</xsl:template>

</xsl:stylesheet>
