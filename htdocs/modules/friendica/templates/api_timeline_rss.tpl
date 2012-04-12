<rss xmlns:atom="http://www.w3.org/2005/Atom" version="2.0" xmlns:georss="http://www.georss.org/georss" xmlns:twitter="http://api.twitter.com">
  <channel>
    <title>Friendika</title>
    <link>$rss.alternate</link>
    <atom:link type="application/rss+xml" rel="self" href="$rss.self"/>
    <description>Friendika timeline</description>
    <language>$rss.language</language>
    <ttl>40</ttl>
	<image>
		<link>$`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.link</link>
		<title>$`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.name's items</title>
		<url>$`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.profile_image_url</url>
	</image>
	
{{ for $statuses as $status }}
  <item>
    <title>$status.`" . $GLOBALS['xoopsDB']->prefix(_MI_FDC_MODULE_DB_PREFIX . "user") . "`.name: $status.text</title>
    <description>$status.text</description>
    <pubDate>$status.created_at</pubDate>
    <guid>$status.url</guid>
    <link>$status.url</link>
    <twitter:source>$status.source</twitter:source>
  </item>
{{ endfor }}
  </channel>
</rss>
