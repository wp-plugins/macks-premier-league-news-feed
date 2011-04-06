<?php
/*
Plugin Name: Mack's Premier League News Feed
Description: Lists the Latest from the English Premier League
Author: Mack Bincroft
Version: 0.5
Author URI: http://www.jawer.org
*/


#
# Function Displays the Feed
#
function widget_macks_premier_league_news_feed() 
{
?>
<?php get_macks_premier_league_news_feed(); ?>
<?php
}
#
# Function registers the plugin
#
function macks_premier_league_news_feed_install()
{
register_sidebar_widget(__('Mack\'s Premier League News Feed'), 'widget_macks_premier_league_news_feed'); 
}
add_action("plugins_loaded", "macks_premier_league_news_feed_install");
#
# Activation Hook
#
register_activation_hook(__FILE__,'macks_premier_league_news_feed_activate');
#
# Deactivation Hook
#
register_deactivation_hook( __FILE__, 'macks_premier_league_news_feed_remove' );
#
# Let's Install Mack's Plugin :)
#
function macks_premier_league_news_feed_activate() 
{
add_option("macks_premier_league_news_feed_width", "162", "Width of Feed", "yes");
add_option("macks_premier_league_news_feed_height", "290", "Height Of Feed", "yes");
add_option("macks_premier_league_news_feed_articles", "3", "Article Count", "yes");
add_option("mackdbwidget", "0", "Menu Status", "yes");
}
#
# Let's Remove Mack's Plugin :(
#
function macks_premier_league_news_feed_remove() 
{
delete_option('macks_premier_league_news_feed_width');
delete_option('macks_premier_league_news_feed_height');
delete_option('macks_premier_league_news_feed_articles');
delete_option('mackdbwidget');
}

#
# Thanks Ricardo for this function!
#
/***************************************************************************

Plugin Name:  MainFunction
Plugin URI:   http://rick.jinlabs.com
Description:  Add options to your dashboard widgets.
Version:      0.1
Author:       Ricardo Gonz&aacute;lez Castro

**************************************************************************/
function Premier_League_fetch_rss_items( $num ) {
			include_once(ABSPATH . WPINC . '/feed.php');
			$rss = fetch_feed( 'http://www.jawer.org/wp-plugins/macks_feed.xml' );
			
			// Bail if feed doesn't work
			if ( is_wp_error($rss) )
				return false;
			
			$rss_items = $rss->get_items( 0, $rss->get_item_quantity( $num ) );
			
			// If the feed was erroneously 
			if ( !$rss_items ) {
				$md5 = md5( 'http://www.jawer.org/wp-plugins/macks_feed.xml' );
				delete_transient( 'feed_' . $md5 );
				delete_transient( 'feed_mod_' . $md5 );
				$rss = fetch_feed( 'http://www.jawer.org/wp-plugins/macks_feed.xml' );
				$rss_items = $rss->get_items( 0, $rss->get_item_quantity( $num ) );
			}
			
			return $rss_items;
		}


/**
 * Content of Dashboard-Widget
 */
function Premier_League_MainFunction() {
			#
			# Assigns A number to $menustats for menu status
			#
			$menustats = get_option('mackdbwidget');
			#
			# If User Clicks X button to cancel feed
			#
			if (isset($_POST['mack_removedbwidget']))
				{
				update_option('mackdbwidget','1');
				}
			#
			# Otherwise, the Menu will Display
			#
			if ($menustats == 0)
			{
			#
			# Get the RSS Feed
			#
			$rss_items = Premier_League_fetch_rss_items(3);
			
				echo '<div class="rss-widget">';		
				echo '<ul>';

				if ( !$rss_items ) 
					{
			    	echo '<li class="mack">No information to display at this time...</li>';
					} 
				else 
					{
			    foreach ( $rss_items as $item ) 
						{
						echo '<li class="mack">';
						echo '<a class="rsswidget" href="'.esc_url( $item->get_permalink(), $protocolls=null, 'display' ).'">'. esc_html( $item->get_title() ) .'</a>';
						echo ' <span class="rss-date">'. $item->get_date('F j, Y') .'</span>';
						echo '<div class="rssSummary">'. esc_html( strip_tags( $item->get_description() ), 150).'</div>';
						echo '</li>';
			    		}
					}						

				echo '</ul>';
				echo '<br class="clear"/><div style="margin-top:10px;border-top: 1px solid #ddd; padding-top: 8px; text-align:center;">Click the "X" icon to remove this feed. Then, reload the control panel with your browser button to see the change. You can activate the feed again using Mack\'s plugin control panel.<br />';
				echo '<form class="alignright" method="post"><input type="hidden" name="mack_removedbwidget" value="true"/><input title="Remove this widget from all users dashboards" class="button" type="submit" value="X" onClick="window.location.href=window.location.href"/></form>';
				echo '</div>';
				echo '</div>';
			}
			else
			{
			### Don't Display Anything
			}
}
/**
 * add Dashboard Widget via function wp_add_dashboard_widget()
 */
function Premier_League_MainFunction_Init() {
	wp_add_dashboard_widget( 'Premier_League_MainFunction', __( 'Macks\'s Premier League News' ), 'Premier_League_MainFunction', 'Premier_League_MainFunction_Setup' );
}
#
# The Setup Function like Yoast WP-SEO Plugin
#
function Premier_League_MainFunction_Setup() {
	    		wp_add_dashboard_widget( 'mack_db_widget' , 'The Latest From Mack' , array(&$this, 'Premier_League_MainFunction') );
		}
 
#
# This function sorts the order of dashboard boxes, like Yoast WP-SEO Plugin
#
function Premier_League_MainFunction_Order( $arr ) 
 			{
			#
			# WP's Global Meta Boxes
			#
			global $wp_meta_boxes;
			#
			# Test if User wants Menu
			#
			$menu = get_option('mackdbwidget');
			
			if ($menu == 1)
				{
				# No Menu is Shown
				}
			else
				{
					if ( is_admin() ) 
						{
						if ( isset($wp_meta_boxes['dashboard']['normal']['core']['Premier_League_MainFunction']) ) 		{
						$Premier_League_MainFunction = $wp_meta_boxes['dashboard']['normal']['core']['Premier_League_MainFunction'];
						unset($wp_meta_boxes['dashboard']['normal']['core']['Premier_League_MainFunction']);
						if ( isset($wp_meta_boxes['dashboard']['side']['core']) ) 
							{
							$begin = array_slice($wp_meta_boxes['dashboard']['side']['core'], 0, 1);
							$end = array_slice($wp_meta_boxes['dashboard']['side']['core'], 1, 5);
							$wp_meta_boxes['dashboard']['side']['core'] = $begin;
							$wp_meta_boxes['dashboard']['side']['core'][] = $Premier_League_MainFunction;
							$wp_meta_boxes['dashboard']['side']['core'] += $end;
							} 
						else 
							{
							$wp_meta_boxes['dashboard']['side']['core'] = array();
							$wp_meta_boxes['dashboard']['side']['core'][] = $Premier_League_MainFunction;
							}
						}
					}
		}
		return $arr;
	}
/**
 * use hook, to integrate new widget
 */
add_action('wp_dashboard_setup', 'Premier_League_MainFunction_Init');
add_action('wp_dashboard_setup', 'Premier_League_MainFunction_Order');
add_filter('wp_dashboard_setup', 'Premier_League_MainFunction_Init');
add_filter('wp_dashboard_setup', 'Premier_League_MainFunction_Order');
#
# Menu for the Plugin, adding options to WP
#
if ( is_admin() )
	{
	add_action('admin_menu', 'macks_premier_league_news_feed_admin_menu');

	function macks_premier_league_news_feed_admin_menu() {
		add_options_page('Mack\'s Premier League News Feed', 'Mack\'s Premier League News Feed Settings', 'administrator', 'macks-premier-league-news-feed.php', 'macks_premier_league_news_feed_plugin_page');
		}
	}
#
# The actual code that calls the feed dynamically
#
function get_macks_premier_league_news_feed()
{
require_once(dirname(__FILE__) . '/rss_fetch.inc');
#
# Use A Different Magpie RSS Grabber, Just the Fetch.inc
#
define('MAGPIE_FETCH_TIME_OUT', 60);
#
# Generous Timeout for Server
#
define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');
#
# Encoding: Self Explanatory
#
define('MAGPIE_CACHE_ON', 0);
#
# We don't need to cache any of this
#
$width = get_option('macks_premier_league_news_feed_width');
$height = get_option('macks_premier_league_news_feed_height');
$articles = get_option('macks_premier_league_news_feed_articles');
#
# The feed URL where we can get the actual news and customized size
#
$feedurl = "http://www.jawer.org/wp-plugins/macks-premier-league-news-feed/macks-premier-league-news-creator.php?mywidth=$width&myheight=$height&articles=$articles";
#
# Use Magpie to Get the Feed
#
$rss = macks_premier_league_news_feed_fetch_rss( $feedurl );

#
# Parse the RSS Information Out
#
foreach ($rss->items as $item) 
		{
		# The Item Title 
		$title = $item['title'];
		# The Item Story
		$description = $item['description'];
		# Tes for the Feed Titiel
		if ($title == 'MacksPremierLeague')
			{
			# This is essentially the information for the feed
			$adform = $description;
			}
		}
echo $adform;
}
#
# The User Control Panel Page
#
function macks_premier_league_news_feed_plugin_page() { ?>
<div class="postbox-container" style="width:70%; height:40%;"> 
<div class="metabox-holder">	
	<div class="meta-box-sortables"> 
	<div id="general-settings" class="postbox"> 
	<h3 class="hndle"><span>Mack's Premier League Feed Settings</span></h3> 
	<div class="inside">

<!-- -->
<img src="../wp-content/plugins/macks-premier-league-news-feed/mack.JPG" width="91" height="64" alt="Mack" longdesc="http://www.wordpress.org" />
<p>You can use this page to set the width and height of your feed box, plus select how many articles you wish to display. It is recommended to use the default settings, width=162 and height=290, for best performance. The default number of articles is 3. You can choose up to 10.</p>
<br />
<strong>Adjust Feed Width</strong> 
<br />
<br />
Enter a Number Value for Width in Pixels
<br />
<form name="setwidth" method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>
	<input type="Text" name="macks_premier_league_news_feed_width" id="macks_premier_league_news_feed_width" value="<?php echo get_option('macks_premier_league_news_feed_width'); ?>" />
      <br />
      <input type="hidden" name="action" value="update" />
   	  <input type="hidden" name="page_options" value="macks_premier_league_news_feed_width" />
      <input name="macks_default_width" type="hidden" value="162" />
	  <input type="button" value="Default Width" onClick="document.setwidth.macks_premier_league_news_feed_width.value=document.setwidth.macks_default_width.value">
  	  <p>
      <input type="submit" value="<?php _e('Save Changes') ?>" />
     </p>
  
</form>
<strong>Adjust Feed Height</strong> 
<br />
<br />
Enter a Number Value for Height in Pixels
<br />
<form name="setheight" method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>
	<input type="Text" name="macks_premier_league_news_feed_height" id="macks_premier_league_news_feed_height" value="<?php echo get_option('macks_premier_league_news_feed_height'); ?>" />
      <br />
      <input type="hidden" name="action" value="update" />
   	  <input type="hidden" name="page_options" value="macks_premier_league_news_feed_height" />
      <input name="macks_default_height" type="hidden" value="290" />
	  <input type="button" value="Default Height" onClick="document.setheight.macks_premier_league_news_feed_height.value=document.setheight.macks_default_height.value">
  	  
  	  <p>
      <input type="submit" value="<?php _e('Save Changes') ?>" />
     </p>
  
</form>
<strong>Adjust Feed Articles</strong> 
<br />
<br />
Select the number of articles the feed will display. Choose up to 10. <br />Currently, <strong><?php echo get_option('macks_premier_league_news_feed_articles'); ?></strong> are being displayed.
<br />
<form name="setarticles" method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>
	<select name="macks_premier_league_news_feed_articles" id="macks_premier_league_news_feed_articles" value="<?php echo get_option('macks_premier_league_news_feed_articles'); ?>" />
    <option value="1">1</option>
    <option value="2">2</option>
    <option value="3">3</option>
    <option value="4">4</option>
    <option value="5">5</option>
    <option value="6">6</option>
    <option value="7">7</option>
    <option value="8">8</option>
	<option value="9">9</option>
	<option value="10">10</option>
    </select>
    <input type="hidden" name="action" value="update" />
   	  <input type="hidden" name="page_options" value="macks_premier_league_news_feed_articles" />
  	  <p>
      <input type="submit" value="<?php _e('Save Changes') ?>" />
     </p>
</form>
<br />
<strong>Turn Mack's Dashboard Feed On?</strong>
<form name="setmenu" method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>
	<select name="mackdbwidget" id="mackdbwidget" value="<?php echo get_option('mackdbwidget'); ?>" />
    <option value="0">Yes</option>
    <option value="1">No</option>
    </select>
    <input type="hidden" name="action" value="update" />
   	  <input type="hidden" name="page_options" value="mackdbwidget" />
  	  <p>
      <input type="submit" value="<?php _e('Save Changes') ?>" />
     </p>
</form>
<br  />
</div>
</div>
</div>
</div>
</div>
<div class="postbox-container" style="width:20%; height:40%;"> 
<div class="metabox-holder">	
	<div class="meta-box-sortables"> 
	<div id="general-settings" class="postbox"> 
	<h3 class="hndle"><span>Sample Output</span></h3> 
	<div class="inside">
    <p>
<?php echo get_macks_premier_league_news_feed(); ?>
</p>
</div>
</div>
</div>
</div>
</div>

   <?php
   }
?>