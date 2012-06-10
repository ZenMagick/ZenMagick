<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    version="1.0">
  <xsl:param name="title"/>

  <xsl:output method="xml" indent="yes"
      version="1.0" omit-xml-declaration="yes"
      doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
      doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
  />

  <!-- Copy standard document elements.  Elements that
       should be ignored must be filtered by apply-templates
       tags. -->
  <xsl:template match="*">
    <xsl:copy>
      <xsl:copy-of select="attribute::*[. != '']"/>
      <xsl:apply-templates/>
    </xsl:copy>
  </xsl:template>

  <xsl:template match="zipdiff">
    <html>
      <head>
        <title><xsl:value-of select="$title"/></title>
        <style type="text/css">
          body, p, td {font-family:verdana,arial,helvetica;font-size:80%;color:#000;}
          .entries td {font-weight: bold;text-align:left;background:#a6caf0;}
          table {border:none;width:100%;}
          table#nav {border-top:2px solid gray;padding-top:.8em;}
          tr, td {background:#eeeee0;}
          a {color:black;text-decoration:none;}
        </style>
      </head>
      <body>
         <h1><a name="top"><xsl:value-of select="$title"/></a></h1>
         <p>
          Zipdiff between <xsl:value-of select="@filename1"/> and 
          <xsl:value-of select="@filename2"/>
         </p>
         <table width="100%" id="nav">
           <tr>
             <td align="right">
               <a href="#New">New Files</a> |
               <a href="#Modified">Modified Files</a> |
               <a href="#Removed">Removed Files</a>
             </td>
           </tr>
         </table>
        <table cellpadding="3" cellspacing="1">
          <xsl:call-template name="show-entries">
            <xsl:with-param name="title">New Files</xsl:with-param>
            <xsl:with-param name="anchor">New</xsl:with-param>
            <xsl:with-param name="entries" select="differences/added"/>
          </xsl:call-template>

          <xsl:call-template name="show-entries">
            <xsl:with-param name="title">Modified Files</xsl:with-param>
            <xsl:with-param name="anchor">Modified</xsl:with-param>
            <xsl:with-param name="entries" select="differences/changed"/>
          </xsl:call-template>

          <xsl:call-template name="show-entries">
            <xsl:with-param name="title">Removed Files</xsl:with-param>
            <xsl:with-param name="anchor">Removed</xsl:with-param>
            <xsl:with-param name="entries" select="differences/removed"/>
          </xsl:call-template>
        </table>
      </body>
    </html>
  </xsl:template>

  <xsl:template name="show-entries">
    <xsl:param name="title"/>
    <xsl:param name="anchor"/>
    <xsl:param name="entries"/>
    <tr class="entries">
      <td>
        <a>
          <xsl:attribute name="name"><xsl:value-of select="$anchor"/></xsl:attribute>
            <xsl:value-of select="$title"/> - <xsl:value-of select="count($entries)"/> entries
        </a>
      </td>
    </tr>
    <tr>
      <td>
        <ul>
          <xsl:apply-templates select="$entries">
            <xsl:sort/>
          </xsl:apply-templates>
        </ul>
     </td>
   </tr>
  </xsl:template>  

  <xsl:template match="differences/added">
    <li><a href="http://zenmagick.svn.sourceforge.net/viewvc/zenmagick/trunk/{.}"><xsl:value-of select="."/></a></li>
  </xsl:template>

  <xsl:template match="differences/removed">
    <li><a href="http://zenmagick.svn.sourceforge.net/viewvc/zenmagick/trunk/{.}"><xsl:value-of select="."/></a></li>
  </xsl:template>

  <xsl:template match="differences/changed">
    <li><a href="http://zenmagick.svn.sourceforge.net/viewvc/zenmagick/trunk/{.}"><xsl:value-of select="."/></a></li>
  </xsl:template>

</xsl:stylesheet>
