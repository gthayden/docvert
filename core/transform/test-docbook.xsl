<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:db="http://docbook.org/ns/docbook"
	>

<xsl:template match="text()"/>

<xsl:template match="db:title">
	<xsl:choose>
		<xsl:when test="normalize-space(text()) = '[no title]'">
			<div class="error">
				<p>Document contains empty titles</p>
			</div>
		</xsl:when>
		<xsl:when test="not(normalize-space(text()))">
			<div class="error">
				<p>Document contains empty titles</p>
			</div>
		</xsl:when>
	</xsl:choose>
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="/">
	<xsl:if test="not(//db:title)">
		<div class="error">
			<p>Documents should have titles</p>
		</div>
	</xsl:if>
	<xsl:apply-templates/>

</xsl:template>

<xsl:template match="db:imagedata">
	<xsl:choose>
		<xsl:when test=" @format='wmf' ">
			<div class="error">
				<p>Document contains WMF images which will not display in the majority of browsers</p>
			</div>
		</xsl:when>
		<xsl:when test="not(@format='gif' or @format='jpg' or @format='jpeg' or @format='png')">
			<div class="error">
				<p>
					Document contains unrecognised image format of
					"<xsl:value-of select="@format"/>".
					(path was "<xsl:value-of select="@fileref"/>")
				</p>
			</div>
		</xsl:when>
	</xsl:choose>
</xsl:template>

</xsl:stylesheet>
