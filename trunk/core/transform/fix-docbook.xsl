<?xml version='1.0' encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"

	xmlns:db="http://docbook.org/ns/docbook"

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

	xmlns:ooo="http://openoffice.org/2004/office"
	xmlns:ooow="http://openoffice.org/2004/writer"
	xmlns:oooc="http://openoffice.org/2004/calc"
	xmlns:config="http://openoffice.org/2001/config"

	xmlns:xlink="http://www.w3.org/1999/xlink"
	xmlns:math="http://www.w3.org/1998/Math/MathML"
	xmlns:dom="http://www.w3.org/2001/xml-events"
	xmlns:xforms="http://www.w3.org/2002/xforms"
	xmlns:xsd="http://www.w3.org/2001/XMLSchema"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xmlns:html="http://www.w3.org/1999/xhtml"

	exclude-result-prefixes="office dc style text table draw fo meta number svg chart dr3d form script xlink math ooo ooow oooc dom xforms xsd xsi config"

	office:class="text"
	>

	<xsl:output method="xml" omit-xml-declaration="no"  />

<xsl:variable name="bookTitle">
	<xsl:choose>
		<xsl:when test="//db:para[@role='dc.title']"><xsl:value-of select="//db:para[@role='dc.title']"/></xsl:when>
		<xsl:when test="normalize-space(/db:book/db:title)"><xsl:value-of select="/db:book/db:title"/></xsl:when>
		<xsl:otherwise>[no title]</xsl:otherwise>
	</xsl:choose>
</xsl:variable>

<xsl:template match="/db:book">
	<db:book>
		<db:title><xsl:value-of select="$bookTitle"/></db:title>
		<xsl:if test="db:info/db:description or //*[@role='dc.description']">
			<db:abstract>
				<db:para>
					<xsl:choose>
						<xsl:when test="//*[@role='dc.description']"><xsl:value-of select="//*[@role='dc.description']"/></xsl:when>
						<xsl:when test="db:info/db:description"><xsl:value-of select="db:description"/></xsl:when>
					</xsl:choose>
				</db:para>
			</db:abstract>
		</xsl:if>
		<xsl:apply-templates select="*[not(self::db:title)]"/>
	</db:book>
</xsl:template>

<xsl:template match="db:title">
	<xsl:if test="ancestor::db:chapter or ancestor::db:preface">
		<db:title>
			<xsl:apply-templates/>
		</db:title>
	</xsl:if>
</xsl:template>

<xsl:template match="db:chapter">
	<xsl:variable name="childText">
		<xsl:for-each select="descendant-or-self::node()">
			<xsl:value-of select="."/>
		</xsl:for-each>
	</xsl:variable>
	<xsl:if test="normalize-space($childText)">
		<db:chapter>
			<xsl:apply-templates/>
		</db:chapter>
	</xsl:if>
</xsl:template>

<xsl:template match="db:sect1 | db:sect2 | db:sect3 | db:sect4 | db:sect5 | db:sect6 | db:sect7 | db:sect8 | db:sect9">
	<xsl:variable name="childText">
		<xsl:for-each select="descendant-or-self::node()">
			<xsl:value-of select="."/>
		</xsl:for-each>
	</xsl:variable>
	<xsl:if test="normalize-space($childText)">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:if>
</xsl:template>

<xsl:template match="db:info">
	<db:info>
		<db:title><xsl:value-of select="$bookTitle"/></db:title>
		<xsl:apply-templates select="*[not(db:title)]"/>
	</db:info>
</xsl:template>

<xsl:template match="db:authorgroup">
	<xsl:variable name="unique-authors" select="db:author[not(.=following::text())]" />
	<db:authorgroup>
		<xsl:for-each select="$unique-authors">
			<db:author><xsl:value-of select="."/></db:author>
		</xsl:for-each>
	</db:authorgroup>
</xsl:template>

<xsl:template match="db:superscript[db:footnote]">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="db:preface">
	<db:preface>
		<xsl:apply-templates/>
	</db:preface>
</xsl:template>

<xsl:template match="db:table">
	<db:table>
		<xsl:choose>
			<xsl:when test="db:tbody">
				<xsl:apply-templates/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates select="db:thead"/>
				<db:tbody>
					<xsl:apply-templates select="*[not(self::db:thead)]"/>
				</db:tbody>
				<xsl:apply-templates select="db:tfoot"/>				
			</xsl:otherwise>
		</xsl:choose>
	</db:table>
</xsl:template>

<xsl:template match="db:row[descendant::db:title]">
	<db:thead>
		<db:row>
			<xsl:for-each select="db:entry">
				<db:entry><p><xsl:value-of select="."/></p></db:entry>
			</xsl:for-each>
		</db:row>
	</db:thead>
</xsl:template>

<xsl:template match="db:footnote/text()"/>

<xsl:template match="text()" mode="toc"/>

<xsl:template match="@*|node()">
	<xsl:copy>
		<xsl:apply-templates select="@*|node()"/>
	</xsl:copy>
</xsl:template>

</xsl:stylesheet>
