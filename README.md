# Friends ACL

In Elgg the friends access collection is abstracted and access is determined by 
a relationship check at the point of database query.  Problems can arise when viewing
comments/replies and annotations that use this abstracted friends access id such that some
comments/replies may not be visible to some viewers that should have access.

This plugin does 3 simple tasks.

1. Creates a legitimate access collection for a users friends
2. Retroactively updates a users content to use the new access collection
3. Uses the new access collection in place of the default friends access id for new content


## Dependencies

Vroom - https://community.elgg.org/plugins/1222696


## Installation

Install Vroom

Unzip to the mod directory of your Elgg installation and activate through the admin plugins page

If the elgg installation has existing content, visit the plugin settings page to run the script to fix it


## Acknowledgments

Funding for this plugin has been provided by Connecting Conservation.