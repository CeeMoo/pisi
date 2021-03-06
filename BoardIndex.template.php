<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines http://www.simplemachines.org
 * @copyright 2015 Simple Machines and individual contributors
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1 Beta 2
 */

function template_boardindex_outer_above()
{
	template_newsfader();
}

function template_newsfader()
{
	global $context, $settings, $options, $txt;

	// Show the news fader?  (assuming there are things to show...)
	if (!empty($settings['show_newsfader']) && !empty($context['news_lines']))
	{
		echo '
		<ul id="smf_slider" class="roundframe">';

		foreach ($context['news_lines'] as $news)
		{
			echo '
			<li>', $news,'</li>';
		}

		echo '
		</ul>
		<script>
			jQuery("#smf_slider").slippry({
				pause: ', $settings['newsfader_time'],',
				adaptiveHeight: 0,
				captions: 0,
				controls: 0,
			});
		</script>';
	}
}

function template_main()
{
	global $context, $txt, $scripturl,$boarddir,$modSettings, $boardurl;
	echo '<table border="0" width="100%" cellpadding="0" cellspacing="0">
		<tbody><tr>';
		require_once($boarddir . '/SSI.php');
		if (!empty($modSettings['sideleft']))
			{
		    echo '<td valign="top">
			<button type="button" id="teknoleft" title="" onclick="leftPanel.toggle();"></button>
			</td>
			<td valign="top" id="upshrinkLeftBarTD">
				<div id="upshrinkLeftBar" style="width:',$modSettings['sideleftwidth'] ? $modSettings['sideleftwidth'] :'200px','; margin-right:4px; overflow:auto;" >
				', empty($modSettings['sideleft1']) ? '' : '<div class="cat_bar"><h3 class="catbg">'.$modSettings['lefthtmlbaslik'].'</h3></div>'.$modSettings['sideleft1'].'', '
				', empty($modSettings['sideleftphp']) ? '' : '<div class="cat_bar"><h3 class="catbg">'.$modSettings['leftphpbaslik'].'</h3></div>';eval($modSettings['sideleftphp']);
				if (!empty($modSettings['sidelefthaberetkin']))
				{
				$array = ssi_boardNews($modSettings['sidelefthaber'], $modSettings['sideleftsay'], null, 1000, 'array');
					echo'<div class="cat_bar">
							<h3 class="catbg">',$modSettings['lbaslik'],'</h3>
						</div>';
					global $memberContext;	
					foreach ($array as $news)
					 {
					  loadMemberData($news['poster']['id']);
					  loadMemberContext($news['poster']['id']);
						  echo '<div class="sidehaber">
								<div class="sideBaslik">
								', $news['icon'], '
								<h3><a href="', $news['href'], '">', shorten_subject($news['subject'], 30), '</a></h3>
								</div>
								<div class="snrj"> ', $memberContext[$news['poster']['id']]['avatar']['image'],' 
								<p>', $txt['by'], '', $news['poster']['link'], '</p>
								</div>
								</div><hr/>';
					 }
				} 
				echo'</div>
			</td>';
			}

			echo '<td valign="top" width="100%">
			<table id="maintable" border="0" width="100%" cellpadding="0" cellspacing="0"><tbody><tr><td valign="top">';	

			echo '<div id="uyari">Lütfen Yardım ve Destek için Sohbet alanı ve Kişisel iletileri kullanmayın.Herkezin bilgilenmesi için <span class="generic_icons moderate"></span><a href="index.php?board=24.0">2.0.x sorularınız için buraya</a> <span class="generic_icons moderate"></span><a href="index.php?board=35.0">2.1.x sorularınız için buraya</a></div>';


				 require_once($boarddir."/NChat/NChatBoardIndex.php");
			
			echo '
	<div id="boardindex_table" class="boardindex_table">';

	/* Each category in categories is made up of:
	id, href, link, name, is_collapsed (is it collapsed?), can_collapse (is it okay if it is?),
	new (is it new?), collapse_href (href to collapse/expand), collapse_image (up/down image),
	and boards. (see below.) */
	foreach ($context['categories'] as $category)
	{
		// If theres no parent boards we can see, avoid showing an empty category (unless its collapsed)
		if (empty($category['boards']) && !$category['is_collapsed'])
			continue;

		echo '
		<div class="main_container">
			<div class="cat_bar" id="category_', $category['id'], '">
				<h3 class="catbg">';

		// If this category even can collapse, show a link to collapse it.
		if ($category['can_collapse'])
			echo '
					<span id="category_', $category['id'], '_upshrink" class="', $category['is_collapsed'] ? 'toggle_down' : 'toggle_up', ' floatright" data-collapsed="', (int) $category['is_collapsed'], '" title="', !$category['is_collapsed'] ? $txt['hide_category'] : $txt['show_category'] ,'" style="display: none;"></span>';

		echo '
					<b>SMF </b>', $category['link'], '
				</h3>', !empty($category['description']) ? '
				<div class="desc">' . $category['description'] . '</div>' : '', '
			</div>
			<div id="category_', $category['id'], '_boards">';

			/* Each board in each category's boards has:
			new (is it new?), id, name, description, moderators (see below), link_moderators (just a list.),
			children (see below.), link_children (easier to use.), children_new (are they new?),
			topics (# of), posts (# of), link, href, and last_post. (see below.) */
			foreach ($category['boards'] as $board)
			{
				echo '
				<div id="board_', $board['id'], '" class="up_contain">
					<div class="icon">
						<a href="', ($board['is_redirect'] || $context['user']['is_guest'] ? $board['href'] : $scripturl . '?action=unread;board=' . $board['id'] . '.0;children'), '">
							<span class="board_', $board['board_class'], '"', !empty($board['board_tooltip']) ? ' title="' . $board['board_tooltip'] . '"' : '', '></span>
						</a>
					</div>
					<div class="info">
						<h2><a class="subject" href="', $board['href'], '" id="b', $board['id'], '">', $board['name'], '</a></h2>';

				// Has it outstanding posts for approval?
				if ($board['can_approve_posts'] && ($board['unapproved_posts'] || $board['unapproved_topics']))
					echo '
						<a href="', $scripturl, '?action=moderate;area=postmod;sa=', ($board['unapproved_topics'] > 0 ? 'topics' : 'posts'), ';brd=', $board['id'], ';', $context['session_var'], '=', $context['session_id'], '" title="', sprintf($txt['unapproved_posts'], $board['unapproved_topics'], $board['unapproved_posts']), '" class="moderation_link">(!)</a>';

				echo '

						<p>', $board['description'] , '</p>';
	// Show the "Child Boards: ". (there's a link_children but we're going to bold the new ones...)
				if (!empty($board['children']))
				{
					// Sort the links into an array with new boards bold so it can be imploded.
					$children = array();
					/* Each child in each board's children has:
							id, name, description, new (is it new?), topics (#), posts (#), href, link, and last_post. */
					foreach ($board['children'] as $child)
					{
						if (!$child['is_redirect'])
							$child['link'] = '<a href="' . $child['href'] . '" ' . ($child['new'] ? 'class="board_new_posts" ' : '') . 'title="' . ($child['new'] ? $txt['new_posts'] : $txt['old_posts']) . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')">' . $child['name'] . ($child['new'] ? '</a> <a href="' . $scripturl . '?action=unread;board=' . $child['id'] . '" title="' . $txt['new_posts'] . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')"><span class="new_posts">' . $txt['new'] . '</span>' : '') . '</a>';
						else
							$child['link'] = '<a href="' . $child['href'] . '" title="' . comma_format($child['posts']) . ' ' . $txt['redirects'] . ' - ' . $child['short_description'] . '">' . $child['name'] . '</a>';

						// Has it posts awaiting approval?
						if ($child['can_approve_posts'] && ($child['unapproved_posts'] || $child['unapproved_topics']))
							$child['link'] .= ' <a href="' . $scripturl . '?action=moderate;area=postmod;sa=' . ($child['unapproved_topics'] > 0 ? 'topics' : 'posts') . ';brd=' . $child['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" title="' . sprintf($txt['unapproved_posts'], $child['unapproved_topics'], $child['unapproved_posts']) . '" class="moderation_link">(!)</a>';

						$children[] = $child['new'] ? '<strong>' . $child['link'] . '</strong>' :'<strong>' . $child['link'] . '</strong>';
					}

				echo '
					<div id="board_', $board['id'], '_children" class="children">
						<p>', implode(' ', $children), '</p>
					</div>';
				}
				// Show the "Moderators: ". Each has name, href, link, and id. (but we're gonna use link_moderators.)
				if (!empty($board['link_moderators']))
					echo '
						<p class="moderators">', count($board['link_moderators']) == 1 ? $txt['moderator'] : $txt['moderators'], ': ', implode(', ', $board['link_moderators']), '</p>';

				// Show some basic information about the number of posts, etc.
					echo '
					</div>
					<div class="stats">
						<p>', comma_format($board['posts']), ' ', $board['is_redirect'] ? $txt['redirects'] : $txt['posts'], '
						', $board['is_redirect'] ? '' : '<br> ' . comma_format($board['topics']) . ' ' . $txt['board_topics'], '
						</p>
					</div>
					<div class="lastpost">';
					if (!empty($board['last_post']['id']))
					echo '
						<p>', $board['last_post']['last_post_message'], '</p>';
				echo '
					</div>';
			

				echo '
					</div>';
			}

		echo '
			</div>
		</div>';
	}

	echo '
	</div>';

	// Show the mark all as read button?
	if ($context['user']['is_logged'] && !empty($context['categories']))
		echo '
		<div class="mark_read">', template_button_strip($context['mark_read_button'], 'right'), '</div>';
}

function template_boardindex_outer_below()
{
		global $modSettings;
	// Info center collapse object.
	echo '</td></tr></tbody></table>';
		if (!empty($modSettings['sideright']))
			{
			echo '<td valign="top" id="upshrinkRightBarTD">
				<div id="upshrinkRightBar" style="width:',$modSettings['siderightwidth'] ? $modSettings['siderightwidth'] :'200px','; overflow:hidden;">
				', empty($modSettings['sideright1']) ? '' : '<div class="cat_bar"><h3 class="catbg">'.$modSettings['righthtmlbaslik'].'</h3></div>'.$modSettings['sideright1'].'', '
				', empty($modSettings['siderightphp']) ? '' : '<div class="cat_bar"><h3 class="catbg">'.$modSettings['rightphpbaslik'].'</h3></div>';eval($modSettings['siderightphp']);
				if (!empty($modSettings['siderighthaberetkin']))
				{
				$array = ssi_boardNews($modSettings['siderighthaber'], $modSettings['siderightsay'], null, 1000, 'array');
					echo'<div class="cat_bar">
							<h3 class="catbg">',$modSettings['rbaslik'],'</h3>
						</div>';
					global $memberContext;	
                     
					foreach ($array as $news)
					 {
					  loadMemberData($news['poster']['id']);
					  loadMemberContext($news['poster']['id']);
						  echo '<div class="sidehaber">
								<div class="sideBaslik">
								
								<h3><a href="', $news['href'], '"><span class="generic_icons sort_up"></span> ', shorten_subject($news['subject'], 30), '</a></h3>
								</div>
								<div class="snrj"> ', $memberContext[$news['poster']['id']]['avatar']['image'],' 
								<p>', $txt['by'], '', $news['poster']['link'], '</p>
								</div>
								</div>';
					 }
				 } 
				echo '</div>
			</td>
			<td valign="top">
			<button type="button" onclick="rightPanel.toggle();" id="teknoright"></button>
			</td>';
			}
				echo '</td>
		</tr></tbody></table>';
		template_info_center();
}

function template_info_center()
{
	global $context, $options, $txt;

	if (empty($context['info_center']))
		return;

	// Here's where the "Info Center" starts...
	echo '
	<div class="roundframe" id="info_center">
		<div class="title_bar">
			<h3 class="titlebg">
				<span class="toggle_up floatright" id="upshrink_ic" title="', $txt['hide_infocenter'], '" style="display: none;"></span>
				<a href="#" id="upshrink_link">', sprintf($txt['info_center_title'], $context['forum_name_html_safe']), '</a>
			</h3>
		</div>
		<div id="upshrinkHeaderIC"', empty($options['collapse_header_ic']) ? '' : ' style="display: none;"', '>';

	foreach ($context['info_center'] as $block)
	{
		$func = 'template_ic_block_' . $block;
		$func();
	}

	echo '
		</div>
	</div>';

	// Info center collapse object.
	echo '
<script><!-- // --><![CDATA[
		var teknoromi_sidebar = new Object();
		teknoromi_sidebar["left"] = "left Panel";
		teknoromi_sidebar["right"] = "right Panel";
		function setUpshrinkTitles() {if(this.opt.bToggleEnabled){ var panel = this.opt.aSwappableContainers[0].substring(8, this.opt.aSwappableContainers[0].length - 3).toLowerCase(); document.getElementById("tekno" + panel).setAttribute("title", (this.bCollapsed ? "Hide the " : "Show the ") + teknoromi_sidebar[panel]);}}	
		var leftPanel=new smc_Toggle({bToggleEnabled:true,bCurrentlyCollapsed:false,funcOnBeforeCollapse:setUpshrinkTitles,funcOnBeforeExpand:setUpshrinkTitles,aSwappableContainers:[\'upshrinkLeftBar\'],oCookieOptions:{bUseCookie:true,sCookieName:\'upshrleftPanel\',sCookieValue:\'0\'}});	
		var rightPanel=new smc_Toggle({bToggleEnabled:true,bCurrentlyCollapsed:false,funcOnBeforeCollapse:setUpshrinkTitles,funcOnBeforeExpand:setUpshrinkTitles,aSwappableContainers:[\'upshrinkRightBar\'],oCookieOptions:{bUseCookie:true,sCookieName:\'upshrrightPanel\',sCookieValue:\'0\'}});	
		var oInfoCenterToggle = new smc_Toggle({
			bToggleEnabled: true,
			bCurrentlyCollapsed: ', empty($options['collapse_header_ic']) ? 'false' : 'true', ',
			aSwappableContainers: [
				\'upshrinkHeaderIC\'
			],
			aSwapImages: [
				{
					sId: \'upshrink_ic\',
					altExpanded: ', JavaScriptEscape($txt['hide_infocenter']), ',
					altCollapsed: ', JavaScriptEscape($txt['show_infocenter']), '
				}
			],
			aSwapLinks: [
				{
					sId: \'upshrink_link\',
					msgExpanded: ', JavaScriptEscape(sprintf($txt['info_center_title'], $context['forum_name_html_safe'])), ',
					msgCollapsed: ', JavaScriptEscape(sprintf($txt['info_center_title'], $context['forum_name_html_safe'])), '
				}
			],
			oThemeOptions: {
				bUseThemeSettings: ', $context['user']['is_guest'] ? 'false' : 'true', ',
				sOptionName: \'collapse_header_ic\',
				sSessionId: smf_session_id,
				sSessionVar: smf_session_var,
			},
			oCookieOptions: {
				bUseCookie: ', $context['user']['is_guest'] ? 'true' : 'false', ',
				sCookieName: \'upshrinkIC\'
			}
		});
	// ]]></script>';
}

function template_ic_block_recent()
{
			global $context, $scripturl, $settings, $txt,$modSettings;

	// Teknoromi bar.
	echo '<div id="recent_posts_content">';

	echo'<dl style="width: 100%;"><dt class="teknoromistats">	
<section class="tab-area tabs-checked">
			<input checked="checked" name="tab" id="tab-A" type="radio">
			<input name="tab" id="tab-B" type="radio">
			<input name="tab" id="tab-C" type="radio">
			<input name="tab" id="tab-D" type="radio">
			<label class="tab-link up_contain" for="tab-A">',$txt['teknoromi1'],'</label>
			<label class="tab-link up_contain" for="tab-B">',$txt['teknoromi2'],'</label>
			<label class="tab-link up_contain" for="tab-C">',$txt['teknoromi3'],'</label>';
			 if (!empty($modSettings['enable_likes']))
			{echo'<label class="tab-link up_contain" for="tab-D">',$txt['teknoromi7'],'</label>';}
echo'<article class="tab up_contain"><dl>';
				if (!empty($context['latest_posts']))
	foreach ($context['latest_posts'] as $post)
	{echo '<dt class="silboard"><span style="background-position: 0px -110px;">&nbsp</span><a href="',$post['href'],'">', $post['short_subject'], '</a></dt><dd class="silboard1">', $post['board']['link'],  '</dd><dd class="sil">', $post['poster']['link'],'</dd>';}

			echo'</dl></article>
			<article class="tab up_contain"><dl>';
	foreach ($context['top_topics_replies'] as $topic)
	{echo '<dt><span style="background-position: 0px -154px;">&nbsp</span>', $topic['link'], '</dt><dd class="fmavi"><span class="fmavi1">', $topic['num_replies'], '</span></dd>';}
			echo'</dl></article>
			<article class="tab up_contain"><dl>';
	foreach ($context['top_topics_views'] as $topic)
	{echo '<dt><span style="background-position: 0px -132px;">&nbsp</span>', $topic['link'], '</dt><dd class="fmavi"><span class="fmavi1">', $topic['num_views'], '</span></dd>';}
			echo'</dl></article>';
		if (!empty($modSettings['enable_likes']))
	{	echo'<article class="tab up_contain"><dl>';
	foreach ($context['stats_blocks']['liked_messages'] as $topic)
	{echo '<dt><span style="background-position: 0px -132px;">&nbsp</span>', $topic['link'], '</dt><dd class="fmavi"><span class="fmavi1">', $topic['num'], '</span></dd>';}
			echo'</dl></article>';
	}			
		echo'</section></dt><dd class="teknoromistats1">
			<section class="tab-area tabs-checked">
			<input checked="checked" name="tab" id="tab-E" type="radio">
			<input name="tab" id="tab-F" type="radio">
			<input name="tab" id="tab-G" type="radio">
			<input name="tab" id="tab-Z" type="radio">
			<label class="tab-link up_contain" for="tab-E">',$txt['teknoromi4'],'</label>
			<label class="tab-link up_contain" for="tab-F">',$txt['teknoromi5'],'</label>
			<label class="tab-link up_contain" for="tab-G">',$txt['teknoromi6'],'</label>
	<article class="tab up_contain"><dl>';
				foreach ($context['top_starters'] as $poster)
	{echo '<dt><span style="background-position: 0px -22px;">&nbsp</span>', $poster['link'], '</dt><dd class="fmavi"><span class="fmavi1">', $poster['num_topics'], '</span></dd>';}
			echo'</dl></article>
	<article class="tab up_contain"><dl>';
			foreach ($context['top_posters'] as $poster)
	{echo '<dt><span style="background-position: 0px 0px;">&nbsp</span>', $poster['link'], '</dt><dd class="fmavi"><span class="fmavi1">', $poster['num_posts'], '</span></dd>'; }
			echo'</dl></article>
			<article class="tab up_contain">';
				
	foreach ($context['new_members'] as $poster)
	{echo '<dt><span style="background-position: 0px -88px;">&nbsp</span>',$poster['link'], '</dt><dd>&nbsp</dd>'; }
	echo'</article>
	</section></dd></dl>';
 
	echo '
			</div>';
}

function template_ic_block_calendar()
{
	global $context, $scripturl, $txt, $settings;

	// Show information about events, birthdays, and holidays on the calendar.
	echo '
			<div class="sub_bar">
				<h4 class="subbg">
					<a href="', $scripturl, '?action=calendar' . '"><span class="generic_icons calendar"></span> ', $context['calendar_only_today'] ? $txt['calendar_today'] : $txt['calendar_upcoming'], '</a>
				</h4>
			</div>';

	// Holidays like "Christmas", "Chanukah", and "We Love [Unknown] Day" :P.
	if (!empty($context['calendar_holidays']))
		echo '
				<p class="inline holiday"><span>', $txt['calendar_prompt'], '</span> ', implode(', ', $context['calendar_holidays']), '</p>';

	// People's birthdays. Like mine. And yours, I guess. Kidding.
	if (!empty($context['calendar_birthdays']))
	{
		echo '
				<p class="inline">
					<span class="birthday">', $context['calendar_only_today'] ? $txt['birthdays'] : $txt['birthdays_upcoming'], '</span>';
		// Each member in calendar_birthdays has: id, name (person), age (if they have one set?), is_last. (last in list?), and is_today (birthday is today?)
		foreach ($context['calendar_birthdays'] as $member)
			echo '
					<a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['is_today'] ? '<strong class="fix_rtl_names">' : '', $member['name'], $member['is_today'] ? '</strong>' : '', isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a>', $member['is_last'] ? '' : ', ';
		echo '
				</p>';
	}

	// Events like community get-togethers.
	if (!empty($context['calendar_events']))
	{
		echo '
				<p class="inline">
					<span class="event">', $context['calendar_only_today'] ? $txt['events'] : $txt['events_upcoming'], '</span> ';

		// Each event in calendar_events should have:
		//		title, href, is_last, can_edit (are they allowed?), modify_href, and is_today.
		foreach ($context['calendar_events'] as $event)
			echo '
					', $event['can_edit'] ? '<a href="' . $event['modify_href'] . '" title="' . $txt['calendar_edit'] . '"><span class="generic_icons calendar_modify"></span></a> ' : '', $event['href'] == '' ? '' : '<a href="' . $event['href'] . '">', $event['is_today'] ? '<strong>' . $event['title'] . '</strong>' : $event['title'], $event['href'] == '' ? '' : '</a>', $event['is_last'] ? '<br>' : ', ';
		echo '
				</p>';
	}
}

function template_ic_block_stats()
{
	global $scripturl, $txt, $context, $settings;

	// Show statistical style information...
	echo '
			<div class="sub_bar">
				<h4 class="subbg">
					<a href="', $scripturl, '?action=stats" title="', $txt['more_stats'], '"><span class="generic_icons stats"></span> ', $txt['forum_stats'], '</a>
				</h4>
			</div>
			<p class="inline">
				', $context['common_stats']['boardindex_total_posts'], '', !empty($settings['show_latest_member']) ? ' - '. $txt['latest_member'] . ': <strong> ' . $context['common_stats']['latest_member']['link'] . '</strong>' : '', '<br>
				', (!empty($context['latest_post']) ? $txt['latest_post'] . ': <strong>&quot;' . $context['latest_post']['link'] . '&quot;</strong>  (' . $context['latest_post']['time'] . ')<br>' : ''), '
				<a href="', $scripturl, '?action=recent">', $txt['recent_view'], '</a>
			</p>';
}

function template_ic_block_online()
{
	global $context, $scripturl, $txt, $modSettings, $settings;
	// "Users online" - in order of activity.
	echo '
			<div class="sub_bar">
				<h4 class="subbg">
					', $context['show_who'] ? '<a href="' . $scripturl . '?action=who">' : '', '<span class="generic_icons people"></span> ', $txt['online_users'], '', $context['show_who'] ? '</a>' : '', '
				</h4>
			</div>
			<p class="inline">
				', $context['show_who'] ? '<a href="' . $scripturl . '?action=who">' : '', '<strong>', $txt['online'], ': </strong>', comma_format($context['num_guests']), ' ', $context['num_guests'] == 1 ? $txt['guest'] : $txt['guests'], ', ', comma_format($context['num_users_online']), ' ', $context['num_users_online'] == 1 ? $txt['user'] : $txt['users'];

	// Handle hidden users and buddies.
	$bracketList = array();
	if ($context['show_buddies'])
		$bracketList[] = comma_format($context['num_buddies']) . ' ' . ($context['num_buddies'] == 1 ? $txt['buddy'] : $txt['buddies']);
	if (!empty($context['num_spiders']))
		$bracketList[] = comma_format($context['num_spiders']) . ' ' . ($context['num_spiders'] == 1 ? $txt['spider'] : $txt['spiders']);
	if (!empty($context['num_users_hidden']))
		$bracketList[] = comma_format($context['num_users_hidden']) . ' ' . ($context['num_spiders'] == 1 ? $txt['hidden'] : $txt['hidden_s']);

	if (!empty($bracketList))
		echo ' (' . implode(', ', $bracketList) . ')';

	echo $context['show_who'] ? '</a>' : '', '

				&nbsp;-&nbsp;', $txt['most_online_today'], ': <strong>', comma_format($modSettings['mostOnlineToday']), '</strong>&nbsp;-&nbsp;
				', $txt['most_online_ever'], ': ', comma_format($modSettings['mostOnline']), ' (', timeformat($modSettings['mostDate']), ')<br>';

	// Assuming there ARE users online... each user in users_online has an id, username, name, group, href, and link.
	if (!empty($context['users_online']))
	{
		echo '
				', sprintf($txt['users_active'], $modSettings['lastActive']), ': ', implode(', ', $context['list_users_online']);

		// Showing membergroups?
		if (!empty($settings['show_group_key']) && !empty($context['membergroups']))
			echo '
				<span class="membergroups">' . implode(',&nbsp;', $context['membergroups']). '</span>';
	}

	echo '
			</p>';
}

?>