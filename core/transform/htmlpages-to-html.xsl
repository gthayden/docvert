<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns="http://www.w3.org/1999/xhtml"
	>

	<xsl:output
		method="xml"
		indent="no"
		omit-xml-declaration="yes"
		version="1.0"
		encoding="UTF-8"
	/>

	<xsl:template match="html:html | html:body">
		<docvert-remove-me>
			<xsl:apply-templates/>
		</docvert-remove-me>
	</xsl:template>

	<xsl:template match="html:head"/>

	<xsl:template match="html:div[@class='page' or @class='sect1' or @class='sect2' or @class='sect3' or @class='sect4' or @class='sect5' or @class='sect6']">
		<xsl:apply-templates/>
	</xsl:template>

	<xsl:template match="html:h1 | html:h2 | html:h3 | html:h4 | html:h5 | html:h6">
		<xsl:variable name="innerText" select="normalize-space(translate(text(), ' &#160;&#9;&#10;', ''))"/>
		<xsl:if test="$innerText">
			<xsl:copy>
				<xsl:attribute name="heading-level"><xsl:value-of select="number(translate(local-name(), 'h', ''))"/></xsl:attribute>
				<xsl:apply-templates select="@*|node()"/>
			</xsl:copy>
		</xsl:if>
	</xsl:template>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:template>

</xsl:stylesheet>
