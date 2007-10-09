<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:html="http://www.w3.org/1999/xhtml"
	>

<xsl:template match="//title">
	<xsl:if test="not(normalize-space(text()))">
		<div class="error">
			<p>Titles should have text</p>
		</div>
	</xsl:if>
</xsl:template>

</xsl:stylesheet>
