<?xml version='1.0' encoding="UTF-8"?>
<!--
	Docvert changes to this file were made in order to support
	Oasis Open Document (as the original bundled in OOo 1.9.122
	was coded for OpenOffice 1.x),

	- changes made
		- many fixes for XSLT in Ubuntu 7.04.
		- namespace changes
		- root node becomes office:document-content.
		- in match="office:body" changed an apply-templates line matching headings to read (extra office:text depth):
			<xsl:apply-templates select="office:text/text:h[@text:outline-level='1']"/>
		- change text:level to text:outline-level
		- include content before first heading,
			<xsl:apply-templates select="office:text/text:*[not(local-name() = 'h') and count(following-sibling::text:h) &gt; 0 and count(preceding-sibling::text:h) = 0]"/>			
		- maintain linebreaks using xhtml namespace, xhtml:br
		- added support for italics/bold/foreground/background colour as emphasis@role='bold' and fo:*
		- added support for uncommon lists
		- moved to DOCBOOK 5.0!! with namespace xmlns:db="http://docbook.org/ns/docbook"
		- added support for images
		- pulling in styles outside automatic-styles.
		- working around bugs in common XSL implementations of xsl:keys
		- adding support for preformatted text
		- all kinds of other stuff not worth mentioning

	-For reference, original OOo 1.x namespaces,
		xmlns:style="http://openoffice.org/2000/style"
		xmlns:text="http://openoffice.org/2000/text"
		xmlns:office="http://openoffice.org/2000/office"
		xmlns:table="http://openoffice.org/2000/table"
		xmlns:draw="http://openoffice.org/2000/drawing"
		xmlns:fo="http://www.w3.org/1999/XSL/Format"
		xmlns:xlink="http://www.w3.org/1999/xlink"
		xmlns:meta="http://openoffice.org/2000/meta"
		xmlns:number="http://openoffice.org/2000/datastyle"
		xmlns:svg="http://www.w3.org/2000/svg"
		xmlns:chart="http://openoffice.org/2000/chart"
		xmlns:dr3d="http://openoffice.org/2000/dr3d"
		xmlns:math="http://www.w3.org/1998/Math/MathML"
		xmlns:form="http://openoffice.org/2000/form"
		xmlns:script="http://openoffice.org/2000/script"

 #  The Contents of this file are made available subject to the terms of
 #  either of the following licenses
 #
 #         - GNU Lesser General Public License Version 2.1
 #         - Sun Industry Standards Source License Version 1.1
 #
 #  Sun Microsystems Inc., October, 2000
 #
 #  GNU Lesser General Public License Version 2.1
 #  =============================================
 #  Copyright 2000 by Sun Microsystems, Inc.
 #  901 San Antonio Road, Palo Alto, CA 94303, USA
 #
 #  This library is free software; you can redistribute it and/or
 #  modify it under the terms of the GNU Lesser General Public
 #  License version 2.1, as published by the Free Software Foundation.
 #
 #  This library is distributed in the hope that it will be useful,
 #  but WITHOUT ANY WARRANTY; without even the implied warranty of
 #  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 #  Lesser General Public License for more details.
 #
 #  You should have received a copy of the GNU Lesser General Public
 #  License along with this library; if not, write to the Free Software
 #  Foundation, Inc., 59 Temple Place, Suite 330, Boston,
 #  MA  02111-1307  USA
 #
 #
 #  Sun Industry Standards Source License Version 1.1
 #  =================================================
 #  The contents of this file are subject to the Sun Industry Standards
 #  Source License Version 1.1 (the "License"); You may not use this file
 #  except in compliance with the License. You may obtain a copy of the
 #  License at http://www.openoffice.org/license.html.
 #
 #  Software provided under this License is provided on an "AS IS" basis,
 #  WITHOUT WARRANTY OF ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING,
 #  WITHOUT LIMITATION, WARRANTIES THAT THE SOFTWARE IS FREE OF DEFECTS,
 #  MERCHANTABLE, FIT FOR A PARTICULAR PURPOSE, OR NON-INFRINGING.
 #  See the License for the specific provisions governing your rights and
 #  obligations concerning the Software.
 #
 #  The Initial Developer of the Original Code is: Sun Microsystems, Inc.
 #
 #  Copyright: 2000 by Sun Microsystems, Inc.
 #
 #  All Rights Reserved.
 #
 #  Contributor(s): _______________________________________
 #
 #
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"

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

	xmlns:docvert="urn:holloway.co.nz:names:docvert:2"
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

	xmlns:html="http://www.w3.org/1999/xhtml"

	>

	<xsl:output method="xml" indent="yes" omit-xml-declaration="no" version="1.0" encoding="UTF-8" />

	<xsl:preserve-space elements="text:h text:p"/>

	<xsl:key name='headchildren'
		match="text:p | table:table | text:ordered-list | text:list | draw:frame | draw:image | svg:desc | office:annotation | text:unordered-list | text:footnote | text:a | text:list-item | draw:plugin | draw:text-box | text:footnote-body | text:section"
		use="generate-id((..|preceding-sibling::text:h[@text:outline-level='1']|preceding-sibling::text:h[@text:outline-level='2']|preceding-sibling::text:h[@text:outline-level='3']|preceding-sibling::text:h[@text:outline-level='4']|preceding-sibling::text:h[@text:outline-level='5'])[last()])"
	/>

	<xsl:key name="children"
		match="text:h[@text:outline-level &gt; '1' and @text:outline-level &lt; '10']"
		use="generate-id(preceding-sibling::text:h[@text:outline-level &lt; current()/@text:outline-level][1])"
	/>

	<xsl:key name="lists"
		match="text:list[not(parent::text:list-item)] | text:ordered-list[not(parent::text:list-item)] | text:unordered-list[not(parent::text:list-item)]"
		use="generate-id(preceding-sibling::*[not(self::text:unordered-list | self::text:list | self::text:ordered-list)][1])"
	/>

	<xsl:key name="styleNames" match="office:automatic-styles/style:style" use="@style:name"/>

	<xsl:variable name="lowerCaseLetters">abcdefghijklmnopqrstuvwxyz</xsl:variable>

	<xsl:variable name="upperCaseLetters">ABCDEFGHIJKLMNOPQRSTUVWXYZ</xsl:variable>

<xsl:template match="/office:document-content">
	<xsl:element name="db:book">
		<xsl:variable name="setLanguage" select="/office:document-content/office:meta/dc:language"/>
		<xsl:attribute name="xml:lang">
			<xsl:choose>
				<xsl:when test="$setLanguage"><xsl:value-of select="$setLanguage"/></xsl:when>
				<xsl:otherwise>en</xsl:otherwise>
			</xsl:choose>
		</xsl:attribute>
		<xsl:attribute name="version">
			<xsl:text>5.0</xsl:text>
		</xsl:attribute>
		<xsl:for-each select="//text:p">
			<xsl:variable name="docbookTitle" select="node()"/>
			<xsl:variable name="textStyle" select="@text:style-name"/>
			<xsl:choose>
				<xsl:when test="translate($textStyle, $upperCaseLetters, $lowerCaseLetters) = 'title' ">
					<xsl:if test="normalize-space($docbookTitle)">
						<db:title>
							<xsl:apply-templates select="$docbookTitle"/>
						</db:title>
					</xsl:if>
				</xsl:when>
				<xsl:otherwise>
					<xsl:variable name="referencedStyle" select="//office:automatic-styles/style:style[@style:name = $textStyle]"/>
					<xsl:for-each select="$referencedStyle">
						<xsl:variable name="referencedParentStyleName" select="@style:parent-style-name"/>
						<xsl:variable name="referencedParentStyle" select="//docvert:external-file/office:styles/style:style[@style:name = $referencedParentStyleName]"/>
						<xsl:for-each select="$referencedParentStyle">
							<xsl:if test="translate($referencedParentStyleName, $upperCaseLetters, $lowerCaseLetters) = 'title' ">
								<xsl:if test="normalize-space($docbookTitle)">
									<db:title>
										<xsl:apply-templates select="$docbookTitle"/>
									</db:title>
								</xsl:if>
							</xsl:if>
						</xsl:for-each>
					</xsl:for-each>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:for-each>
		<xsl:apply-templates select="office:body"/>
	</xsl:element>
</xsl:template>

<xsl:template match="office:body">
	<db:preface>
		<xsl:apply-templates select="key('headchildren', generate-id())"/>
		<xsl:apply-templates select="office:text/text:*[not(local-name() = 'h') and count(following-sibling::text:h) &gt; 0 and count(preceding-sibling::text:h) = 0]"/>
		<xsl:if test="not(//text:h[@text:outline-level='1'])">
			<xsl:apply-templates select="//text:h"/>
		</xsl:if>
		<xsl:if test="not(//text:h)">
			<xsl:apply-templates/>
		</xsl:if>
	</db:preface>
	<xsl:apply-templates select="office:text/text:h[@text:outline-level='1']"/>
	<xsl:if test="not(//text:h)">
		<xsl:apply-templates/>
	</xsl:if>
</xsl:template>

<xsl:template match="text:h[@text:outline-level='1']">
	<xsl:choose>
		<xsl:when test=".='Abstract'">
			<db:abstract>
				<xsl:apply-templates select="key('headchildren', generate-id())"/>
     				<xsl:apply-templates select="key('children', generate-id())"/>
			</db:abstract>
		</xsl:when>
		<xsl:otherwise>
			<xsl:call-template name="make-section">
				<xsl:with-param name="current" select="@text:outline-level"/>
				<xsl:with-param name="prev" select="1"/>
			</xsl:call-template>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="text:h[@text:outline-level='2'] | text:h[@text:outline-level='3']| text:h[@text:outline-level='4'] | text:h[@text:outline-level='5']">
	<xsl:variable name="level" select="@text:outline-level"></xsl:variable>
	<xsl:call-template name="make-section">
		<xsl:with-param name="current" select="$level"/>
		<xsl:with-param name="prev" select="preceding-sibling::text:h[@text:outline-level &lt; $level][1]/@text:outline-level "/>
	</xsl:call-template>
</xsl:template>

<xsl:template name="make-section">
	<xsl:param name="current"/>
	<xsl:param name="prev"/>
	<xsl:choose>
		<xsl:when test="$current &gt; $prev+1">
			<xsl:text disable-output-escaping="yes">&lt;db:sect</xsl:text><xsl:value-of select="$prev "/><xsl:text disable-output-escaping="yes">&gt;</xsl:text>
			<xsl:call-template name="make-section">
				<xsl:with-param name="current" select="$current"/>
				<xsl:with-param name="prev" select="$prev +1"/>
			</xsl:call-template>
			<xsl:text disable-output-escaping="yes">&lt;/db:sect</xsl:text><xsl:value-of select="$prev "/><xsl:text disable-output-escaping="yes">&gt;</xsl:text>
		</xsl:when>
		<xsl:otherwise>
			<xsl:choose>
				<xsl:when test="$current = 1">
					<db:chapter>
						<xsl:variable name="title">
							<xsl:for-each select="descendant::node()">
								<xsl:value-of select="."/>
							</xsl:for-each>
						</xsl:variable>
						<xsl:if test="normalize-space($title)">
							<db:title>
								<xsl:apply-templates/>
							</db:title>
						</xsl:if>
						<xsl:apply-templates select="key('headchildren', generate-id())"/>
						<xsl:apply-templates select="key('children', generate-id())"/>
						<xsl:call-template name="workAroundXslCurrentFunctionKeyBug"/>
					</db:chapter>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text disable-output-escaping="yes">&lt;db:sect</xsl:text><xsl:value-of select="$current - 1"/><xsl:text disable-output-escaping="yes">&gt;</xsl:text>
					<xsl:if test="normalize-space(.)">
						<db:title>
							<xsl:apply-templates/>
						</db:title>
					</xsl:if>
					<xsl:apply-templates select="key('headchildren', generate-id())"/>
					<xsl:apply-templates select="key('children', generate-id())"/>
					<xsl:call-template name="workAroundXslCurrentFunctionKeyBug"/>
					<xsl:text disable-output-escaping="yes">&lt;/db:sect</xsl:text><xsl:value-of select="$current - 1"/><xsl:text disable-output-escaping="yes">&gt;</xsl:text>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template name="workAroundXslCurrentFunctionKeyBug">
	<xsl:if test="not(key('children', generate-id()))">
		<xsl:variable name="currentGenerateId" select="generate-id()"/>
		<xsl:variable name="currentOutlineLevel" select="number(@text:outline-level)"/>
		<xsl:for-each select="following-sibling::text:h">
			<xsl:variable name="subheadingOutlineLevel" select="@text:outline-level"/>
			<xsl:variable name="firstPrecedingHeading" select="./preceding-sibling::text:h[@text:outline-level &lt; $subheadingOutlineLevel][1]"/>
			<xsl:if test="generate-id($firstPrecedingHeading) = $currentGenerateId">
				<xsl:apply-templates select="."/>
			</xsl:if>
		</xsl:for-each>
	</xsl:if>
</xsl:template>

<xsl:template match="text:p">
	<xsl:variable name="textStyle" select="@text:style-name"/>
	<xsl:variable name="referencedStyle" select="//office:automatic-styles/style:style[@style:name = $textStyle]"/>
	<xsl:variable name="doNotInheritParentItalics" select="$referencedStyle/style:text-properties/@fo:font-style"/>
	<xsl:variable name="doNotInheritParentBold" select="$referencedStyle/style:text-properties/@fo:font-weight"/>
	
	<xsl:variable name="referencedParentStyleName" select="$referencedStyle/@style:parent-style-name"/>
	<xsl:variable name="referencedParentStyle" select="//docvert:external-file/office:styles/style:style[@style:name = $referencedParentStyleName]"/>
	<xsl:variable name="boolParentStyleBoldOrItalics" select="$referencedParentStyle[style:text-properties/@fo:font-weight = 'bold' or style:text-properties/@fo:font-style = 'italic']"/>

	<xsl:variable name="lowercaseReferencedParentStyleName" select="translate($referencedParentStyleName, $upperCaseLetters, $lowerCaseLetters)"/>

	<xsl:if test="normalize-space(.) or descendant::draw:frame">
		<xsl:choose>
			<xsl:when test="starts-with($textStyle, 'preformatted')">
				<xsl:element name="db:literallayout">
					<xsl:attribute name="role"><xsl:value-of select="$textStyle"/></xsl:attribute>
					<xsl:apply-templates/>
				</xsl:element>
			</xsl:when>
			<xsl:when test="starts-with($referencedParentStyleName, 'preformatted')">
				<xsl:element name="db:literallayout">
					<xsl:attribute name="role"><xsl:value-of select="$referencedParentStyleName"/></xsl:attribute>
					<xsl:apply-templates/>
				</xsl:element>
			</xsl:when>
			<xsl:otherwise>
				<!-- <xsl:if test="not(contains($lowercaseReferencedParentStyleName, 'title'))"> -->
				<xsl:element name="db:para">
					<!-- <xsl:value-of select="$referencedParentStyleName"/> -->
					<xsl:call-template name="includeStyleNameWhenUseful"/>
					<xsl:call-template name="detectDublinCoreMetaData">
						<xsl:with-param name="possibleDCName"><xsl:value-of select="@text:style-name"/></xsl:with-param>
					</xsl:call-template>
					<xsl:choose>
						<xsl:when test="$doNotInheritParentBold = 'bold' or $doNotInheritParentItalics = 'italic' ">
							<db:emphasis>
								<xsl:if test="$doNotInheritParentBold = 'bold' ">
									<xsl:attribute name="role">
										<xsl:text>bold</xsl:text>
									</xsl:attribute>
								</xsl:if>
								<xsl:apply-templates/>
							</db:emphasis>
						</xsl:when>
						<xsl:when test="$boolParentStyleBoldOrItalics and (not($doNotInheritParentBold) or not($doNotInheritParentItalics))">
							<db:emphasis>
								<xsl:if test="$referencedParentStyle[style:text-properties/@fo:font-weight = 'bold'] ">
									<xsl:attribute name="role">
										<xsl:text>bold</xsl:text>
									</xsl:attribute>
								</xsl:if>
								<xsl:apply-templates/>
							</db:emphasis>
						</xsl:when>
						<xsl:otherwise>
							<xsl:apply-templates/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:element>
				<!-- </xsl:if> -->
			</xsl:otherwise>
		</xsl:choose>
	</xsl:if>
</xsl:template>

<xsl:template name="includeStyleNameWhenUseful">
	<xsl:choose>
		<xsl:when test="contains(@text:style-name, 'dc.')">
			<xsl:attribute name="role"><xsl:value-of select="@text:style-name"/></xsl:attribute>
		</xsl:when>
		<xsl:when test="contains(key('styleNames', @text:style-name)/@style:parent-style-name, 'dc.')">
			<xsl:attribute name="role"><xsl:value-of select="key('styleNames', @text:style-name)/@style:parent-style-name"/></xsl:attribute>
		</xsl:when>
	</xsl:choose>
</xsl:template>

<xsl:template match="draw:frame">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="text:s"><!-- an additional space -->
	<xsl:element name="db:literal">
		<xsl:attribute name="role">additionalSpace</xsl:attribute>
		<xsl:text>&#160;</xsl:text>
	</xsl:element>
</xsl:template>

<xsl:template match="svg:desc">
	<db:textobject>
		<db:para>
			<xsl:call-template name="includeStyleNameWhenUseful"/>
			<xsl:apply-templates/>
		</db:para>
	</db:textobject>
</xsl:template>

<xsl:template match="draw:image">
	<db:mediaobject>
		<db:imageobject>
			<db:imagedata fileref="{@xlink:href}">
				<!-- not a real attribute, do they ever come with format or mime type info? matthew@holloway.co.nz -->
				<xsl:attribute name="format">
					<xsl:choose>
						<xsl:when test="@format"><xsl:value-of select="@format"/></xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="substring-after(@xlink:href, '.')"/> <!-- detecting format by file extension... ugh -->
						</xsl:otherwise>
					</xsl:choose>
				</xsl:attribute>
			</db:imagedata>
		</db:imageobject>
	</db:mediaobject>
</xsl:template>

<xsl:template name="detectDublinCoreMetaData">
	<xsl:param name="possibleDCName"/>
	<xsl:variable name="lowercasePossibleDBName">
		<xsl:value-of select="translate($possibleDCName, concat($upperCaseLetters, ':'), concat($lowerCaseLetters, '.'))"/>
	</xsl:variable>
	<xsl:if test="contains(@text:style-name, 'dc.')">
		<xsl:choose>
			<xsl:when test="contains($lowercasePossibleDBName, 'dc.title')">
				<xsl:attribute name="dc:title"><xsl:value-of select="normalize-space(.)"/></xsl:attribute>
			</xsl:when>
			<xsl:when test="contains($lowercasePossibleDBName, 'dc.creator')">
				<xsl:attribute name="dc:creator"><xsl:value-of select="normalize-space(.)"/></xsl:attribute>
			</xsl:when>
			<xsl:when test="contains($lowercasePossibleDBName, 'dc.subject')">
				<xsl:attribute name="dc:subject"><xsl:value-of select="normalize-space(.)"/></xsl:attribute>
			</xsl:when>
			<xsl:when test="contains($lowercasePossibleDBName, 'dc.description')">
				<xsl:attribute name="dc:description"><xsl:value-of select="normalize-space(.)"/></xsl:attribute>
			</xsl:when>
			<xsl:when test="contains($lowercasePossibleDBName, 'dc.publisher')">
				<xsl:attribute name="dc:publisher"><xsl:value-of select="normalize-space(.)"/></xsl:attribute>
			</xsl:when>
			<xsl:when test="contains($lowercasePossibleDBName, 'dc.contributor')">
				<xsl:attribute name="dc:contributor"><xsl:value-of select="normalize-space(.)"/></xsl:attribute>
			</xsl:when>
			<xsl:when test="contains($lowercasePossibleDBName, 'dc.date')">
				<xsl:attribute name="dc:date"><xsl:value-of select="normalize-space(.)"/></xsl:attribute>
			</xsl:when>
			<xsl:when test="contains($lowercasePossibleDBName, 'dc.type')">
				<xsl:attribute name="dc:type"><xsl:value-of select="normalize-space(.)"/></xsl:attribute>
			</xsl:when>
			<xsl:when test="contains($lowercasePossibleDBName, 'dc.format')">
				<xsl:attribute name="dc:format"><xsl:value-of select="normalize-space(.)"/></xsl:attribute>
			</xsl:when>
			<xsl:when test="contains($lowercasePossibleDBName, 'dc.identifier')">
				<xsl:attribute name="dc:identifier"><xsl:value-of select="normalize-space(.)"/></xsl:attribute>
			</xsl:when>
			<xsl:when test="contains($lowercasePossibleDBName, 'dc.source')">
				<xsl:attribute name="dc:source"><xsl:value-of select="normalize-space(.)"/></xsl:attribute>
			</xsl:when>
			<xsl:when test="contains($lowercasePossibleDBName, 'dc.language')">
				<xsl:attribute name="dc:language"><xsl:value-of select="normalize-space(.)"/></xsl:attribute>
			</xsl:when>
			<xsl:when test="contains($lowercasePossibleDBName, 'dc.relation')">
				<xsl:attribute name="dc:relation"><xsl:value-of select="normalize-space(.)"/></xsl:attribute>
			</xsl:when>
			<xsl:when test="contains($lowercasePossibleDBName, 'dc.coverage')">
				<xsl:attribute name="dc:coverage"><xsl:value-of select="normalize-space(.)"/></xsl:attribute>
			</xsl:when>
			<xsl:when test="contains($lowercasePossibleDBName, 'dc.rights')">
				<xsl:attribute name="dc:rights"><xsl:value-of select="normalize-space(.)"/></xsl:attribute>
			</xsl:when>
		</xsl:choose>
	</xsl:if>
</xsl:template>

<xsl:template match="office:meta">
	<!--<xsl:apply-templates/>-->
</xsl:template>

<xsl:template match="meta:editing-cycles">
</xsl:template>

<xsl:template match="meta:user-defined">
</xsl:template>

<xsl:template match="meta:editing-duration">
</xsl:template>

<xsl:template match="dc:language">
</xsl:template>

<xsl:template match="dc:date">
	<!--
	<pubdate>
		<xsl:value-of select="substring-before(.,'T')"/>
	</pubdate>
	-->
</xsl:template>

<xsl:template match="meta:creation-date"/>

<xsl:template match="office:styles">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="office:script">
</xsl:template>

<xsl:template match="office:settings">
</xsl:template>

<xsl:template match="office:font-decls">
</xsl:template>

<xsl:template match="text:section">
<xsl:choose>
	<xsl:when test="@text:name='ArticleInfo'">
		<db:articleinfo>
			<db:title>
				<xsl:attribute name="id"><xsl:number count="*" level="multiple" /></xsl:attribute>
				<xsl:value-of select="text:p[@text:style-name='Document Title']"/>
			</db:title>
			<db:subtitle><xsl:value-of select="text:p[@text:style-name='Document SubTitle']"/></db:subtitle>
			<db:edition><xsl:value-of select="text:p/text:variable-set[@text:name='articleinfo.edition']"/></db:edition>
			<xsl:for-each select="text:p/text:variable-set[substring-after(@text:name,'articleinfo.releaseinfo')]">
				<db:releaseinfo>
					<xsl:value-of select="."/>
				</db:releaseinfo>
			</xsl:for-each>
			<xsl:call-template name="ArticleInfo"><xsl:with-param name="level" select="0"/></xsl:call-template>
		</db:articleinfo>
	</xsl:when>
	<xsl:when test="@text:name='Abstract'">
		<db:abstract>
			<xsl:apply-templates/>
		</db:abstract>
	</xsl:when>
	<xsl:when test="@text:name='Appendix'">
		<db:appendix>
			<xsl:apply-templates/>
		</db:appendix>
	</xsl:when>
	<xsl:otherwise>
		<xsl:variable name="numberOfAncestorSections" select="count(ancestor::text:section)"/>
		<xsl:variable name="sectvar"><xsl:text>db:sect</xsl:text><xsl:value-of select="$numberOfAncestorSections"/></xsl:variable>	
		<xsl:variable name="idvar"><xsl:text> id="</xsl:text><xsl:value-of select="@text:name"/><xsl:text>"</xsl:text></xsl:variable>

		<xsl:choose>
			<xsl:when test="$numberOfAncestorSections = 0">
				<db:chapter>
					<xsl:apply-templates/>
				</db:chapter>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text disable-output-escaping="yes">&lt;db:</xsl:text><xsl:value-of select="$sectvar"/><xsl:value-of select="$idvar"/><xsl:text  disable-output-escaping="yes">&gt;</xsl:text>
					<xsl:apply-templates/>
				<xsl:text disable-output-escaping="yes">&lt;/db:</xsl:text><xsl:value-of select="$sectvar"/><xsl:text disable-output-escaping="yes">&gt;</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:otherwise>

	</xsl:choose>
</xsl:template>

<xsl:template name="ArticleInfo">
	<xsl:param name="level"/>
	<xsl:variable name="author"><xsl:value-of select="concat('articleinfo.author_','', $level)"/></xsl:variable>
	<xsl:if test="text:p/text:variable-set[contains(@text:name, $author )]">
		<xsl:call-template name="Author"><xsl:with-param name="AuthorLevel" select="0"/></xsl:call-template>
		<xsl:call-template name="Copyright"><xsl:with-param name="CopyrightLevel" select="0"/></xsl:call-template>	
	</xsl:if>	
</xsl:template>

<xsl:template name="Copyright">
	<xsl:param name="CopyrightLevel"/>
	
	<xsl:variable name="Copyright"><xsl:value-of select="concat('articleinfo.copyright_','', $CopyrightLevel)"/></xsl:variable>
	
	<xsl:if test="text:p/text:variable-set[contains(@text:name,$Copyright)]">
		<db:copyright>
			<xsl:call-template name="Year">
				<xsl:with-param name="CopyrightLevel" select="$CopyrightLevel"/>
				<xsl:with-param name="YearlLevel" select="0"/>
			</xsl:call-template>
			<xsl:call-template name="Holder">
				<xsl:with-param name="CopyrightLevel" select="$CopyrightLevel"/>
				<xsl:with-param name="HolderlLevel" select="0"/>
			</xsl:call-template>
		</db:copyright>
	</xsl:if>
</xsl:template>

<xsl:template name="Year">
	<xsl:param name="CopyrightLevel"/>
	<xsl:param name="YearLevel"/>
	<xsl:variable name="Copyright"><xsl:value-of select="concat('articleinfo.copyright_','', $CopyrightLevel)"/></xsl:variable>
	<xsl:variable name="Year"><xsl:value-of select="concat($Copyright,'',concat('.year_','',$YearLevel))"/></xsl:variable>

	<xsl:if test="text:p/text:variable-set[@text:name=$Year]">
		<db:orgname>
			<xsl:value-of select="text:p/text:variable-set[@text:name=$Year]"/>
		</db:orgname>
	</xsl:if>
</xsl:template>


<xsl:template name="Holder">
	<xsl:param name="CopyrightLevel"/>
	<xsl:param name="HolderLevel"/>
	<xsl:variable name="Copyright"><xsl:value-of select="concat('articleinfo.copyright_','', $CopyrightLevel)"/></xsl:variable>
	<xsl:variable name="Holder"><xsl:value-of select="concat($Copyright,'',concat('.holder_','',$HolderLevel))"/></xsl:variable>

	<xsl:if test="text:p/text:variable-set[@text:name=$Holder]">
		<db:orgname>
			<xsl:value-of select="text:p/text:variable-set[@text:name=$Holder]"/>
		</db:orgname>
	</xsl:if>
</xsl:template>

<xsl:template name="Author">
	<xsl:param name="AuthorLevel"/>
	<xsl:variable name="Author"><xsl:value-of select="concat('articleinfo.author_','', $AuthorLevel)"/></xsl:variable>	
	<xsl:if test="text:p/text:variable-set[contains(@text:name, $Author )]">
		<db:author>
			<xsl:call-template name="Surname"><xsl:with-param name="AuthorLevel" select="$AuthorLevel"/><xsl:with-param name="SurnameLevel" select="0"/></xsl:call-template>
			<xsl:call-template name="Firstname"><xsl:with-param name="AuthorLevel" select="$AuthorLevel"/><xsl:with-param name="FirstnameLevel" select="0"/></xsl:call-template>
			<xsl:call-template name="Affiliation"><xsl:with-param name="AuthorLevel" select="$AuthorLevel"/><xsl:with-param name="AffilLevel" select="0"/></xsl:call-template>
		</db:author>
		<xsl:call-template name="Author"><xsl:with-param name="AuthorLevel" select="$AuthorLevel+1"/></xsl:call-template>
	</xsl:if>	
</xsl:template>

<xsl:template name="Surname">
	<xsl:param name="AuthorLevel"/>
	<xsl:param name="SurnameLevel"/>
	<xsl:variable name="Author"><xsl:value-of select="concat('articleinfo.author_','', $AuthorLevel)"/></xsl:variable>
	<xsl:variable name="Surname"><xsl:value-of select="concat($Author,'',concat('.surname_','',$SurnameLevel))"/></xsl:variable>
	<xsl:if test="text:p/text:variable-set[@text:name=$Surname]">
		<db:surname>
			<xsl:value-of select="text:p/text:variable-set[@text:name=$Surname]"/>
		</db:surname>
		<xsl:call-template name="Surname"><xsl:with-param name="AuthorLevel" select="$AuthorLevel"/>
			<xsl:with-param name="SurnameLevel" select="SurnameLevel+1"/>
		</xsl:call-template>
	</xsl:if>
</xsl:template>

<xsl:template name="Firstname">
	<xsl:param name="AuthorLevel"/>
	<xsl:param name="FirstnameLevel"/>
	<xsl:variable name="Author"><xsl:value-of select="concat('articleinfo.author_','', $AuthorLevel)"/></xsl:variable>
	<xsl:variable name="Firstname"><xsl:value-of select="concat($Author,'',concat('.firstname_','',$FirstnameLevel))"/></xsl:variable>
	<xsl:if test="text:p/text:variable-set[@text:name=$Firstname]">
		<db:firstname>
			<xsl:value-of select="text:p/text:variable-set[@text:name=$Firstname]"/>
		</db:firstname>
		<xsl:call-template name="Surname">
			<xsl:with-param name="AuthorLevel" select="$AuthorLevel"/>
			<xsl:with-param name="FirstnameLevel" select="FirstnameLevel+1"/>
		</xsl:call-template>
	</xsl:if>
</xsl:template>

<xsl:template name="Affiliation">
	<xsl:param name="AuthorLevel"/>
	<xsl:param name="AffilLevel"/>
	<xsl:variable name="Author"><xsl:value-of select="concat('articleinfo.author_','', $AuthorLevel)"/></xsl:variable>
	<xsl:variable name="Affil"><xsl:value-of select="concat($Author,'',concat('.affiliation_','',$AffilLevel))"/></xsl:variable>
	<xsl:if test="text:p/text:variable-set[contains(@text:name,$Affil)]">
		<db:affiliation>
			<xsl:call-template name="Orgname">
				<xsl:with-param name="AuthorLevel" select="$AuthorLevel"/>
				<xsl:with-param name="AffilLevel" select="$AffilLevel"/><xsl:with-param name="OrgLevel" select="0"/>
			</xsl:call-template>
			<xsl:call-template name="Address">
				<xsl:with-param name="AuthorLevel" select="$AuthorLevel"/>
				<xsl:with-param name="AffilLevel" select="$AffilLevel"/><xsl:with-param name="AddressLevel" select="0"/>
			</xsl:call-template>
		</db:affiliation>
	</xsl:if>
</xsl:template>

<xsl:template name="Orgname">
	<xsl:param name="AuthorLevel"/>
	<xsl:param name="AffilLevel"/>
	<xsl:param name="OrgLevel"/>

	<xsl:variable name="Author"><xsl:value-of select="concat('articleinfo.author_','', $AuthorLevel)"/></xsl:variable>
	<xsl:variable name="Affil"><xsl:value-of select="concat($Author,'',concat('.affiliation_','',$AffilLevel))"/></xsl:variable>
	<xsl:variable name="Org"><xsl:value-of select="concat($Affil,'',concat('.orgname_','',$OrgLevel))"/></xsl:variable>

	<xsl:if test="text:p/text:variable-set[@text:name=$Org]">
		<db:orgname>
			<xsl:value-of select="text:p/text:variable-set[@text:name=$Org]"/>
		</db:orgname>
	</xsl:if>
</xsl:template>

<xsl:template name="Address">
	<xsl:param name="AuthorLevel"/>
	<xsl:param name="AffilLevel"/>
	<xsl:param name="AddressLevel"/>
	<xsl:variable name="Author"><xsl:value-of select="concat('articleinfo.author_','', $AuthorLevel)"/></xsl:variable>
	<xsl:variable name="Affil"><xsl:value-of select="concat($Author,'',concat('.affiliation_','',$AffilLevel))"/></xsl:variable>
	<xsl:variable name="Address"><xsl:value-of select="concat($Affil,'',concat('.address_','',$AddressLevel))"/></xsl:variable>
	<xsl:if test="text:p/text:variable-set[@text:name=$Address]">
		<db:address>
			<xsl:value-of select="text:p/text:variable-set[@text:name=$Address]"/>
		</db:address>
	</xsl:if>
</xsl:template>

<xsl:template match="text:p[@text:style-name='VarList Term']">
	<xsl:element name="db:term">
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>

<xsl:template match="text:footnote-citation"/>

<xsl:template match="text:p[@text:style-name='Mediaobject']">
	<db:mediaobject>
		<xsl:apply-templates/>
	</db:mediaobject>
</xsl:template>

<xsl:template match="office:annotation/text:p">
	<db:note>
		<db:remark>
			<xsl:apply-templates/>
		</db:remark>
	</db:note>
</xsl:template>

<xsl:template name="colspec">
	<xsl:param name="left"/>
	<xsl:if test="number($left &lt; ( table:table-column/@table:number-columns-repeated +2)  )">
		<xsl:element name="db:colspec">
			<xsl:attribute name="colnum"><xsl:value-of select="$left"/></xsl:attribute>
			<xsl:attribute name="colname">c<xsl:value-of select="$left"/></xsl:attribute>
		</xsl:element>
		<xsl:call-template name="colspec"><xsl:with-param name="left" select="$left+1" /></xsl:call-template>
	</xsl:if>
</xsl:template>

<xsl:template match="table:table-column">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="table:table-header-rows">
	<db:thead>
		<xsl:apply-templates/>
	</db:thead>	
</xsl:template>

<xsl:template match="table:table">
	<xsl:variable name="tableTitle" select="following-sibling::text:p[@text:style-name='Table']"/>
	<db:table>
		<xsl:attribute name="id">table_<xsl:number count="*" level="multiple" /></xsl:attribute>
		<xsl:if test="normalize-space($tableTitle)">
			<db:title>
				<xsl:attribute name="id">tabletitle_<xsl:number count="*" level="multiple" /></xsl:attribute>
				<xsl:value-of select="$tableTitle"/>
			</db:title>
		</xsl:if>
		<xsl:apply-templates/>			
	</db:table>
</xsl:template>

<xsl:template match="table:table-row">
	<db:row>
		<xsl:apply-templates/>
	</db:row>
</xsl:template>

<xsl:template match="table:table-cell">
	<db:entry>
		<xsl:if test="@table:number-columns-spanned">
			<xsl:attribute name="html:colspan"><xsl:value-of select="@table:number-columns-spanned"/></xsl:attribute>
		</xsl:if>
		<xsl:if test="@table:number-rows-spanned">
			<xsl:attribute name="html:rowspan"><xsl:value-of select="@table:number-rows-spanned"/></xsl:attribute>
		</xsl:if>
		<xsl:apply-templates/>
	</db:entry>
</xsl:template>

<xsl:template match="text:unordered-list | text:list | text:ordered-list">
	<xsl:if test="not(preceding-sibling::*[1][self::text:list or self::unordered-list or self::text:ordered-list])">
		<xsl:call-template name="drawList"/>
	</xsl:if>
</xsl:template>

<xsl:template match="text:unordered-list | text:list | text:ordered-list" mode="listPullBack">
	<xsl:call-template name="drawList"/>
</xsl:template>

<xsl:template name="drawList">
	<xsl:variable name="docbookTitles">
		<xsl:for-each select="descendant::text:p">
			<xsl:variable name="docbookTitle" select="."/>
			<xsl:variable name="textStyle" select="@text:style-name"/>
			<xsl:variable name="referencedStyle" select="//office:automatic-styles/style:style[@style:name = $textStyle]"/>
			<xsl:for-each select="$referencedStyle">
				<xsl:variable name="referencedParentStyleName" select="@style:parent-style-name"/>
				<xsl:variable name="referencedParentStyle" select="//docvert:external-file/office:styles/style:style[@style:name = $referencedParentStyleName]"/>
				<xsl:for-each select="$referencedParentStyle">
					<xsl:if test="translate($referencedParentStyleName, $upperCaseLetters, $lowerCaseLetters) = 'title' ">
						<xsl:if test="normalize-space($docbookTitle)">
							<xsl:value-of select="$docbookTitle"/>
						</xsl:if>
					</xsl:if>
				</xsl:for-each>
			</xsl:for-each>
		</xsl:for-each>
	</xsl:variable>
	<xsl:choose>
		<xsl:when test="normalize-space($docbookTitles)">
			<db:para role="title"><xsl:value-of select="$docbookTitles"/></db:para>
		</xsl:when>
		<xsl:otherwise>
			<xsl:variable name="textStyle">
				<xsl:choose>
					<xsl:when test="@text:style-name">
						<xsl:value-of select="@text:style-name"/>
					</xsl:when>
					<xsl:when test="ancestor::*[@text:style-name]">
						<xsl:value-of select="ancestor::*[@text:style-name][1]/@text:style-name"/>
					</xsl:when>
				</xsl:choose>
			</xsl:variable>
			<!--[{{<xsl:value-of select="$textStyle"/>}}] -->
			<xsl:variable name="referencedListStyle" select="//text:list-style[@style:name = $textStyle]"/>
			<!-- [<xsl:value-of select="count($referencedListStyle)"/> -->
			<!--
			<xsl:if test="not(count($referencedListStyle))">
				?<xsl:value-of select="local-name()"/> has <xsl:value-of select="count(@*)"/> attributes?<xsl:for-each select="@*"><xsl:value-of select="local-name()"/>=<xsl:value-of select="."/>,</xsl:for-each>
			</xsl:if>
			:<xsl:value-of select="$referencedListStyle"/>]
			-->
			<xsl:variable name="listDepth" select="count(ancestor::text:list-item) + 1"/>
			<xsl:choose>
				<xsl:when test="$referencedListStyle/text:list-level-style-number[@text:level=$listDepth]">
					<db:orderedlist>
						<xsl:apply-templates/>
					</db:orderedlist>
				</xsl:when>
				<xsl:otherwise>
					<db:itemizedlist>
						<xsl:apply-templates/>
					</db:itemizedlist>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="text:list-item">
	<xsl:choose>
		<xsl:when test="parent::text:unordered-list/@text:style-name='Var List' ">
			<xsl:if test="child::text:p[@text:style-name='VarList Term']">
				<xsl:element name="db:varlistentry">
					<xsl:apply-templates select="child::text:p[@text:style-name='VarList Term']"/>
					<xsl:if test="following-sibling::text:list-item[1]/text:p[@text:style-name='VarList Item']">
						<xsl:apply-templates select="following-sibling::text:list-item[1]/text:p"/>
					</xsl:if>
	  			</xsl:element>
			</xsl:if>
		</xsl:when>
		<xsl:otherwise>
			<xsl:variable name="ancestorLists" select="ancestor::*[self::text:unordered-list or self::text:list or self::text:ordered-list]"/>
			<xsl:variable name="ancestorList" select="$ancestorLists[position() = 1]"/>
			<xsl:variable name="ancestorListGenerateId" select="generate-id($ancestorList)"/>

			<xsl:variable name="precedingNonList" select="$ancestorList/preceding-sibling::*[not(self::text:unordered-list) and not(self::text:list) and not(self::text:ordered-list)][1]"/>
			<!--
			$<xsl:value-of select="count($ancestorList)"/>$
			*<xsl:value-of select="count($precedingNonList)"/>*
			^<xsl:for-each select="$ancestorList/preceding-sibling::*[not(self::text:unordered-list) and not(self::text:list) and not(self::text:ordered-list)]"><xsl:value-of select="local-name()"/>, </xsl:for-each>^
			-->
			<xsl:variable name="allLists" select="key('lists', generate-id($precedingNonList))"/>
			<xsl:variable name="currentListIndex">
				<xsl:for-each select="$allLists">
					<xsl:if test="generate-id() = $ancestorListGenerateId">
						<xsl:value-of select="position()"/>
					</xsl:if>
				</xsl:for-each>
			</xsl:variable>
			<xsl:variable name="followingLists" select="$allLists[position() &gt; number($currentListIndex)]"/>
			<!-- <xsl:value-of select="$currentListIndex"/>:<xsl:value-of select="count($followingLists)"/>:<xsl:value-of select="count($allLists)"/> -->
			<xsl:variable name="currentListItemDepth" select="count(ancestor-or-self::text:list-item)"/>

			<xsl:variable name="lastListItemWithinAncestorList" select="$ancestorList/descendant::text:list-item[position() = last()]"/>
			<xsl:element name="db:listitem">
				<xsl:if test="not(normalize-space($currentListIndex))">
					Error, unable to determine list
					((<xsl:value-of select="count($followingLists)"/>/<xsl:value-of select="count($allLists)"/>:<xsl:value-of select="$currentListIndex"/>))
				</xsl:if>
				<xsl:apply-templates/>
				<xsl:if test="generate-id() = generate-id($lastListItemWithinAncestorList)">
					<!-- LAST, Process children(<xsl:value-of select="."/>)[ -->
					<xsl:variable name="listPointer" select="$followingLists/descendant::text:list-item[count(ancestor::text:list-item) = $currentListItemDepth][1]/parent::*"/>
					<!-- [<xsl:value-of select="count($listPointer)"/>{ -->
					<xsl:apply-templates select="$listPointer[1]" mode="listPullBack"/>
				</xsl:if>
			</xsl:element>
			<xsl:if test="generate-id() = generate-id($lastListItemWithinAncestorList)">
				<!-- LAST, processing siblings(<xsl:value-of select="."/>)[ -->
				<xsl:variable name="listItemPointer" select="$followingLists/descendant::text:list-item[count(ancestor::text:list-item) = $currentListItemDepth - 1 and *[not(self::text:unordered-list or self::text:list or self::text:ordered-list)]]"/>
				<xsl:if test="$listItemPointer">
					<xsl:apply-templates select="$listItemPointer"/>
				</xsl:if>
				<!-- [not(self::text:unordered-list or self::text:list or self::text:ordered-list)] -->
			</xsl:if>

		</xsl:otherwise>
	</xsl:choose>	
</xsl:template>

<xsl:template match="dc:title"/>

<xsl:template match="dc:description">
	<xsl:if test="normalize-space(.)">
		<db:abstract>
			<db:para>
				<xsl:call-template name="includeStyleNameWhenUseful"/>
				<xsl:apply-templates/>
			</db:para>
		</db:abstract>
	</xsl:if>
</xsl:template>

<xsl:template match="dc:subject"/>

<xsl:template match="meta:generator"/>

<xsl:template match="draw:plugin">
	<xsl:element name="db:audioobject">
		<xsl:attribute name="fileref">
			<xsl:value-of select="@xlink:href"/>
		</xsl:attribute>
		<xsl:attribute name="width">
		</xsl:attribute>
	</xsl:element>
</xsl:template>

<xsl:template match="text:note[@text:note-class='footnote']">
	<db:footnote label="{normalize-space(text:note-citation)}">
		<xsl:apply-templates/>
	</db:footnote>
</xsl:template>

<xsl:template match="text:note-body">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="text:footnote">
	<db:footnote>
		<xsl:apply-templates/>
	</db:footnote>
</xsl:template>

<xsl:template match="text:footnote-body">
		<xsl:apply-templates/>
</xsl:template>

<xsl:template match="draw:text-box">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="text:line-break">
	<db:literal role="linebreak">
		<html:br/>
	</db:literal>
</xsl:template>

<xsl:template match="text:span">
	<xsl:variable name="textStyle" select="@text:style-name"/>
	<xsl:variable name="referencedStyle" select="//style:style[@style:name = $textStyle]"/>
	<xsl:choose>
		<xsl:when test="$referencedStyle[style:text-properties/@fo:font-weight = 'bold' or style:text-properties/@fo:font-style = 'italic']">
			<!-- italics or bold -->
			<xsl:element name="db:emphasis">
				<xsl:if test="$referencedStyle[style:text-properties/@fo:font-weight='bold']">
					<xsl:attribute name="role">
						<xsl:text>bold</xsl:text>
					</xsl:attribute>
				</xsl:if>
				<xsl:copy-of select="$referencedStyle/style:text-properties/@fo:color"/>
				<xsl:copy-of select="$referencedStyle/style:text-properties/@fo:background-color"/>
				<xsl:apply-templates/>
			</xsl:element>
		</xsl:when>
		<xsl:when test="contains($referencedStyle/style:text-properties/@style:text-position, 'super')">
			<xsl:element name="db:superscript">
				<xsl:apply-templates/>
			</xsl:element>
		</xsl:when>
		<xsl:when test="contains($referencedStyle/style:text-properties/@style:text-position, 'sub')">
			<xsl:element name="db:subscript">
				<xsl:apply-templates/>
			</xsl:element>
		</xsl:when>
		<xsl:when test="./@text:style-name='GuiMenu'">
			<xsl:element name="db:guimenu">
				<xsl:value-of select="."/>	
			</xsl:element>
		</xsl:when>
		<xsl:when test="./@text:style-name='GuiSubMenu'">
			<xsl:element name="db:guisubmenu">
				<xsl:value-of select="."/>	
			</xsl:element>
		</xsl:when>
		<xsl:when test="@text:style-name='GuiMenuItem'">
			<xsl:element name="db:guimenuitem">
				<xsl:value-of select="."/>	
			</xsl:element>
		</xsl:when>
		<xsl:when test="@text:style-name='GuiButton'">
			<xsl:element name="db:guibutton">
				<xsl:value-of select="."/>	
			</xsl:element>
		</xsl:when>
		<xsl:when test="@text:style-name='GuiButton'">
			<xsl:element name="db:guibutton">
				<xsl:value-of select="."/>	
			</xsl:element>
		</xsl:when>
		<xsl:when test="@text:style-name='GuiLabel'">
			<xsl:element name="db:guilabel">
				<xsl:value-of select="."/>	
			</xsl:element>
		</xsl:when>
		<xsl:when test="@text:style-name='Emphasis'">
			<xsl:element name="db:emphasis">
				<xsl:value-of select="."/>	
			</xsl:element>
		</xsl:when>
		<xsl:when test="@text:style-name='FileName'">
			<xsl:element name="db:filename">
				<xsl:value-of select="."/>	
			</xsl:element>
		</xsl:when>
		<xsl:when test="@text:style-name='Application'">
			<xsl:element name="db:application">
				<xsl:value-of select="."/>	
			</xsl:element>
		</xsl:when>
		<xsl:when test="@text:style-name='Command'">
			<db:command>
				<xsl:apply-templates/>
			</db:command>
		</xsl:when>
		<xsl:when test="@text:style-name='SubScript'">
			<db:subscript>
				<xsl:apply-templates/>
			</db:subscript>
		</xsl:when>
		<xsl:when test="@text:style-name='SuperScript'">
			<db:superscript>
				<xsl:apply-templates/>
			</db:superscript>
		</xsl:when>
		<xsl:when test="@text:style-name='SystemItem'">
			<db:systemitem>
				<xsl:apply-templates/>
			</db:systemitem>
		</xsl:when>
		<xsl:when test="@text:style-name='ComputerOutput'">
			<db:computeroutput>
				<xsl:apply-templates/>
			</db:computeroutput>
		</xsl:when>
		<xsl:when test="@text:style-name='Highlight'">
			<db:highlight>
				<xsl:apply-templates/>
			</db:highlight>
		</xsl:when>
		<xsl:when test="@text:style-name='KeyCap'">
			<db:keycap>
				<xsl:apply-templates/>
			</db:keycap>
		</xsl:when>
		<xsl:when test="@text:style-name='KeySym'">
			<xsl:element name="db:keysym">
				<xsl:apply-templates/>
			</xsl:element>
		</xsl:when>
		<xsl:when test="@text:style-name='KeyCombo'">
			<db:keycombo>
				<xsl:apply-templates/>
			</db:keycombo>
		</xsl:when>
		<xsl:otherwise>
			<xsl:apply-templates/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="text:tab">
	<xsl:variable name="tabNumber" select="count(preceding-sibling::text:tab) + 1"/>
	<xsl:variable name="paragraphStyleName" select="ancestor::text:p[1]/@text:style-name"/>
	<xsl:variable name="paragraphStyle" select="//style:style[@style:name = $paragraphStyleName]"/>
	<xsl:variable name="tabStops" select="$paragraphStyle/descendant::style:tab-stops/style:tab-stop"/>
	<xsl:variable name="tabStop" select="$tabStops[position() = $tabNumber]/@style:position"/>
	<xsl:element name="db:literal">
		<xsl:attribute name="role">tab</xsl:attribute>
		<xsl:if test="$tabStop">
			<xsl:attribute name="html:style">left:<xsl:value-of select="$tabStop"/>;</xsl:attribute>
		</xsl:if>
		<xsl:text>&#9;</xsl:text>
	</xsl:element>
</xsl:template>

<!-- track changes, looks like this...
	<text:tracked-changes>
		<text:changed-region text:id="ct-1352177536">
			<text:insertion>
				<office:change-info>
					<dc:creator>NAME</dc:creator>
					<dc:date>DATE</dc:date>
				</office:change-info>
			</text:insertion>
		</text:changed-region>
		<text:changed-region text:id="ct-1351710216">
			<text:deletion>
				<office:change-info>
					<dc:creator>NAME</dc:creator>
					<dc:date>DATE</dc:date>
				</office:change-info>
				<text:p text:style-name="Standard">2.1.2</text:p>
			</text:deletion>
		</text:changed-region>
	</text:tracked-changes>
 -->
<xsl:template match="text:tracked-changes"/>


<xsl:template match="text:bookmark-start">
	<db:anchor xlink:to="{@text:name}"/>
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="text:a">
	<xsl:element name="db:link">
		<xsl:attribute name="xlink:href">
			<xsl:value-of select="@xlink:href"/>
		</xsl:attribute>
		<xsl:apply-templates/>
	</xsl:element>
</xsl:template>


<!-- ########## CODE IN TESTING  ########## -->

<!--
	<db:row>
		<xsl:for-each select="table:table-cell">
			<xsl:if test="not(table:table/@table:is-sub-table = 'true')">
				<xsl:text disable-output-escaping="yes">&lt;db:entry&gt;</xsl:text>
			</xsl:if>
			<xsl:apply-templates select="text:p"/>
				[<xsl:value-of select="table:table/@table:is-sub-table"/>
				(nodes:
				<xsl:for-each select="*">
					<xsl:value-of select="name()"/>,
				</xsl:for-each>
				)]
			<xsl:for-each select="descendant::table:table-row[count(preceding-sibling::table:table-row) = 0]/table:table-cell">
				<xsl:if test="not(table:table)">
					<xsl:text disable-output-escaping="yes">&lt;db:entry&gt;</xsl:text>
				</xsl:if>
				<xsl:apply-templates select="text:p"/>
					[<xsl:value-of select="table:table/@table:is-sub-table"/>
					(nodes:
					<xsl:for-each select="*">
						<xsl:value-of select="name()"/>,
					</xsl:for-each>
					)]
				<xsl:if test="not(table:table)">
					<xsl:text disable-output-escaping="yes">&lt;/db:entry&gt;</xsl:text>
				</xsl:if>
			</xsl:for-each>

			<xsl:if test="not(table:table/@table:is-sub-table = 'true')">
				<xsl:text disable-output-escaping="yes">&lt;/db:entry&gt;</xsl:text>
			</xsl:if>

		</xsl:for-each>
	</db:row>
	
	<xsl:if test="descendant::table:table/@table:is-sub-table = 'true' ">
		<xsl:variable name="numberOfRowsToDraw">
			<xsl:call-template name="countMaximumRows">
				<xsl:with-param name="tables" select="descendant::table:table[@table:is-sub-table = 'true']"/>
			</xsl:call-template>
		</xsl:variable>
		<xsl:call-template name="drawRemainingRows">
			<xsl:with-param name="upToRowNumber" select="$numberOfRowsToDraw"/>
		</xsl:call-template>
	</xsl:if>
		
	ancestor::table:table/@table:is-sub-table = 'true'
	<xsl:element name="db:entry">
 		<xsl:if test="@table:number-columns-spanned > '1' ">
			<xsl:attribute name="namest"><xsl:value-of select="concat('c',count(preceding-sibling::table:table-cell[not(@table:number-columns-spanned)]) +sum(preceding-sibling::table:table-cell/@table:number-columns-spanned)+1)"/></xsl:attribute>
			<xsl:attribute name="nameend"><xsl:value-of select="concat('c',count(preceding-sibling::table:table-cell[not(@table:number-columns-spanned)]) +sum(preceding-sibling::table:table-cell/@table:number-columns-spanned)+ @table:number-columns-spanned)"/></xsl:attribute>
			<xsl:attribute name="html:colspan"><xsl:value-of select="@table:number-columns-spanned"/></xsl:attribute>
		</xsl:if>
		<xsl:if test="$verticalSpans">
			<xsl:variable name="maximumColumnCells">
				<xsl:call-template name="countMaximumColumnCells">
					<xsl:with-param name="columns" select="$verticalSpans"/>
				</xsl:call-template>
			</xsl:variable>
			<xsl:attribute name="html:rowspan"><xsl:value-of select="$maximumColumnCells"/></xsl:attribute>
			YES THERE'S A VERTICAL SPAN AROUND ME
		</xsl:if>
		<xsl:apply-templates/>
	</xsl:element>
-->


<xsl:template name="drawRemainingRows">
	<xsl:param name="upToRowNumber"/>
	<xsl:param name="rowIndex">2</xsl:param>


	<xsl:if test="$rowIndex &lt;= $upToRowNumber">
		<xsl:variable name="tables" select="descendant::table:table[@table:is-sub-table = 'true']"/>
		<xsl:variable name="rows" select="$tables/table:table-row[count(preceding-sibling::table:table-row) = ($rowIndex - 1)]"/>
		<db:row>
			<xsl:for-each select="$rows">
				<xsl:for-each select="table:table-cell">
					<db:entry>
						[DEBUG: <xsl:value-of select="."/>]
						...
					</db:entry>
				</xsl:for-each>
			</xsl:for-each>
		</db:row>
		<xsl:call-template name="drawRemainingRows">
			<xsl:with-param name="upToRowNumber" select="$upToRowNumber"/>
			<xsl:with-param name="rowIndex" select="$rowIndex + 1"/>
		</xsl:call-template>
	</xsl:if>
</xsl:template>

<xsl:template name="countMaximumRows">
	<xsl:param name="itemIndex">1</xsl:param>
	<xsl:param name="tables"/>
	<xsl:param name="maximumSoFar">1</xsl:param>

	<xsl:choose>
		<xsl:when test="count($tables) != $itemIndex">
			<xsl:variable name="currentTable" select="$tables[position() = $itemIndex]"/>
			<xsl:variable name="currentTableRowCount" select="count($currentTable/table:table-row)"/>
			<!-- [<xsl:value-of select="$currentTableRowCount"/> vs. <xsl:value-of select="$maximumSoFar"/>] -->
			<xsl:variable name="newMaximum">
				<xsl:choose>
					<xsl:when test="$currentTableRowCount &gt; $maximumSoFar">
						<xsl:value-of select="$currentTableRowCount"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="$maximumSoFar"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:variable>
			<xsl:call-template name="countMaximumRows">
				<xsl:with-param name="itemIndex" select="$itemIndex + 1"/>
				<xsl:with-param name="tables" select="$tables"/>
				<xsl:with-param name="maximumSoFar" select="$newMaximum"/>
			</xsl:call-template>
		</xsl:when>
		<xsl:otherwise>
			<xsl:value-of select="$maximumSoFar"/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

</xsl:stylesheet>
