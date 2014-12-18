<?php

namespace Friends\ACL;


/**
 * called on the get_write_access_array hook, defines the write access the user can save content with
 * we will be replacing ACCESS_FRIENDS with our users friend collection ID
 * 
 * @param type $h
 * @param type $t
 * @param type $r
 * @param type $p
 */
function write_access($h, $t, $r, $p) {
	$user = get_user($p['user_id']);
	
	if (!$user || !$user->friends_acl) {
		return $r;
	}

	$page_owner = elgg_get_page_owner_entity();
	if (elgg_instanceof($page_owner, 'group')) {
		if ($page_owner->canWriteToContainer($user->guid)) {
			// don't want to change in group context
			// as ACCESS_FRIENDS is removed later
			return $r;
		}
	}

	if ($r[ACCESS_FRIENDS]) {
		
		/**
		 * This little bit of gibberish switches the ACCESS_FRIENDS key with our $user->friends_acl
		 * but importantly it retains the order, otherwise the friends option gets shuffled
		 * to the bottom of the stack if we just set the new one and unset the old one
		 */
		$keys = array_keys($r);
		if (false === $index = array_search(ACCESS_FRIENDS, $keys)) {
			return $r; // this shouldn't be able to happen
		}
		$keys[$index] = $user->friends_acl;
		$r = array_combine($keys, array_values($r));
	}
	
	return $r;
}
