=== Full Site Cache for KeyCDN ===
Contributors: ze3kr, keycdn
Donate link: https://tlo.xyz/donate/
Tags: keycdn, cache, optimize, performance, speed, pagespeed, html, cdn, proxy
Requires at least: 4.4
Tested up to: 4.5.3
Stable tag: trunk
License: GNU GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin allows full site acceleration for WordPress with KeyCDN, which gives you the advantages of free SSL, HTTP/2, GZIP and more.

== Description ==

> NOTE: This is an **unofficial** plugin.

This plugin can help you to use KeyCDN on your WordPress, not only your Media and CSS, but also all HTML page. It is much faster than other cache plugins because it can cache the content on the [KeyCDN’s Edge Servers](https://www.keycdn.com/network?a=7126), which are close to the end-user. This plugins can automatically purge the page (and also the homepage, archive page, tag page, category page, feed, sitemap) when you publish a page or post.

This plugin only cache the content on the KeyCDN’s Edge Servers but not on your origin server, so if you install another cache plugin and use both of them, can improve performance. Now this plugin only works well with <a target="_blank" href="https://wordpress.org/plugins/cache-enabler/">Cache Enabler - WordPress Cache</a>.

The development version is on [gitTLO](https://git.tlo.xyz/ZE3kr/Full-Site-Cache-for-KeyCDN) and [GitHub](https://github.com/ZE3kr/Full-Site-Cache-for-KeyCDN), you can download the development version to help us to test, and [leave your issues here](https://git.tlo.xyz/ZE3kr/Full-Site-Cache-for-KeyCDN/issues).

= Features =

This plugin support those features **even if your server doesn’t support it**, works well with shared hosting.

+ Faster than other caching plugins
+ SSL (HTTPS) with Let’s Encrypt FREE SSL
+ HTTP/2 with HPACK
+ GZIP
+ CDN (Layer 7 proxy)
+ Hide origin IP and prevent DDOS attack to your server (Suggest using CloudFlare for the wp-admin doamin, and change your IP address, [see it in “IP White List” section](https://wordpress.org/plugins/full-site-cache-kc/other_notes/#Advance-Feature)).

= Compare it with CloudFlare =

The CloudFlare also can give you CDN, SSL and HTTP/2 support, but there’s something KeyCDN can but CloudFlare free plan cannot:

+ **Cache HTML page**, that means all the HTML page on CloudFlare is not cacheable and the request will bypass your origin server.
+ Use custom SSL certificate, including **EV certificate**.
+ Use CNAME and doesn’t need to change NS server.
+ Raw log forwarding in **real time** (CloudFlare has 24 hours delay for free plan)
+ Clear Cache by Tag

However, KeyCDN is not a free service but a pay-as-you-go service, KeyCDN is a affordable choice. And CloudFlare is also a very good DNS provider, you can still use it.

= Requirements =

+ PHP 5.4+ (PHP 5.6+ is recommended)
+ Wordpress 4.4+
+ Disable all others cache plugin and use <a target="_blank" href="https://wordpress.org/plugins/cache-enabler/">Cache Enabler</a> instead (Optional).
+ KeyCDN account, <a target="_blank" href="https://app.keycdn.com/signup?a=7126">sign up by this link</a> and get 250GB of free traffic.


== Installation ==

Before use this plugin, you need to have a KeyCDN account. You can [sign up by this link](https://www.keycdn.com/?a=7126), and you can get $10 free credit, that is included 250GB web traffic, it's enough for your test, and can use for a long time (if you don't have too much web traffic).

Put the folder `full-site-cache-kc` in your server, to `wp-content/plugins/full-site-cache-kc/`, you can [download it at here](https://wordpress.org/plugins/full-site-cache-kc/).

Or if you can add plugin online, you can search `full-site-cache-kc` and install it.

After that, goto the settings page of this plugin, which is called “KeyCDN”, and click “Setup Online” button, and following the introduction of the installation, you **does not need** to follow the Manual Setup guide below.

== Manual Setup ==

You can set it up manually, and use an existing Zone, only if you want to manual setup, you need to do this.

Note: This plugin doesn’t support root domain like `example.com`, you have to change settings before use it.

= 1. Setup this plugin =

Add the configuration code to your `wp-config.php` file in `wp-config.php` **above** the line reading `/* That’s all, stop editing! Happy blogging. */`.

You can get those configuration in the settings page of this plugin by click “Generate Configuration (for Manual Setup)” button.

Example configuration:

	$fsckeycdn_useHTTPS = false; // Change it to true if your server support HTTPS.
	$fsckeycdn_x_pull_key = 'KeyCDN'; // By default is `KeyCDN`, to make your server more secure, change it to a random 15 alphameric key, and update KeyCDN Zone settings.
	$fsckeycdn_apikey = 'vHlnpHcE6GPEWyTWWisr4hE9e80Xvr4a'; // You can find your key at here https://app.keycdn.com/users/authSettings
	$fsckeycdn_id = [
		1 => 10001,
		2 => 10002,
		3 => 10003,
	]; // The key (1, 2, 3) is blog id. the value (10001, 10002, 10003) is KeyCDN Zone ID.
	// $fsckeycdn_id = "10001"; // Use this line instead of above if you doesn't use multisite.

Then add a `require_once` function just below the variables just add, that need to run before evey plugin, to identify the server is KeyCDN or not.

	require_once(ABSPATH . 'wp-content/plugins/full-site-cache-kc/include.php'); // This plugin need run some scripts before everything, so you need to add this, if you use a different location for plugins, change it.

After that, you can enable this plugin.

= 2. Setup DNS and Change Siteurl =

You need to use a domain like `www.example.com` or `blog.example.com` but not root domain like `example.com`, because you need to create a CNAME on that domain.

If you are using domain like `www.example.com`, you need to create a DNS at `wp-admin.example.com` that point to your server.

If you are using domain like `blog.example.com`, you need to create a DNS at `wp-admin-blog.example.com` that point to your server.

Example BIND DNS file for root domain:

	example.com 300 IN A <origin-server>
	www.example.com 300 IN CNAME <your-zone-name>-<userid-hex>.kxcdn.com
	wp-admin.example.com 300 IN A <origin-server>

After you setted up, you need to go to the new host to visit WordPress dashboard.

And you need to change Siteurl to the new domain, keep Home URL not change. Example: Siteurl is `wp-admin.example.com` and Homeurl is `www.example.com`. Or Siteurl is `wp-admin-blog.example.com` and Homeurl is `blog.example.com`.

And if you need to edit your post, or go to the dashboard, you need to goto `wp-admin.example.com/wp-admin/` or `wp-admin-blog.example.com/wp-admin/`

= 3. Setup KeyCDN =

You need to add a `Pull Zone` in KeyCDN, and set `Origin URL` to `http(s)://wp-admin.example.com`, set SSL to `Letsencrypt` (You can get free SSL support!), enable `Cache Cookies`, `Strip Cookies` and `Forward Host Header`. Set `Expire (in minutes)` to 0 and set `Max Expire (in minutes)` to 1440 or bigger, and make sure to disable `CORS`.

== Advance Options ==

= Default Zone Options =

You can add `$fsckeycdn_default_settings` variable as a array in `wp-config.php` to replace the default value of KeyCDN Zone.

Default value:

	$fsckeycdn_default_settings = [
		'status' => 'active',
		'type' => 'pull',
		'forcedownload' => 'disabled',
		'cors' => 'disabled',
		'gzip' => 'enabled',
		'expire' => '0',
		'http2' => 'enabled',
		'securetoken' => 'disabled',
		'cacheignorecachecontrol' => 'enabled',
		'cacheignorequerystring' => 'disabled',
		'cachestripcookies' => 'enabled',
		'cachecanonical' => 'disabled',
		'cacherobots' => 'disabled',
		'cachehostheader' => 'enabled',
		'cachecookies' => 'enabled',
		'cachestripcookies' => 'enabled',
	];

You can see [all the parameters](https://www.keycdn.com/api#add-zone) that can set.

Example:

	$fsckeycdn_default_settings = [
		'cachemaxexpire' => '525949'.
		'sslcert' => 'letsencrypt',
	]

= Different API Key for Each Site =

KeyCDN has Zone Limit, so if your has too many site in your multisite, you have to use it.

This plugin can use different API Key for each site:

	$fsckeycdn_apikey = [
		1 => 'vHlnpHcE6GPEWyTWWisr4hE9e80Xvr4a',
		2 => 'w752ikcfTv0srgLhwNHayBpxAss8VT3X',
		3 => 'BI0lFsGBKOF5S6Y0Kc9zaQkq8BQXVJsv',
	];

And you can use different API Key for every 10 site like:

	$fsckeycdn_apikey = [
		'0X' => 'vHlnpHcE6GPEWyTWWisr4hE9e80Xvr4a',
		'1X' => 'w752ikcfTv0srgLhwNHayBpxAss8VT3X',
		'2X' => 'BI0lFsGBKOF5S6Y0Kc9zaQkq8BQXVJsv',
	];

0X for site 1~9, 1X for site 10~19.

= Variable X-Pull Key =

By default online setup will enable this feature, this make X-Pull Key more secure, especially for multisite.

This feature can generate different X-Pull Key for each domain, even if you enable Forward Host Header, it can prevent visitor visit your site using another domain.

Algorithm:

	substr(fsckeycdn_convert('f'.md5($fsckeycdn_x_pull_key.$_SERVER['HTTP_HOST'])),-15);

“fsckeycdn_convert” is a function that can convert hexadecimal to alphameric.

	function fsckeycdn_convert($s, $to=62) {
		$dict = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$res = '';
		$b = '';
		if($to > 0) {
			$to = ceil(log($to, 2));
			for($i=0; $i<strlen($s); $i++) $b .= sprintf('%04b', hexdec($s{$i}));
			while(strlen($b) >= $to) {
				$res = $dict{bindec(substr($b, -$to))} . $res;
				$b = substr($b, 0, -$to);
			}
			$res = $dict{bindec($b)} . $res;
			return $res;
		}
		$to = ceil(log(-$to, 2));
		for($i=0; $i<strlen($s); $i++) $b .= sprintf("%0{$to}b", strpos($dict, $s{$i}));
		while(strlen($b) > 4) {
			$res = $dict{bindec(substr($b, -4))} . $res;
			$b = substr($b, 0, -4);
		}
		if(bindec($b)) $res = $dict{bindec($b)} . $res;
		return $res;
	}

= Custom CDN Domain =

You can custom your CDN domain, it’s a specific domain that only for Images, CSS and JS.

Just add this in the `wp-config.php`.

	$fsckeycdn_cdn_domain = 'cdn.example.com';

== Extra Settings For CloudFlare ==

You need to do some Extra Settings For CloudFlare after you actived KeyCDN. If you are using CloudFlare and KeyCDN, you might set your DNS like that, [CF] means this domain enabled CloudFlare Proxy, and others means DNS only. Note: You need to disable CloudFlare on KeyCDN domain to ensure make HTML cache available:

	example.com 300 IN A <origin-server> [CF]
	www.example.com 300 IN CNAME <your-zone-name>-<userid-hex>.kxcdn.com
	wp-admin.example.com 300 IN A <origin-server> [CF]
	[key].example.com 300 IN A <origin-server>

The key is a random value, keep it like your origin IP.

And add the configuration code to `wp-config.php`, and **above** `require_once` (Optional, use this feature can make sure even if others has your origin IP, the request direct to your origin server will reject by this plugin.):

	$fsckeycdn_ip_white_list = [
		'103.21.244.0/22',
		'103.22.200.0/22',
		'103.31.4.0/22',
		'104.16.0.0/12',
		'108.162.192.0/18',
		'131.0.72.0/22',
		'141.101.64.0/18',
		'162.158.0.0/15',
		'172.64.0.0/13',
		'173.245.48.0/20',
		'188.114.96.0/20',
		'190.93.240.0/20',
		'197.234.240.0/22',
		'198.41.128.0/17',
		'199.27.128.0/21',
	]; // This is CloudFlare's IP.
	$fsckeycdn_ipv6_white_list = [
		'2400:cb00::/32',
		'2405:8100::/32',
		'2405:b500::/32',
		'2606:4700::/32',
		'2803:f800::/32'
	]; // This is CloudFlare's IPv6.
	$fsckeycdn_client_ip = $_SERVER['REMOTE_ADDR']; // The IP for CloudFlare, if it doesn’t work, please try `end(explode(', ',$_SERVER['HTTP_X_FORWARDED_FOR']))`
	$fsckeycdn_client_real_ip = $_SERVER['HTTP_CF_CONNECTING_IP']; // The IP for real client (Optional)

Then, go to KeyCDN Zone settings page, change “Origin URL” to `http(s)://[key].example.com`, disabled “Origin Shield” and enable “Forward Host Header”.

== About Purge ==

This plugin will purge all page that need to be purge, but when your customized your theme or changed your theme, you need to purge your site manually. go to the settings page and you can find purge button.

You can set `$fsckeycdn_purge` to change purge type.

+ `1`. Purge that page and every archive page when a post/page published (Recommend, default).
+ `2`. Purge whole blog (not include css, js and image) when a post/page published.
+ `3`. Purge all page that need to be purge (Beta).
+ `4`. Only purge that page when a post/page published.
+ `5`. Do nothing when a post/page published.

Example:

	$fsckeycdn_purge = 3;

== About “Cache Enabler” plugin ==

This plugin only cache the content on the KeyCDN’s Edge Servers but not on your origin server, so if you install another cache plugin and use both of them, can improve performance. Now this plugin only works well with <a target="_blank" href="https://wordpress.org/plugins/cache-enabler/">Cache Enabler - WordPress Cache</a>.

If you click “Clear Cache” on the admin bar, it will automatically clear KeyCDN Tag and also clear the cache of “Cache Enabler” plugin.

And if you change “Cache Behavior” in the settings page of “Cache Enabler”, **it will also effect WP KeyCDN cache behavior**.

== Extra Settings For Root Domain ==

You will get an error if you trying to enable it for root domain, whatever you setup manually or not.

To solve this problem, you need to change your domain beginning with `www.`, for example, if your blog domain is `example.com`, you have to change it to `www.example.com`.

However, you are still able to use root domain if your DNS provider supports ANAME record (or CNAME Flattening), for example, CloudFlare supports this feature, so you can set a CNAME record on your root domain and won't get any error! The version 2.2.0 brings this feature, you need to add this line to your `wp-config.php`:

```
$fsckeycdn_root_domain_setup = true;
```

= For NOT Multisite Installed =

Go to general settings page, change WordPress Address and Site Address to the new domain.

= For Multisite Installed =

If you NOT use subdomain install, pleace [see this page](http://codex.wordpress.org/Moving_WordPress#Moving_WordPress_Multisite) to find out how to move WordPress multisite to a new URL.

If you use subdomain install, I suggest you create a new blog and that blog’s domain is beginning with `www.`, for example, your have a network blog which use domain `example.com`, and you create a another blog which use domain `www.example.com`. Then input the content from the old blog which use domain `example.com`, and enable this plugin for the new blog.

After you add `require_once` function in `wp-config.php`, it will automatically redirect the old blog to new bog, and not effect with admin page.

== How to fully disable this plugin ==

It will disable KeyCDN for ALL blogs if you are using Multisite.

= 1. Remove the configurations =

You need to remove the lines that you added in wp-config.php file. And you **MUST** remove these line before your **delete** this plugin.

	/* Start WP KeyCDN code */
	Some codes……
	/* End WP KeyCDN code */

= 2. Delete this plugin =

It’s optional.

= 3. Change the Siteurl =

Use <a href="https://github.com/interconnectit/Search-Replace-DB">Search Replace DB</a>, replace `://wp-admin.` to `://www.`, and `://wp-admin-` to `://`.

= How to disable A blog in Multisite =

Just add this codes to `wp-config.php`

	$fsckeycdn_id[1] = false;

You need to add this line **after** the `$fsckeycdn_id` if you use Manual Setup. Change `1` to your blog ID that need to disabled.

== Known Issues ==

+ If you set a password to a post/page, it may cannot view by visitors even if they has password.

== Frequently Asked Questions ==

= Why can’t use this plugin on root doamin? =

Because to use KeyCDN, it need to set a CNAME record on the domain. But CNAME records are not supported on root domains (e.g. example.com) as they would conflict with the SOA- and NS-records (RFC1912 section 2.4: “A CNAME record is not allowed to coexist with any other data.”), an alternative is to redirect your root domain to a subdomain (e.g. www).

However, you are still able to use root domain if your DNS provider supports ANAME record (or CNAME Flattening), for example, CloudFlare supports this feature, so you can set a CNAME record on your root domain and won't get any error! The version 2.2.0 brings this feature, you need to add this line to your `wp-config.php`:

	$fsckeycdn_root_domain_setup = true;

= Why this plugin need to change the Siteurl? =

KeyCDN will enable cache for all request, so it won’t show admin bar or any admin page by pass the KeyCDN. This plugin will change your Siteurl, add `wp-admin` prefix to your domain, you can go to the admin page by the new domain. This plugin will set the redirect, so you don’t need to care about it at most of time. The visitor who not logged in can’t visit `wp-admin` domain.

NOTE: This plugin only changes the Siteurl, but not the Home URL.

= Why this plugin need my API Key? =

This plugin needs your API Key to:

+ Purge Zone Cache (When you click “Purge Everything” button in the settings page, or the WordPress Core has updated.)
+ Purge Zone Tag (When a new post published, switched theme, trashed a post.)
+ List Zones (To check if the zone already exists, and also use it to check API Key is correct or not)
+ Add Zone (When you first setup this plugin)
+ List Zonealiases (To check if the zonealiases already exists)
+ Add Zonealias (When you first setup this plugin)

Your API Key only store in the `wp-config.php` file, this plugin never store this in the database, and never send this to other server.

== Changelog ==

= 2.2.0 =

+ Compatible with WordPress 4.6.
+ Use cron job to purge, so now it soesn't has delay to purge!
+ Support to use KeyCDN on a non-www root domain for some DNS provider.
+ Fix a bug when using setup online.

= 2.1.5.1 =

+ Fix a Fatal error in PHP 5.4 and 5.5.

= 2.1.5 =

+ Add Custom CDN Domain support, you need to [set it up manually](https://wordpress.org/plugins/full-site-cache-kc/other_notes/#Advance-Feature) in Custom CDN Domain section.
+ Change the appearance of the settings page.
+ Fixed a bug in Manual Setup.

= 2.1.4 =

+ Fix bugs.

= 2.1.3 =

+ Add “Purge Everything” button. By default, the “Clear Cache” button on right top only purge the cache of HTML page of this blog but not include CSS, JS and media files.
+ Add “Feedback & Support”, “Donate” and “Write a review” button.
+ Add “Disable KeyCDN” for NOT manual setup
+ Fixed can’t rewrite URL of image correctly in some themes.


= 2.1.2 =

+ Fix URL rewrite for JS and URL Encoded.
+ Fixed customize page preview.

= 2.1.1 =

+ Fix bugs, works better with `Cache Enabler`.

= 2.1 =

+ Change name `Full Site Cache Enabler for KeyCDN` to `Full Site Cache for KeyCDN`.
+ Add support with `Cache Enabler`, now you can clear cache include `Cache Enabler - WordPress Cache` in the settings page of this plugin.

= 2.0 =

+ Add setup online feature, you don’t need to create a KeyCDN Zone by you self.
+ Add a admin page that can purge page.
+ Add Variable X-Pull Key feature.
+ Add Different API Key for Each Site feature.

= 1.0.0 =

Improve performance, some variable changed in this version, you need to do something before update, see “Upgrade Notice”.

= 0.4.3 =

IPv6 White list supported!

= 0.4.2 =

Never redirect when user is logged in.

= 0.4.1 =

Just update readme.txt

= 0.4.0 =

+ Disallow Direct File Access to plugin files.
+ Use Unique function (and/or define) names.

== Upgrade Notice ==

= 2.1.5.1 =

Fix a Fatal error in some old PHP version. By the way, you must use this plugin on PHP 5.4 or higher, however, PHP 5.6 or higher are recommend.

= 2.1.5 =

Add Custom CDN Domain support, change the appearance of the settings page.

= 2.1.4 =

Fix bugs.

= 2.1.3 =

Add “Purge Everything” button. By default, the “Clear Cache” button on right top only purge the cache of HTML page of this blog but not include CSS, JS and media files. Add “Feedback & Support”, “Donate” and “Write a review” button. Add “Disable KeyCDN” for NOT manual setup. Fixed can’t rewrite URL of image correctly in some themes.

= 2.1.2 =

Fix URL rewrite for JS and URL Encoded. Fixed customize page preview.

= 2.1.1 =

Fix bugs, works better with `Cache Enabler`.

= 2.1 =

Change name `Full Site Cache Enabler for KeyCDN` to `Full Site Cache for KeyCDN`. Add support with `Cache Enabler`, now you can clear cache include `Cache Enabler - WordPress Cache` in the settings page of this plugin.

= 2.0.0 =

This is a big update, you can setup online very easily now.

= 1.0.0 =

Improve performance.

You need to do something before update this plugin to v1.0.0.

First, you need to change `$fsckeycdn_id` variable, like this:

	$fsckeycdn_id = [
		1 => 10001,
		2 => 10002,
		3 => 10003,
	]; // The key (1, 2, 3) is blog id. the value (10001, 10002, 10003) is KeyCDN Zone ID.

Then, you need to change KeyCDN Zone settings, change `Expire (in minutes)` to 0.

= 0.4.3 =

IPv6 White list supported!

= 0.4.2 =

You can see admin bar when you enter admin page, can show post/page preview.

= 0.4.1 =

Just update readme.txt

= 0.4.0 =

This update changed some function name and variable name, you need to edit `wp-config.php`.

== Screenshots ==

1. Before setup.
2. Online setup.
3. Settings page.
