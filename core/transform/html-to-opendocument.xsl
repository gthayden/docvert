<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:c="container"

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
	xmlns:dom="http://www.w3.org/2001/xml-events"
	xmlns:xforms="http://www.w3.org/2002/xforms"
	xmlns:xsd="http://www.w3.org/2001/XMLSchema"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xmlns:config="http://openoffice.org/2001/config"
	>

	<xsl:param name="pageWidth">6in</xsl:param>

	<xsl:param name="dotsPerInch" select="number(92)"/>

	<xsl:variable name="numbers" select=" '01234567890.' "/>

	<xsl:output encoding="UTF-8"/>

	<xsl:template match="/">
		<office:document-content office:version="1.0">
			<office:automatic-styles>
				<style:style style:name="bold" style:family="text">
					<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
				</style:style>
				<style:style style:name="emphasis" style:family="text">
					<style:text-properties fo:font-style="italic" style:font-style-asian="italic" style:font-style-complex="italic"/>
				</style:style>
				<style:style style:name="strong" style:family="text">
					<style:text-properties fo:font-weight="bold" style:font-weight-asian="bold" style:font-weight-complex="bold"/>
				</style:style>
				<style:style style:name="italics" style:family="text">
					<style:text-properties fo:font-style="italic" style:font-style-asian="italic" style:font-style-complex="italic"/>
				</style:style>
				<text:list-style style:name="orderedList">
					<text:list-level-style-number text:level="1" style:num-format=""/>
					<text:list-level-style-number text:level="2" style:num-format=""/>
					<text:list-level-style-number text:level="3" style:num-format=""/>
					<text:list-level-style-number text:level="4" style:num-format=""/>
					<text:list-level-style-number text:level="5" style:num-format=""/>
					<text:list-level-style-number text:level="6" style:num-format=""/>
					<text:list-level-style-number text:level="7" style:num-format=""/>
					<text:list-level-style-number text:level="8" style:num-format=""/>
					<text:list-level-style-number text:level="9" style:num-format=""/>
					<text:list-level-style-number text:level="10" style:num-format=""/>
				</text:list-style>
				<text:list-style style:name="bulletedList">
					<text:list-level-style-bullet text:level="1" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="●">
						<style:list-level-properties text:space-before="0.25in" text:min-label-width="0.25in"/>
						<style:text-properties style:font-name="StarSymbol"/>
					</text:list-level-style-bullet>

					<text:list-level-style-bullet text:level="2" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="○">
						<style:list-level-properties text:space-before="0.5in" text:min-label-width="0.25in"/>
						<style:text-properties style:font-name="StarSymbol"/>
					</text:list-level-style-bullet>

					<text:list-level-style-bullet text:level="3" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="■">
						<style:list-level-properties text:space-before="0.75in" text:min-label-width="0.25in"/>
						<style:text-properties style:font-name="StarSymbol"/>
					</text:list-level-style-bullet>

					<text:list-level-style-bullet text:level="4" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="●">
						<style:list-level-properties text:space-before="1in" text:min-label-width="0.25in"/>
						<style:text-properties style:font-name="StarSymbol"/>
					</text:list-level-style-bullet>

					<text:list-level-style-bullet text:level="5" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="○">
						<style:list-level-properties text:space-before="1.25in" text:min-label-width="0.25in"/>
						<style:text-properties style:font-name="StarSymbol"/>
					</text:list-level-style-bullet>

					<text:list-level-style-bullet text:level="6" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="■">
						<style:list-level-properties text:space-before="1.5in" text:min-label-width="0.25in"/>
						<style:text-properties style:font-name="StarSymbol"/>
					</text:list-level-style-bullet>

					<text:list-level-style-bullet text:level="7" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="●">
						<style:list-level-properties text:space-before="1.75in" text:min-label-width="0.25in"/>
						<style:text-properties style:font-name="StarSymbol"/>
					</text:list-level-style-bullet>

					<text:list-level-style-bullet text:level="8" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="○">
						<style:list-level-properties text:space-before="2in" text:min-label-width="0.25in"/>
						<style:text-properties style:font-name="StarSymbol"/>
					</text:list-level-style-bullet>

					<text:list-level-style-bullet text:level="9" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="■">
						<style:list-level-properties text:space-before="2.25in" text:min-label-width="0.25in"/>
						<style:text-properties style:font-name="StarSymbol"/>
					</text:list-level-style-bullet>

					<text:list-level-style-bullet text:level="10" text:style-name="Bullet_20_Symbols" style:num-suffix="." text:bullet-char="●">
						<style:list-level-properties text:space-before="2.5in" text:min-label-width="0.25in"/>
						<style:text-properties style:font-name="StarSymbol"/>
					</text:list-level-style-bullet>
				</text:list-style>

				<style:style style:name="bulletedListParagraph" style:family="paragraph" style:parent-style-name="Standard" style:list-style-name="bulletedList">
					<style:paragraph-properties>
						<style:tab-stops>
							<style:tab-stop style:position="0.5in"/>
						</style:tab-stops>
					</style:paragraph-properties>
					<style:text-properties style:use-window-font-color="true" fo:font-size="12pt" fo:language="en" fo:country="US" style:font-name-asian="Sans Serif" style:font-size-asian="12pt" style:language-asian="en" style:country-asian="US" style:font-name-complex="Tahoma" style:font-size-complex="12pt" style:language-complex="en" style:country-complex="US"/>
				</style:style>

				<style:style style:name="image" style:family="graphic" style:parent-style-name="Graphics">
					<style:graphic-properties style:wrap="dynamic" style:number-wrapped-paragraphs="no-limit" style:wrap-contour="false" style:vertical-pos="from-top" style:vertical-rel="paragraph" style:horizontal-pos="from-left" style:horizontal-rel="paragraph" fo:background-color="#ffffff" style:background-transparency="0%" fo:padding="0.0102in" fo:border="none" style:mirror="none" fo:clip="rect(0in 0in 0in 0in)" draw:luminance="0%" draw:contrast="0%" draw:red="0%" draw:green="0%" draw:blue="0%" draw:gamma="100%" draw:color-inversion="false" draw:image-opacity="100%" draw:color-mode="standard">
						<style:background-image/>
					</style:graphic-properties>
				</style:style>
			</office:automatic-styles>
			<office:body>
				<office:text>
					<xsl:apply-templates/>
				</office:text>
			</office:body>
		</office:document-content>
	</xsl:template>

	<xsl:template match="html:h1 | html:h2 | html:h3 | html:h4 | html:h5 | html:h6">
		<text:h>
			<xsl:attribute name="text:outline-level">
				<xsl:value-of select="substring-after(local-name(), 'h')"/>
			</xsl:attribute>
			<xsl:apply-templates/>
		</text:h>
	</xsl:template>

	<xsl:template match="html:p">
		<xsl:element name="text:p">
			<xsl:choose>
				<xsl:when test="contains(@style, 'font-weight')">
					<xsl:variable name="font-weight">
						<xsl:value-of select="substring-after(@style, 'font-weight:')"/>
					</xsl:variable>
					<xsl:variable name="font-weight-value">
						<xsl:choose>
							<xsl:when test="contains($font-weight, ';')">
								<xsl:value-of select="substring-before($font-weight, ';')"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="$font-weight"/>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:variable>
					<xsl:choose>
						<xsl:when test="contains($font-weight-value, 'bold') or contains($font-weight-value, '00')">
							<xsl:element name="text:span">
								<xsl:attribute name="text:style-name">strong</xsl:attribute>
								<xsl:apply-templates/>
							</xsl:element>
						</xsl:when>
						<xsl:otherwise>
							<xsl:apply-templates/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:element>
	</xsl:template>

	<xsl:template match="html:table">
		<xsl:variable name="tableId">
			<xsl:text>Table</xsl:text>
			<xsl:number/>
		</xsl:variable>
		<table:table table:name="{$tableId}" table:style-name="{$tableId}">
			<xsl:if test="parent::table:table-cell and count(following-sibling::*) = 0 and count(preceding-sibling::*) = 0">
				<xsl:attribute name="table:is-sub-table">
					<xsl:text>true</xsl:text>
				</xsl:attribute>
			</xsl:if>
			<xsl:variable name="columns" select="descendant::html:tr[1]/html:td"/>
			<xsl:for-each select="$columns">
				<table:table-column table:style-name="{$tableId}.{position()}"/>
			</xsl:for-each>
			<xsl:apply-templates/>
		</table:table>
	</xsl:template>

	<xsl:template match="html:tr">
		<table:table-row>
			<xsl:apply-templates/>
		</table:table-row>
	</xsl:template>

	<xsl:template match="html:td">
		<xsl:variable name="innerText">
			<xsl:for-each select="text()">
				<xsl:value-of select="."/>
			</xsl:for-each>
		</xsl:variable>
		<table:table-cell office:value-type="string">
			<xsl:choose>
				<xsl:when test="normalize-space($innerText) or html:a or html:b or html:strong or html:i or html:em or html:span or html:img or html:font or html:tt">
					<xsl:element name="text:p">
						<xsl:apply-templates select="text() | html:a | html:b | html:strong | html:i | html:em | html:span | html:img | html:font | html:tt"/>
					</xsl:element>
					<xsl:apply-templates select="*[not(self::html:a | self::html:b | self::html:strong | self::html:i | self::html:em | self::html:span | self::html:img | self::html:font | self::html:tt)]"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates/>
				</xsl:otherwise>
			</xsl:choose>
		</table:table-cell>
	</xsl:template>


	<xsl:template match="text()">
		<xsl:choose>
			<xsl:when test="normalize-space(.) and (parent::html:div or parent::c:page or parent::html:form or parent::html:blockquote)">
				<xsl:element name="text:p">
					<xsl:value-of select="."/>
				</xsl:element>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="."/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="html:a">
		<xsl:choose>
			<xsl:when test="parent::html:div or parent::c:page or parent::html:form or parent::html:blockquote">
				<xsl:element name="text:p">
					<xsl:call-template name="drawHyperlink"/>
				</xsl:element>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="drawHyperlink"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="drawHyperlink">
		<xsl:element name="text:a">
			<xsl:attribute name="xlink:type">simple</xsl:attribute>
			<xsl:attribute name="xlink:href"><xsl:value-of select="@href"/></xsl:attribute>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="html:b | html:em | html:strong | html:i | html:span">
		<xsl:choose>
			<xsl:when test="parent::html:div or parent::c:page or parent::html:form or parent::html:blockquote">
				<xsl:element name="text:p">
					<xsl:call-template name="drawInlineFormattedText"/>
				</xsl:element>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="drawInlineFormattedText"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="drawInlineFormattedText">
		<xsl:element name="text:span">
			<xsl:if test="self::html:b or self::html:em or self::html:strong or self::html:i">
				<xsl:attribute name="text:style-name">
					<xsl:choose>
						<xsl:when test="self::html:b">bold</xsl:when>
						<xsl:when test="self::html:em">emphasis</xsl:when>
						<xsl:when test="self::html:strong">strong</xsl:when>
						<xsl:when test="self::html:i">italics</xsl:when>
					</xsl:choose>
				</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>		
	

	<xsl:template match="html:ul | html:ol">
		<text:list text:style-name="bulletedList">
			<xsl:apply-templates/>
		</text:list>
	</xsl:template>

	<xsl:template match="html:li">
		<xsl:variable name="innerText">
			<xsl:for-each select="text()">
				<xsl:value-of select="."/>
			</xsl:for-each>
		</xsl:variable>
		<text:list-item>
			<xsl:choose>
				<xsl:when test="normalize-space($innerText) or html:a or html:b or html:strong or html:i or html:em or html:span or html:img or html:font or html:tt">
					<xsl:element name="text:p">
						<xsl:apply-templates select="text() | html:a | html:b | html:strong | html:i | html:em | html:span | html:img | html:font | html:tt"/>
					</xsl:element>
					<xsl:apply-templates select="*[not(self::html:a | self::html:b | self::html:strong | self::html:i | self::html:em | self::html:span | self::html:img | self::html:font | self::html:tt)]"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates/>
				</xsl:otherwise>
			</xsl:choose>
		</text:list-item>
	</xsl:template>


	<xsl:template match="html:img">
		<xsl:variable name="imageWidth">
			<xsl:choose>
				<xsl:when test="@width and string-length(translate(@width, $numbers, '')) = 0">
					<xsl:value-of select="@width"/>
				</xsl:when>
				<xsl:when test="string-length(translate(@c:width, $numbers, '')) = 0">
					<xsl:value-of select="@c:width"/>
				</xsl:when>
			</xsl:choose>
		</xsl:variable>
		<xsl:variable name="imageHeight">
			<xsl:choose>
				<xsl:when test="@height and string-length(translate(@height, $numbers, '')) = 0">
					<xsl:value-of select="@height"/>
				</xsl:when>
				<xsl:when test="string-length(translate(@c:height, $numbers, '')) = 0">
					<xsl:value-of select="@c:height"/>
				</xsl:when>
			</xsl:choose>
		</xsl:variable>
		<draw:frame draw:style-name="image" draw:name="graphics1" text:anchor-type="as-char"  draw:z-index="0">
			<xsl:attribute name="svg:width">
				<xsl:choose>
					<xsl:when test="normalize-space($imageWidth)">
						<xsl:value-of select="number($imageWidth) div $dotsPerInch"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text>1</xsl:text>
					</xsl:otherwise>
				</xsl:choose>
				<xsl:text>in</xsl:text>
			</xsl:attribute>
			<xsl:attribute name="svg:height">
				<xsl:choose>
					<xsl:when test="normalize-space($imageHeight)">
						<xsl:value-of select="number($imageHeight) div $dotsPerInch"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text>1</xsl:text>
					</xsl:otherwise>
				</xsl:choose>
				<xsl:text>in</xsl:text>
			</xsl:attribute>
			<draw:image xlink:href="{@src}" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
		</draw:frame>
	</xsl:template>

</xsl:stylesheet>
