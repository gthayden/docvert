<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"
	xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
	xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
	xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
	xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0"
	xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0"
	xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0"
	xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"
	xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0"
	xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0"
	xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0"
	xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0"

	xmlns:xlink="http://www.w3.org/1999/xlink"
	xmlns:math="http://www.w3.org/1998/Math/MathML"
	xmlns:ooo="http://openoffice.org/2004/office"
	xmlns:ooow="http://openoffice.org/2004/writer"
	xmlns:oooc="http://openoffice.org/2004/calc"
	xmlns:dom="http://www.w3.org/2001/xml-events"
	xmlns:xforms="http://www.w3.org/2002/xforms"
	xmlns:xsd="http://www.w3.org/2001/XMLSchema"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xmlns:config="http://openoffice.org/2001/config"

	office:class="text"
	>

	<xsl:output method="xml" omit-xml-declaration="no"  />

	<xsl:key name="styleNames" match="office:automatic-styles/style:style" use="@style:name"/>

<xsl:template match="text:h">
	<xsl:variable name="innerText">
		<xsl:for-each select="descendant::text()">
			<xsl:value-of select="."/>
		</xsl:for-each>
	</xsl:variable>
	<xsl:if test="normalize-space($innerText)">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:if>
</xsl:template>

<xsl:template match="text:section">
	<xsl:apply-templates/>
</xsl:template>


<!--TODO: also find lowercase string 'heading'? -->
<xsl:template match="text:p[contains(@text:style-name, 'Heading')] | text:p[contains(key('styleNames', @text:style-name)/@style:parent-style-name, 'Heading')]">
	<xsl:variable name="innerText">
		<xsl:for-each select="descendant::text()">
			<xsl:value-of select="."/>
		</xsl:for-each>
	</xsl:variable>
	<xsl:if test="normalize-space($innerText)">
		<xsl:element name="text:h">
			<xsl:variable name="possibleHeadingDepth">
				<xsl:choose>
					<xsl:when test="contains(@text:style-name, 'Heading')"><xsl:value-of select="@text:style-name"/></xsl:when>
					<xsl:when test="contains(key('styleNames', @text:style-name)/@style:parent-style-name, 'Heading')"><xsl:value-of select="key('styleNames', @text:style-name)/@style:parent-style-name"/></xsl:when>
				</xsl:choose>
			</xsl:variable>
			<xsl:variable name="possibleHeadingDepth2">
				<xsl:call-template name="replaceCharsInString">
					<xsl:with-param name="stringIn" select="$possibleHeadingDepth"/>
					<xsl:with-param name="charsIn" select="'_20_'"/>
					<xsl:with-param name="charsOut" select="' '"/>
	    			</xsl:call-template>
			</xsl:variable>
			<xsl:variable name="possibleHeadingDepth3">
				<xsl:call-template name="replaceCharsInString">
					<xsl:with-param name="stringIn" select="$possibleHeadingDepth2"/>
					<xsl:with-param name="charsIn" select="'_2b_'"/>
					<xsl:with-param name="charsOut" select="'+'"/>
	    			</xsl:call-template>
			</xsl:variable>

			<xsl:variable name="possibleHeadingDepth4" select="normalize-space(substring-after($possibleHeadingDepth3, 'Heading '))"/>
			<xsl:variable name="possibleHeadingDepth5">
				<xsl:choose>
					<xsl:when test="contains($possibleHeadingDepth4, ' ')">
						<xsl:value-of select="normalize-space(substring-before($possibleHeadingDepth4, ' '))"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="$possibleHeadingDepth4"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<xsl:variable name="remainingHeading" select="normalize-space(translate($possibleHeadingDepth5, '01234567890', ''))"/>
			<xsl:variable name="headingLevel" select="normalize-space($possibleHeadingDepth5)"/>
			<xsl:attribute name="text:outline-level">
				<xsl:choose>
					<xsl:when test="not($remainingHeading)"><xsl:value-of select="$headingLevel"/></xsl:when>
					<xsl:otherwise>1</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<!-- <xsl:message terminate="yes">Not a number. Unable to determine depth of a heading with style-name="<xsl:value-of select="$possibleHeadingDepth5"/>" and "<xsl:value-of select="$possibleHeadingDepth4"/>" and "<xsl:value-of select="$possibleHeadingDepth3"/>".</xsl:message> -->
			<xsl:attribute name="text:style-name"><xsl:value-of select="@text:style-name"/></xsl:attribute>
			<!-- <xsl:value-of select="$innerText"/> -->
			<xsl:apply-templates select="@*|node()"/>
		</xsl:element>
	</xsl:if>
</xsl:template>

<xsl:template match="@*|node()">
   <xsl:copy>
      <xsl:apply-templates select="@*|node()"/>
   </xsl:copy>
</xsl:template>

<xsl:template name="replaceCharsInString">
	<xsl:param name="stringIn"/>
	<xsl:param name="charsIn"/>
	<xsl:param name="charsOut"/>
		<xsl:choose>
		<xsl:when test="contains($stringIn,$charsIn)">
			<xsl:value-of select="concat(substring-before($stringIn,$charsIn),$charsOut)"/>
			<xsl:call-template name="replaceCharsInString">
				<xsl:with-param name="stringIn" select="substring-after($stringIn,$charsIn)"/>
				<xsl:with-param name="charsIn" select="$charsIn"/>
				<xsl:with-param name="charsOut" select="$charsOut"/>
			</xsl:call-template>
		</xsl:when>
	    	<xsl:otherwise>
			<xsl:value-of select="$stringIn"/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

</xsl:stylesheet>
