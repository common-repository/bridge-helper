<?php
/*
 Plugin Name: Bridge Helper
 Plugin URI: http://wordpress.org/extend/plugins/bridge-helper/
 Description: Resource for bridge (or other card games) blog.
 Early version. Works with bridge symbols substitution and inserting deals. Check
 readme or dashboard for howto.
 Version: 0.9b
 Author: Łukasz Jasiński
 Author URI: http://www.ljasinski.pl
 Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 */

/**
 * @todo: [ ] multilingual capabilities
 * @todo: [ ] dynamic css
 * @todo: [ ] settings, turning filters on and off
 * @todo: [ ] custom atrribute names
 * @todo: [ ] multilingual card rank symbols (ex. D for Polish 'Queen')
 * @todo: [ ] other rank symbols for '10' (ex. '1' or 'T')
 * @todo: [ ] get_image function, center image without board number 
 */

// ############################################################################
// ### CSS Stylesheet

add_action( 'wp_print_styles', 'bridge_helper_add_stylesheet' );
/**
 * Adds custom stylesheet to head section of the page
 */
function bridge_helper_add_stylesheet() {
	// TODO: dynamic css
	wp_register_style( 'bridgeHelperStyle', plugins_url("/bridgehelper.css",__FILE__) );
	wp_enqueue_style( 'bridgeHelperStyle');
}

// ############################################################################
// ### Board diagram
// ############################################################################

// ----------------------------------------------------------------------------
// --- Setting up variables

// TODO: translation possibilities
$vul = Array();
$vul[1] = $vul[8] = $vul[11] = $vul[14] = 'obie przed';
$vul[2] = $vul[5] = $vul[12] = $vul[15] = 'NS po partii';
$vul[3] = $vul[6] = $vul[ 9] = $vul[ 0] = 'WE po partii';
$vul[4] = $vul[7] = $vul[10] = $vul[13] = 'obie po partii';

$dealer = Array(
1 => 'N',	2 => 'E',	3 => 'S',	4 => 'W');

$basecards = Array();
$baseCards['c'] = $baseCards['d'] = $baseCards['h'] = $baseCards ['s'] = Array(
	 'A' => 1,   'K' => 1,    'Q' => 1,    'J' => 1,    '10' => 1,
	 '9' => 1,   '8' => 1,    '7' => 1,    '6' => 1, 	 '5' => 1,
     '4' => 1,	 '3' => 1,    '2' => 1	
);

$suits = Array('s','h','d','c');
$handNames = Array('handN', 'handE', 'handS', 'handW');

// ----------------------------------------------------------------------------
// --- Functions

function bridge_helper_board_diagram($dealData) {
	$text = "
	<table class=\"rozdanie\">
		<tbody>
			<tr><td class=\"dealDetails\">Rozd {$dealData['nr']},<br />{$dealData['vul']},
			<br />Rozd. {$dealData['dealer']}</td>
			<td>{$dealData['hands']['N']}</td>
			<td>&nbsp;</td></tr>
			<tr>
				<td>".print_r($dealData['hands']['W'],1)."</td>
				<td class='img'>{$dealData['image']}</td>
				<td>{$dealData['hands']['E']}</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>{$dealData['hands']['S']}</td>
			<td>&nbsp;</td></tr>
		</tbody>
	</table>";	
	return $text;
}

function bridge_helper_hand_diagram($handData) {
	$out = "
	!s {$handData[0]}<br />
	!h {$handData[1]}<br />
	!d {$handData[2]}<br />
	!c {$handData[3]}
	";	
	return $out;
}

function bridge_helper_find_deals ($text) {
	global $vul, $dealer, $baseCards, $suits, $handNames;

	$regex = '#\[deal.*hand="([^"]+)"\]#'; // -- thanks to michzimny: http://www.michzimny.pl
	$dealsNr = preg_match_all($regex, $text, $deals);

	return $deals[0];
}

function bridge_helper_get_deal_number($deal) {
	//TODO: custom deal number attribute name
	$dealNumberPos = strpos($deal,'nr');
	if($dealNumberPos) {
		$dealNumberStart = strpos($deal,'"',$dealNumberPos)+1;
		$dealNumberEnd = strpos($deal,'"',$dealNumberStart);
		$dealNumber = substr($deal, $dealNumberStart, $dealNumberEnd-$dealNumberStart);
	} else {
		$dealNumber = 0;
	}
	return $dealNumber;
}

function bridge_helper_get_hands($deal) {
	//TODO: custom hands attribute name
	global $suits, $baseCards;

	// -- grab hands string from article
	$handPosition = strpos($deal,'hand');
	$handStart = strpos($deal,'"',$handPosition)+1;
	$handEnd = strpos($deal,'"',$handStart);
	$hands = substr($deal, $handStart, $handEnd - $handStart);
	$hands = explode(';',$hands);
	if(!isset($hands[3]))
	$hands[3] = '';
	// -- rewriting for clear coding
	$hands = Array(
	'N' => $hands[0],
	'E' => $hands[1],
	'S' => $hands[2],
	'W' => $hands[3]
	);

	// -- split each hand into suits
	foreach($hands as $position => &$hand) {
		$newHand = '';
		for($licz=0;$licz<strlen($hand);$licz++)
		$newHand .= substr($hand, $licz,1) . " ";
		//TODO: other '10' input methods ('1' and 'T');
		$newHand = str_replace("1 0","10",$newHand);
		if($position != 'W' || strlen($hands['W']))
			$hand = explode('.',$newHand);

		// -- set card as 'used' in other hand
		// TODO: English variable names
		if(!strlen($hands['W']) && $position != 'W') {
			foreach($hand as $index => $kolor) {
				$kolor = explode(' ',$kolor);
				foreach($kolor as $karta) {
					$baseCards[$suits[$index]][$karta] = 0;
				}
			}
		}
		if($position != 'W' || strlen($hands['W']))
		$hand = bridge_helper_hand_diagram($hand);
	}

	// -- filling West's hand
	if(!$hands['W']) {
		foreach($suits as $kolor) {
			$karty = "";
			foreach($baseCards[$kolor] as $karta => $czyKarta) {
				if($czyKarta)
				$karty .= $karta . ' ';
			}
			$hands['W'] .= '!' . $kolor . ' ' . $karty . "<br />";
		}
	}
	return $hands;
}

function bridge_helper_prepare_diagrams ($dealsFound) {

	// -- using global variables
	global $vul, $dealer;
	// -- placeholder for prepared diagrams
	$dealDiagramsData = Array();
	foreach($dealsFound as $dealCounter => $deal) {

		$dealData['nr'] = bridge_helper_get_deal_number($deal);
		$dealData['vul'] = $vul[$dealData['nr'] % 16]; // -- vulnerability repeats after each 16 deals
		$dealData['dealer'] = $dealer[$dealData['nr'] % 4]; // -- dealer repeats after each 4 deals
		$dealData['image'] = '<img src="' . plugins_url("/images/" . $dealData['nr'] % 16 . ".gif",__FILE__) . '" alt ="[ ' . $dealData['nr'] % 16 . ' ]" />';
		$dealData['hands'] = bridge_helper_get_hands($deal);

		// -- generate diagram andsave all into $dealDiagrams array
		$dealDiagrams[$dealCounter] = bridge_helper_board_diagram($dealData);
	}

	return $dealDiagrams;
}
// ----------------------------------------------------------------------------
// --- filters and their functions

add_filter('the_content', 'bridge_helper_deals_content');
add_filter('the_excerpt', 'bridge_helper_deals_excerpt');

function bridge_helper_deals_excerpt($text) {
	$dealsFound = bridge_helper_find_deals($text);

	if(!count($dealsFound))
	return $text;

	$emptyArray = Array();
	foreach($dealsFound as $i => $deal)
	$emptyArray[$i] = '[rozdanie]';
	$text = str_replace($dealsFound, $emptyArray, $text);
	//$text .= "<p>Znaleziono ".count($dealsFound)." rozdań. Numery rozdań:</p>"; // -- debugging purposes only

	return $text;
}

function bridge_helper_deals_content($text) {
	$dealsFound = bridge_helper_find_deals($text);

	if(!count($dealsFound))
	return $text;

	$dealDiagrams = bridge_helper_prepare_diagrams($dealsFound);
	$text = str_replace($dealsFound, $dealDiagrams, $text);

	return $text;
}


// ############################################################################
// ### Bridge Symbol Substitution

function bridge_helper_card_symbols($text) {

	$needle = Array('!C','!D', '!H', '!S');
	$hay1 = Array('&clubs;', '&diams;', '&hearts;', '&spades;');
	$hay2 = Array(
		'<span class="clubs">&clubs;</span>',
		'<span class="diams">&diams;</span>',
		'<span class="hearts">&hearts;</span>',
		'<span class="spades">&spades;</span>'
		);

		// TODO: Styling by params
		$text = str_ireplace($needle, $hay1, $text);
		$text = str_ireplace($hay1, $hay2, $text);

		return $text;
}

add_filter('the_content', 'bridge_helper_card_symbols');
add_filter('the_excerpt', 'bridge_helper_card_symbols');
add_filter('comment_text', 'bridge_helper_card_symbols');

// ############################################################################
// ### Dashboard Menu

add_action('admin_menu', 'bridge_helper_dashboard');

function bridge_helper_dashboard() {
	add_options_page('Bridge Helper by ljasinski.pl', 'Bridge Helper', 'manage_options', 'bridge_helper_dashboard', 'bridge_helper_dashboard_show');
}
// TODO: Board diagram

function bridge_helper_dashboard_show() {
	include_once(plugins_url("/bridgehelper_dashboard.php",__FILE__));
}


?>