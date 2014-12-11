<?php

namespace Friends\ACL;

elgg_register_event_handler('shutdown', 'system', __NAMESPACE__ . '\\_fix_content_access');

system_message(elgg_echo('friends_acl:content_fix:started'));
forward(REFERER);
