<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:html="http://www.w3.org/1999/xhtml">

	<xsl:template match="html:div[@class='page']">
		<xsl:apply-templates/>
	</xsl:template>

	<xsl:template match="html:a">
		<xsl:element name="a">
			<xsl:attribute name="href">
				<xsl:choose>
					<xsl:when test="contains(@href, 'file:///')">
						<xsl:value-of select="substring-after(@href, 'file:///')"/>
					</xsl:when>
					<xsl:when test="contains(@href, 'https://test.pluto.onesquared.net/en')">
						<xsl:value-of select="substring-after(@href, 'https://test.pluto.onesquared.net/en')"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="@href"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="html:div[@id='tableOfContents']"/>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:template>

</xsl:stylesheet>
