<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:html="http://www.w3.org/1999/xhtml">

	<xsl:template match="html:body">
		<xsl:copy>
			<p class="siteName" style="color:#666666;font-family:sans-serif; padding: 3px;margin:0px;background:#eeeeee">
				<span style="margin-left:10px;font-weight:bold;font-style:italic;color:#999966">d<span style="font-style:normal;color:#666699; font-size:medium;margin-left:-1px">ML</span></span>
				&#160;
				The Department of Markup Languages
			</p>
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:template>

	<xsl:template match="html:div[@id='tableOfContents']"/>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:template>

</xsl:stylesheet>
