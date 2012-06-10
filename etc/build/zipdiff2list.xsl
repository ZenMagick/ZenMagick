<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    version="1.0">
  <xsl:output method="text"/>

  <xsl:template match="zipdiff/differences/added">
    <xsl:value-of select="."/>
  </xsl:template>
  <xsl:template match="zipdiff/differences/changed">
    <xsl:value-of select="."/>
  </xsl:template>
</xsl:stylesheet>
