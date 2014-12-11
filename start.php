<?php

namespace Friends\ACL;

const PLUGIN_ID = 'friends_acl';
const UPGRADE_VERSION = 20141209;

require_once __DIR__ . '/lib/hooks.php';
require_once __DIR__ . '/lib/events.php';
require_once __DIR__ . '/lib/functions.php';

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');

function init() {
	elgg_register_event_handler('login', 'user', __NAMESPACE__ . '\\user_login');
	
	elgg_register_event_handler('create', 'friend', __NAMESPACE__ . '\\user_add_friend', 9999);
	elgg_register_event_handler('delete', 'friend', __NAMESPACE__ . '\\user_remove_friend', 9999);
	
	elgg_register_plugin_hook_handler('access:collections:write', 'user', __NAMESPACE__ . '\\write_access');
	
	
	elgg_register_action('friends_acl/content_fix', __DIR__ . '/actions/content_fix.php', 'admin');
	elgg_register_action('friends_acl/friends_fix', __DIR__ . '/actions/friends_fix.php', 'admin');
}
