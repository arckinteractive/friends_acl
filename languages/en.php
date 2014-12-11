<?php

$english = array(
	'friends_acl:users_count' => "There are %s users on the system that don't have a friends access collection.  You can run the fix script by clicking the below button, please be aware that this may take a <strong>very long time</strong> to complete if you have a large number of users with a lot of content marked for friends access",
	'friends_acl:users_count:none' => "Congratulations, all users have a friends access collections",
	'friends_acl:action:friends_fix' => "Fix this now",
	'friends_acl:fix_time' => 'The fix script is currently running, last logged action: %s - please check back later',
	'friends_acl:users:title' => "User Check",
	'friends_acl:content:title' => "Content Check",
	'friends_acl:content_count' => "There are %s content entities on the system that have the default friends access that should have the upgraded access.  You can run the fix script by clicking hte button below, please be aware that this may take a <strong>very long time</strong> to complete if this is a very large number",
	'friends_acl:content_fix_time' => "The fix script is currently running, last logged action: %s - please check back later",
	'friends_acl:content_count:none' => "Congratulations, all friends access is up to date",
	'friends_acl:content_fix:started' => "The content fix script has been started",
);

add_translation("en", $english);