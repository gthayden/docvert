<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"

	xmlns="http://docbook.org/ns/docbook"
	xmlns:db="http://docbook.org/ns/docbook"

	xmlns:html="http://www.w3.org/1999/xhtml"
	xmlns:xlink="http://www.w3.org/1999/xlink"

	exclude-result-prefixes="html"
	>

	<xsl:output method="xml" omit-xml-declaration="no"/>

	<xsl:key name='headchildren'
		match="*[not(self::html:h1 or self::html:h2 or self::html:h3 or self::html:h4 or self::html:h5 or self::html:h6)]"
		use="generate-id((..|preceding::html:h1|preceding::html:h2|preceding::html:h3|preceding::html:h4|preceding::html:h5|preceding::html:h6)[last()])"
	/>

	<xsl:key name="children"
		match="html:h1 | html:h2 | html:h3 | html:h4 | html:h5 | html:h6"
		use="generate-id(preceding-sibling::html:*[@heading-level and @heading-level &lt; current()/@heading-level][1])"
	/>


<xsl:template match="/">
	<xsl:choose>
		<xsl:when test="//html:h1">
			<xsl:variable name="firstHeading1" select="//html:h1[1]"/>		
			<xsl:variable name="contentBeforeFirstHeading1">
				<xsl:for-each select="$firstHeading1/preceding::node()">
					<xsl:value-of select="."/>
				</xsl:for-each>
			</xsl:variable>
			<xsl:if test="normalize-space($contentBeforeFirstHeading1)">
				<preface>
					<xsl:for-each select="$firstHeading1/ancestor-or-self::*">
						<!-- {{<xsl:value-of select="position()"/>}} -->
						<xsl:for-each select="preceding-sibling::*[not(preceding-sibling::html:h1|preceding-sibling::html:h2|preceding-sibling::html:h3|preceding-sibling::html:h4|preceding-sibling::html:h5|preceding-sibling::html:h6) and not(self::html:h1|self::html:h2|self::html:h3|self::html:h4|self::html:h5|self::html:h6)]">
							<!-- [<xsl:value-of select="local-name()"/>] -->
							<xsl:apply-templates select="."/>
						</xsl:for-each>
					</xsl:for-each>
					<xsl:for-each select="$firstHeading1/ancestor-or-self::*">
						<xsl:apply-templates select="preceding-sibling::*[self::html:h2 or self::html:h3 or self::html:h4 or self::html:h5 or self::html:h6]"/>
					</xsl:for-each>
				</preface>
			</xsl:if>
			<xsl:for-each select="//html:h1">
				<xsl:apply-templates select="."/>
			</xsl:for-each>
		</xsl:when>
		<xsl:otherwise>
			<preface>
				<xsl:apply-templates/>
			</preface>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="html:h1">
	<xsl:call-template name="make-section">
		<xsl:with-param name="current" select="1"/>
		<xsl:with-param name="previousNumber" select="1"/>
	</xsl:call-template>
</xsl:template> 

<xsl:template match="html:h2">
	<xsl:variable name="precedingHeading" select="preceding::html:*[self::html:h1]"/>
	<xsl:call-template name="make-section">
		<xsl:with-param name="current" select=" number('2') "/>
		<xsl:with-param name="previousNode" select="$precedingHeading"/>
	</xsl:call-template>
</xsl:template> 

<xsl:template match="html:h3">
	<xsl:variable name="precedingHeading" select="preceding::html:*[self::html:h1 or self::html:h2]"/>
	<xsl:call-template name="make-section">
		<xsl:with-param name="current" select=" number('3') "/>
		<xsl:with-param name="previousNode" select="$precedingHeading"/>
	</xsl:call-template>
</xsl:template> 

<xsl:template match="html:h4">
	<xsl:variable name="precedingHeading" select="preceding::html:*[self::html:h1 or self::html:h2 or self::html:h3]"/>
	<xsl:call-template name="make-section">
		<xsl:with-param name="current" select=" number('4') "/>
		<xsl:with-param name="previousNode" select="$precedingHeading"/>
	</xsl:call-template>
</xsl:template> 

<xsl:template match="html:h5">
	<xsl:variable name="precedingHeading" select="preceding::html:*[self::html:h1 or self::html:h2 or self::html:h3 or self::html:h4]"/>
	<xsl:call-template name="make-section">
		<xsl:with-param name="current" select=" number('5') "/>
		<xsl:with-param name="previousNode" select="$precedingHeading"/>
	</xsl:call-template>
</xsl:template> 

<xsl:template match="html:h6">
	<xsl:variable name="precedingHeading" select="preceding::html:*[self::html:h1 or self::html:h2 or self::html:h3 or self::html:h4 or self::html:h5]"/>
	<xsl:call-template name="make-section">
		<xsl:with-param name="current" select=" number('6') "/>
		<xsl:with-param name="previousNode" select="$precedingHeading"/>
	</xsl:call-template>
</xsl:template> 

<xsl:template name="make-section">
	<xsl:param name="current"/>
	<xsl:param name="previousNode"/>
	<xsl:param name="previousNumber"/>

	<xsl:variable name="prev">
		<xsl:choose>
			<xsl:when test="$previousNode">
				<xsl:value-of select="number(translate(local-name($previousNode), 'h', ''))"/>
			</xsl:when>
			<xsl:when test="$previousNumber">
				<xsl:value-of select="$previousNumber"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="number('1')"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	<xsl:choose>
		<xsl:when test="$current &gt; $prev+1">
			<xsl:text disable-output-escaping="yes">&lt;sect</xsl:text><xsl:value-of select="$prev"/><xsl:text disable-output-escaping="yes">&gt;</xsl:text>
			<xsl:call-template name="make-section">
				<xsl:with-param name="current" select="$current"/>
				<xsl:with-param name="previousNumber" select="$prev+1"/>
			</xsl:call-template>
			<xsl:text disable-output-escaping="yes">&lt;/sect</xsl:text><xsl:value-of select="$prev"/><xsl:text disable-output-escaping="yes">&gt;</xsl:text>
		</xsl:when>
		<xsl:when test="$current = 1">
			<chapter>
				<xsl:if test="normalize-space(.)">
					<title>
						<xsl:apply-templates/>
					</title>
				</xsl:if>
				<xsl:apply-templates select="key('headchildren', generate-id())"/>
				<xsl:apply-templates select="key('children', generate-id())"/>
				<xsl:call-template name="workAroundXslCurrentFunctionKeyBug"/>
			</chapter>
		</xsl:when>
		<xsl:otherwise>
			<xsl:text disable-output-escaping="yes">&lt;sect</xsl:text><xsl:value-of select="$current - 1"/><xsl:text disable-output-escaping="yes">&gt;</xsl:text>
			<xsl:if test="normalize-space(.)">
				<title>
					<xsl:apply-templates/>
				</title>
			</xsl:if>
			<xsl:apply-templates select="key('headchildren', generate-id())"/>
			<xsl:apply-templates select="key('children', generate-id())"/>
			<xsl:call-template name="workAroundXslCurrentFunctionKeyBug"/>
			<xsl:text disable-output-escaping="yes">&lt;/sect</xsl:text><xsl:value-of select="$current - 1"/><xsl:text disable-output-escaping="yes">&gt;</xsl:text>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template name="workAroundXslCurrentFunctionKeyBug">
	<xsl:if test="not(key('children', generate-id()))">
		<xsl:variable name="currentGenerateId" select="generate-id()"/>
		<xsl:variable name="currentOutlineLevel" select="number(@heading-level)"/>
		<!-- [[CurrentOutlineLevel:<xsl:value-of select="$currentOutlineLevel"/>]] -->
		<xsl:for-each select="following-sibling::html:*[self::html:h1 or self::html:h2 or self::html:h3 or self::html:h4 or self::html:h5 or self::html:h6]">
			<xsl:variable name="subheadingOutlineLevel" select="@heading-level"/>
			<xsl:variable name="firstPrecedingHeading" select="./preceding-sibling::html:*[(self::html:h1 or self::html:h2 or self::html:h3 or self::html:h4 or self::html:h5 or self::html:h6) and @heading-level &lt; $subheadingOutlineLevel][1]"/>
			<xsl:if test="generate-id($firstPrecedingHeading) = $currentGenerateId">
				<xsl:apply-templates select="."/>
			</xsl:if>
		</xsl:for-each>
	</xsl:if>
</xsl:template>


<!-- **********************
***************************
  Elements within sections
***************************
*************************** -->

<xsl:template match="html:br">
	<literal role="linebreak">
		<html:br/>
	</literal>
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="html:p[@class='documentTitle']"/>

<xsl:template match="html:p">
	<para>
		<xsl:if test="@class">
			<xsl:attribute name="role">
				<xsl:value-of select="@class"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:apply-templates/>		
	</para>
</xsl:template>

<xsl:template match="html:ol">
	<db:orderedlist>
		<xsl:apply-templates/>
	</db:orderedlist>
</xsl:template>

<xsl:template match="html:ul">
	<db:itemizedlist>
		<xsl:apply-templates/>
	</db:itemizedlist>
</xsl:template>

<xsl:template match="html:li">
	<db:listitem>
		<xsl:apply-templates/>
	</db:listitem>
</xsl:template>

<xsl:template match="html:img">
	<xsl:element name="db:mediaobject">
		<db:imageobject>
			<db:imagedata fileref="{@src}" format="{substring-after(@src, '.')}">
				<xsl:if test="@width">
					<xsl:attribute name="width"><xsl:value-of select="@width"/></xsl:attribute>
				</xsl:if>
				<xsl:if test="@height">
					<xsl:attribute name="height"><xsl:value-of select="@height"/></xsl:attribute>
				</xsl:if>
			</db:imagedata>
		</db:imageobject>
		<xsl:if test="normalize-space(@alt)">
			<db:caption>
				<db:para><xsl:value-of select="@alt"/></db:para>
			</db:caption>
		</xsl:if>
	</xsl:element>
</xsl:template>

<xsl:template match="html:em | html:i">
	<xsl:element name="db:emphasis">
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>

<xsl:template match="html:b | html:strong">
	<xsl:element name="db:emphasis">
		<xsl:attribute name="role">bold</xsl:attribute>
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>

<xsl:template match="html:a">
	<xsl:element name="db:link">
		<xsl:attribute name="xlink:href">
			<xsl:value-of select="@href"/>
		</xsl:attribute>
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>

<xsl:template match="html:table">
	<table>
		<xsl:apply-templates/>		
	</table>
</xsl:template>


<xsl:template match="html:tr">
	<row>
		<xsl:apply-templates/>		
	</row>
</xsl:template>

<xsl:template match="html:thead">
	<thead>
		<xsl:apply-templates/>		
	</thead>
</xsl:template>

<xsl:template match="html:tbody">
	<tbody>
		<xsl:apply-templates/>		
	</tbody>
</xsl:template>

<xsl:template match="html:tfoot">
	<tfoot>
		<xsl:apply-templates/>		
	</tfoot>
</xsl:template>

<xsl:template match="html:td | html:th">
	<entry>
		<xsl:if test="self::html:th">
			<xsl:attribute name="role">heading</xsl:attribute>
		</xsl:if>
		<xsl:if test="@colspan">
			<xsl:attribute name="html:colspan"><xsl:value-of select="@colspan"/></xsl:attribute>
		</xsl:if>
		<xsl:if test="@rowspan">
			<xsl:attribute name="html:rowspan"><xsl:value-of select="@rowspan"/></xsl:attribute>
		</xsl:if>
		<xsl:apply-templates/>		
	</entry>
</xsl:template>

<xsl:template match="html:div">
	<html:div>
		<xsl:if test="@class"><xsl:attribute name="class"><xsl:value-of select="@class"/></xsl:attribute></xsl:if>
		<xsl:if test="@id"><xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute></xsl:if>
		<xsl:if test="@style"><xsl:attribute name="style"><xsl:value-of select="@style"/></xsl:attribute></xsl:if>
	</html:div>
</xsl:template>

<xsl:template match="html:*">
	<xsl:copy>
		<xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
</xsl:template>

</xsl:stylesheet>
