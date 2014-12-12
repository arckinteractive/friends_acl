<?php

namespace Friends\ACL;
use ElggRelationship;

function user_login($e, $t, $user) {
	if ($user->friends_acl) {
		return true;
	}
	
	// no friends acl, lets create one
	$id = create_access_collection('friends_acl:' . $user->guid, elgg_get_site_entity()->guid);
	
	if ($id) {
		$user->friends_acl = $id;
		add_user_to_access_collection($user->guid, $user->friends_acl);
		
		if ($GLOBALS['shutdown_flag']) {
			// we're already shut down, lets update the acl
			update_acl($user);
		}
		else {
			defer_acl_update($user->guid);
		}
	}
	return true;
}


/**
 * called on shutdown, populates any acls associated with users
 */
function update_acls() {
	$guids = elgg_get_config('friends_acl_deferred_update');
	
	if (!is_array($guids)) {
		return true;
	}
	
	foreach ($guids as $guid => $foo) {
		$user = get_user($guid);
		if (!$user || !$user->friends_acl) {
			continue;
		}
		
		update_acl($user);
	}

	return true;
}


function user_add_friend($e, $t, $relationship) {
	if (!($relationship instanceof ElggRelationship)) {
		return true;
	}
	
	$user1 = get_user($relationship->guid_one);
	$user2 = get_user($relationship->guid_two);
	
	if (!$user1 || !$user2 || !$user1->friends_acl) {
		return true;
	}
	
	// seems legit, lets do it
	add_user_to_access_collection($user2->guid, $user1->friends_acl);
	
	return true;
}


function user_remove_friend($e, $t, $relationship) {
	if (!($relationship instanceof ElggRelationship)) {
		return true;
	}
	
	$user1 = get_user($relationship->guid_one);
	$user2 = get_user($relationship->guid_two);
	
	if (!$user1 || !$user2 || !$user1->friends_acl) {
		return true;
	}
	
	// seems legit, lets do it
	remove_user_from_access_collection($user2->guid, $user1->friends_acl);
	
	return true;
}