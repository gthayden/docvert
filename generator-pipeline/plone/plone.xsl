<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:c="container"
	>

	<xsl:template match="c:page">
		<xsl:copy>
			<xsl:apply-templates select="@*|descendant::html:div[@class='documentContent']/node()"/>
		</xsl:copy>
	</xsl:template>

	<xsl:template match="html:div[@id='portal-breadcrumbs' or @class='documentActions' or @id='portal-footer']"/>

	<xsl:template match="html:span[@class='forwardback']"/>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:template>

</xsl:stylesheet>
