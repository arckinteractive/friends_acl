<?php

namespace Friends\ACL;

use ElggBatch;

/**
 * registers the user guid for updating the acl after shutdown
 * thanks to vrrroooooooom
 */
function defer_acl_update($guid) {
	$user_guids = elgg_get_config('friends_acl_deferred_update');

	if (!is_array($user_guids)) {
		$user_guids = array();
	}

	$user_guids[$guid] = 1; // use key to store, keeps it unique

	elgg_set_config('friends_acl_deferred_update', $user_guids);

	// register our shutdown handler
	// unregister first to prevent multiple registrations
	elgg_unregister_event_handler('shutdown', 'system', __NAMESPACE__ . '\\update_acls');
	elgg_register_event_handler('shutdown', 'system', __NAMESPACE__ . '\\update_acls');
}

function update_content_access($user) {
	if (!$user || !$user->friends_acl) {
		return false;
	}

	$options = array(
		'owner_guid' => $user->guid,
		'access_id' => ACCESS_FRIENDS,
		'limit' => false
	);

	// workaround for https://github.com/ColdTrick/file_tools/issues/49
	elgg_unregister_event_handler("update", "object", "file_tools_object_handler");
	elgg_unregister_event_handler("update", "object", "bookmark_tools_object_handler");
	
	$batch = new ElggBatch('elgg_get_entities_from_access_id', $options);
	foreach ($batch as $e) {
		$e->access_id = $user->friends_acl;
		$e->save();
	}

	return true;
}

function update_acl($user) {
	$ia = elgg_set_ignore_access(true);

	// empty the access collection just in case
	$dbprefix = elgg_get_config('dbprefix');
	$sql = "DELETE FROM {$dbprefix}access_collection_membership WHERE access_collection_id = {$user->friends_acl}";
	delete_data($sql);

	// get friends and add them to the acl
	$options = array(
		'type' => 'user',
		'relationship' => 'friend',
		'relationship_guid' => $user->guid,
		'limit' => false,
		'callback' => false // keep it light and quick, we don't need the entities
	);

	$batch = new ElggBatch('elgg_get_entities_from_relationship', $options, null, 100);
	
	// add the user
	add_user_to_access_collection($user->guid, $user->friends_acl);

	foreach ($batch as $f) {
		add_user_to_access_collection($f->guid, $user->friends_acl);
	}

	// see if there's any content we need to update
	update_content_access($user);

	elgg_set_ignore_access($ia);
}

// note, we're looping a batch so that events/hooks trigger on the entity save to update access
// to anything related that needs to stay in sync
function _fix_content_access() {
	$ia = elgg_set_ignore_access(true);
	$dbprefix = elgg_get_config('dbprefix');
	$md_id = add_metastring('friends_acl');

	$options = array(
		'type' => ELGG_ENTITIES_ANY_VALUE,
		'subtype' => ELGG_ENTITIES_ANY_VALUE,
		'joins' => array(
			"JOIN {$dbprefix}metadata md ON md.entity_guid = e.owner_guid"
		),
		'wheres' => array(
			"e.access_id = " . ACCESS_FRIENDS . " AND md.name_id = {$md_id}"
		),
		'limit' => false
	);

	$batch = new ElggBatch('elgg_get_entities', $options, null, 25, false);

	elgg_set_plugin_setting('content_fix_time', time(), PLUGIN_ID);
	
	// workaround for https://github.com/ColdTrick/file_tools/issues/49
	elgg_unregister_event_handler("update", "object", "file_tools_object_handler");
	elgg_unregister_event_handler("update", "object", "bookmark_tools_object_handler");
	

	$count = 0;
	foreach ($batch as $e) {
		$owner = get_user($e->owner_guid);
		if (!$owner || !$owner->friends_acl) {
			continue;
		}

		$e->access_id = $owner->friends_acl;
		$e->save();

		$count++;
		if (!($count % 200)) {
			elgg_set_plugin_setting('content_fix_time', time(), PLUGIN_ID); // update the action time every 200 entities
		}
	}

	elgg_set_plugin_setting('content_fix_time', 0, PLUGIN_ID);
	elgg_set_ignore_access($ia);
}

function _fix_friend_access() {
	$ia = elgg_set_ignore_access(true);
	$dbprefix = elgg_get_config('dbprefix');
	
	$md_id = add_metastring('friends_acl');
	$options = array(
		'type' => 'user',
		'wheres' => "NOT EXISTS (SELECT 1 FROM {$dbprefix}metadata md WHERE md.entity_guid = e.guid AND md.name_id = {$md_id})",
		'limit' => false
	);

	$batch = new ElggBatch('elgg_get_entities', $options, null, 25, false);
	
	elgg_set_plugin_setting('friends_fix_time', time(), PLUGIN_ID);

	$count = 0;
	foreach ($batch as $user) {
		
		// simulate our login event which will create the acl and populate it and 
		user_login(null, null, $user);
		
		$count++;
		if (!($count % 100)) {
			elgg_set_plugin_setting('friends_fix_time', time(), PLUGIN_ID); // update the action time every 200 entities
		}
	}
	
	elgg_set_plugin_setting('friends_fix_time', 0, PLUGIN_ID);
	elgg_set_ignore_access($ia);
}
