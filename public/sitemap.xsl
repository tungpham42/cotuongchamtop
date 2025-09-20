<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9">
  <xsl:output method="html" encoding="UTF-8" indent="yes"/>
  <xsl:template match="/">
    <html>
      <head>
        <title>Cờ tướng Sitemap</title>
        <style>
          body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f9; }
          h1 { color: #333; }
          table { width: 100%; border-collapse: collapse; margin-top: 20px; }
          th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
          th { background-color: #ff0028; color: white; }
          tr:nth-child(even) { background-color: #f9f9f9; }
          tr:hover { background-color: #e0e0e0; }
          a { color: #cc3636; text-decoration: none; }
          a:hover { text-decoration: underline; }
          .footer { margin-top: 20px; font-size: 0.9em; color: #666; }
        </style>
      </head>
      <body>
        <h1>Cờ tướng Sitemap</h1>
        <xsl:choose>
          <xsl:when test="sitemap:sitemapindex">
            <p>Sitemap Index containing <xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"/> sitemaps</p>
            <table>
              <tr>
                <th>Sitemap URL</th>
                <th>Last Modified</th>
              </tr>
              <xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
                <tr>
                  <td><a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a></td>
                  <td><xsl:value-of select="sitemap:lastmod"/></td>
                </tr>
              </xsl:for-each>
            </table>
          </xsl:when>
          <xsl:when test="sitemap:urlset">
            <p>Sitemap containing <xsl:value-of select="count(sitemap:urlset/sitemap:url)"/> URLs</p>
            <table>
              <tr>
                <th>URL</th>
                <th>Last Modified</th>
                <th>Change Frequency</th>
                <th>Priority</th>
              </tr>
              <xsl:for-each select="sitemap:urlset/sitemap:url">
                <tr>
                  <td><a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a></td>
                  <td><xsl:value-of select="sitemap:lastmod"/></td>
                  <td><xsl:value-of select="sitemap:changefreq"/></td>
                  <td><xsl:value-of select="sitemap:priority"/></td>
                </tr>
              </xsl:for-each>
            </table>
          </xsl:when>
        </xsl:choose>
        <div class="footer">Cờ tướng</div>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>