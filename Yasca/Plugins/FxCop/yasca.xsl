<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" 
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/TR/xhtml1/strict">

<xsl:output encoding="utf-8" indent="no" method="xml" />
<xsl:template match="/">
  <results>
    <xsl:apply-templates select="//Message" />
  </results>
</xsl:template>

<xsl:template match="Message">
    <xsl:variable name="typeName" select="@TypeName" />


  <result>

    <xsl:attribute name="filename">
      <xsl:value-of select="ancestor::Target/@Name"/>
    </xsl:attribute>
    
    <xsl:attribute name="category">
      <xsl:value-of select="@Category" />
    </xsl:attribute>

    <xsl:attribute name="message">
      <xsl:value-of select="/FxCopReport/Rules/Rule[@TypeName=$typeName]/Name/text()" />
    </xsl:attribute>

    <xsl:attribute name="reference">
      <xsl:value-of select="/FxCopReport/Rules/Rule[@TypeName=$typeName]/Url/text()" />
    </xsl:attribute>

    <xsl:apply-templates select="./Issue[1]"/>

  </result>
</xsl:template>

<xsl:template match="Issue">

    <xsl:attribute name="severity">
      <xsl:choose>
        <xsl:when test="@Level = 'CriticalError' and @Certainty &gt; 80">1</xsl:when>
        <xsl:when test="@Level = 'CriticalError' and @Certainty &gt; 40">2</xsl:when>
        <xsl:when test="@Level = 'CriticalError'">3</xsl:when>
        <xsl:when test="@Level = 'Error' and @Certainty &gt; 80">2</xsl:when>
        <xsl:when test="@Level = 'Error' and @Certainty &gt; 40">3</xsl:when>
        <xsl:when test="@Level = 'Error'">4</xsl:when>
        <xsl:when test="@Level = 'CriticalWarning' and @Certainty &gt; 80">3</xsl:when>
        <xsl:when test="@Level = 'CriticalWarning' and @Certainty &gt; 40">4</xsl:when>
        <xsl:when test="@Level = 'Warning' and @Certainty &gt; 80">4</xsl:when>
        <xsl:otherwise>5</xsl:otherwise>
      </xsl:choose>
    </xsl:attribute>

  <xsl:attribute name="description">

    <xsl:apply-templates select="../.." mode="parent" />

<xsl:text>

</xsl:text>
    <xsl:value-of select="text()" />
  </xsl:attribute>
</xsl:template>

<xsl:template match="Messages" mode="parent">
    <xsl:apply-templates select=".." mode="parent" />
</xsl:template>

<xsl:template match="Namespace" mode="parent">
   <xsl:value-of select="@Name" />
</xsl:template>

<xsl:template match="Module" mode="parent">
    <xsl:value-of select="@Name" />
</xsl:template>

<xsl:template match="Target" mode="parent">
    <xsl:value-of select="@Name" />
</xsl:template>

<xsl:template match="Accessor" mode="parent">
    <xsl:apply-templates select="../../.." mode="parent" />.<xsl:value-of select="@Name" />   
</xsl:template>

<xsl:template match="Member" mode="parent">
    <xsl:apply-templates select=".." mode="parent" />.<xsl:value-of select="@Name" />
</xsl:template>

<xsl:template match="Resource" mode="parent">
    <xsl:value-of select="@Name" />
</xsl:template>

<xsl:template match="Type" mode="parent">
    <xsl:if test="not(../../@Name='')"><xsl:apply-templates select=".." mode="parent" />.</xsl:if><xsl:value-of select="@Name" />
</xsl:template>

<xsl:template match="*" mode="parent">
   <xsl:apply-templates select=".." mode="parent" />
</xsl:template>

<xsl:template match="FxCopReport" mode="parent">
</xsl:template>
  
</xsl:stylesheet>
