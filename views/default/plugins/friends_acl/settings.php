<?php

namespace Friends\ACL;

$dbprefix = elgg_get_config('dbprefix');
$md_id = add_metastring('friends_acl');

// get a count of any content that is ACCESS_FRIENDS that belongs to users that have a friends acl
$content_count = elgg_get_entities(array(
	'type' => ELGG_ENTITIES_ANY_VALUE,
	'subtype' => ELGG_ENTITIES_ANY_VALUE,
	'joins' => array(
		"JOIN {$dbprefix}metadata md ON md.entity_guid = e.owner_guid"
	),
	'wheres' => array(
		"e.access_id = " . ACCESS_FRIENDS . " AND md.name_id = {$md_id}"
	),
	'count' => true
));


$users_count = elgg_get_entities(array(
	'type' => 'user',
	'wheres' => "NOT EXISTS (SELECT 1 FROM {$dbprefix}metadata md WHERE md.entity_guid = e.guid AND md.name_id = {$md_id})",
	'count' => true
));

$title = elgg_echo('friends_acl:users:title');
if ($users_count) {
	$body = elgg_echo('friends_acl:users_count', array($users_count));
	
	$running_time = elgg_get_plugin_setting('friends_fix_time', PLUGIN_ID);
	if ((time() - $running_time) < 600){  // was it set less than 10 minutes ago?
		$body .= elgg_view('output/longtext', array(
			'value' => elgg_echo('friends_acl:fix_time', array(date('Y-M-j H:i:s', $running_time))),
			'class' => 'elgg-subtext pvm'
		));
	}
	else {
		$body .= '<div class="pvm">' . elgg_view('output/confirmlink', array(
				'href' => 'action/friends_acl/friends_fix',
				'text' => elgg_echo('friends_acl:action:friends_fix'),
				'class' => 'elgg-button elgg-button-action'
			)) . '</div>';
	}
}
else {
	$body = elgg_echo('friends_acl:users_count:none');
}

echo elgg_view_module('main', $title, $body);





$title = elgg_echo('friends_acl:content:title');
if ($content_count) {
	$body = elgg_echo('friends_acl:content_count', array($content_count));
	
	$running_time = elgg_get_plugin_setting('content_fix_time', PLUGIN_ID);
	if ((time() - $running_time) < 600){  // was it set less than 10 minutes ago?
		$body .= elgg_view('output/longtext', array(
			'value' => elgg_echo('friends_acl:content_fix_time', array(date('Y-M-j H:i:s', $running_time))),
			'class' => 'elgg-subtext pvm'
		));
	}
	else {
		$body .= '<div class="pvm">' . elgg_view('output/confirmlink', array(
				'href' => 'action/friends_acl/content_fix',
				'text' => elgg_echo('friends_acl:action:friends_fix'),
				'class' => 'elgg-button elgg-button-action'
			)) . '</div>';
	}
}
else {
	$body = elgg_echo('friends_acl:content_count:none');
}

echo elgg_view_module('main', $title, $body);
?>

<script>
	$(document).ready(function() {
		$('#friends_acl-settings .elgg-button-submit').hide();
	});
</script>