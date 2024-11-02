=== Plugin Name ===
Contributors: ljasinskipl
Donate link: http://www.ljasinski.pl
Tags: bridge, card symbols,
Requires at least: 2.0.2
Tested up to: 3.2.1
Stable tag: 0.9b

Easily insert card symbols into your blog posts and pages

== Description ==

Must have plugin for bridge blogs and websites. Easily insert bridge deals and
symbols into your articles. Main futures
- replace !c, !d, !h, !s with bridge symbols in
	- comments
	- post excerpts
	- post texts
- easily insert board diagrams into your posts (but they will be filtered out
of post exceprts

thanks for michzimny (http://www.michzimny.pl) for help with regex. 

== Installation ==

Just click install :)

== Frequently Asked Questions ==

= How to insert suit symbols? =

It's simple. Just insert into your text !c for clubs, !d for diamonds, !h for hearts and !s for spades - just like using BBO Chat.

= What about styling? =

If you now a bit css, you can edit bridgehelper css to suit your needs. If not, wait for an update - there will be a possibility to set custom styles through the dashboard.

= How to insert a board diagram? =

Write in your text:
[deal nr='25' hand="A542.A32.A10865.K;.K8654.QJ10.Q7432;KJ109.QJ.K98753.J]
Above will result in:
- board diagram with board number 25, vulnerabilities and dealer set accordingly
- hands above are separated by semicolon (;) and suits by full stops (.)
- North's hand: 
	S: A542 
	H: A32
	D: A10865
	C: K
- East's hand:
	S: - (notice starting with .)
	H: K8654
	D: QJ10
	C: Q7432
- South's hand"
	S: KJ109
	H: QJ
	D: K98753
	C: J
- West's hand with remaining 13 cards

If you want to provide only partial board diagram (e.x. endplay), you have to enter at least one of west's cards.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the directory of the stable readme.txt, so in this case, `/tags/4.3/screenshot-1.png` (or jpg, jpeg, gif)
2. This is the second screen shot

== Changelog ==
= 0.9b =
 - bugfix

= 0.9 =
 - quick fix of some minor bugs
 - proper readme
 - filters for excerpts and comments
 - cleaned up code, I'll never do something like that again :)
= 0.8 =
Inserting a deal
TODO:
 - clean up messy code :)
 - partial deals
 - howto
 - clean up post excerpts
 - params in dashboard


= 0.2b = 
Corrected dashboard

= 0.2 = 
Added dashboard
Added CSS

= 0.1 = 
First stable version
Bridge symbols only

`<?php code(); // goes in backticks ?>`