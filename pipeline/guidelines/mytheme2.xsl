<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:html="http://www.w3.org/1999/xhtml"
	>

	<xsl:template match="html:div[@id='nextPreviousMenu']">
		<div id="nextPreviousMenu" class="menu">
			[
			<xsl:for-each select="descendant::html:li">
				<xsl:if test="position() != 1">|</xsl:if>
				<xsl:apply-templates select="node()"/>
			</xsl:for-each>
			]
		</div>
	</xsl:template>

	<xsl:template match="html:div[@id='pageTitle']"/>
	<xsl:template match="html:div[@id='pagesMenu']"/>

	<xsl:template match="html:p[@class='pageTitle']"/>
	<xsl:template match="html:div[@class='pageTitle']"/>

	<xsl:template match="html:table">
		<table border="1" cellpadding="0" cellspacing="0">
			<xsl:apply-templates/>
		</table>
	</xsl:template>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:template>

</xsl:stylesheet>
