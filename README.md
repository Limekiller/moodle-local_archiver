# moodle-local_archiver

Automatically archive Moodle courses -- meaning move course backups to some external location and then delete the courses.

At the moment, the plugin allows a user to select a category for backup to an arbitrary SFTP server; the plugin then creates an ad-hoc task that creates the .mbz files for each course in the category, zips them all together, and uploads them to the specified server. The plugin also contains a page showing a log of previous and in-progress jobs.

Going forward, we want to add
- More backup destination options, such as Google Drive
