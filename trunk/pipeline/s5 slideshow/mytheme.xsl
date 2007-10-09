<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:html="http://www.w3.org/1999/xhtml"
	exclude-result-prefixes="html"
 	>

	<xsl:template match="html:body">
		<xsl:copy>
			<div class="layout">
				<div id="controls"><xsl:text> </xsl:text></div>
				<div id="currentSlide"><xsl:text> </xsl:text></div>
				<div id="header"><xsl:text> </xsl:text></div>
				<div id="footer">
					<h1><xsl:value-of select="//html:head/html:title"/></h1>
				</div>
			</div>

			<div class="presentation">
				<xsl:apply-templates select="@*|node()"/>
			</div>
		</xsl:copy>
	</xsl:template>

	<xsl:template match="html:head">
		<head>
			<xsl:apply-templates/>
			<meta name="generator" content="Docvert" />
			<meta name="version" content="S5 1.1" />
			<meta name="defaultView" content="slideshow" />
			<meta name="controlVis" content="hidden" />
			<link rel="stylesheet" href="ui/default/slides.css" type="text/css" media="projection" id="slideProj" />
			<link rel="stylesheet" href="ui/default/outline.css" type="text/css" media="screen" id="outlineStyle" />
			<link rel="stylesheet" href="ui/default/print.css" type="text/css" media="print" id="slidePrint" />
			<link rel="stylesheet" href="ui/default/opera.css" type="text/css" media="projection" id="operaFix" />

			<script src="ui/default/slides.js" type="text/javascript"><xsl:text> </xsl:text></script>
		</head>
	</xsl:template>

	<xsl:template match="html:div[@class='page']">
		<div class="slide">
			<xsl:apply-templates/>
		</div>
	</xsl:template>

	<xsl:template match="html:style">
		<xsl:copy>
			.siteName {font-size:xx-large; padding: 3px 0px 3px 20px;margin:0px;background:#006699;color:white;}
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:template>

	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:template>

</xsl:stylesheet>
