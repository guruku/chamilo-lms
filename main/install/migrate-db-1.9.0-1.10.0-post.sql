-- This script updates the databases structure after migrating the data from
-- version 1.9.0 (or version 1.9.*) to version 1.10.0
-- it is intended as a standalone script, however, because of the multiple
-- databases related difficulties, it should be parsed by a PHP script in
-- order to connect to and update the right databases.
-- There is one line per query, allowing the PHP function file() to read
-- all lines separately into an array. The xxMAINxx-type markers are there
-- to tell the PHP script which database we're talking about.
-- By always using the keyword "TABLE" in the queries, we should be able
-- to retrieve and modify the table name from the PHP script if needed, which
-- will allow us to deal with the unique-database-type installations
--
-- This first part is for the main database

-- xxMAINxx

ALTER TABLE track_e_access DROP COLUMN access_cours_code;
ALTER TABLE track_e_default DROP COLUMN default_cours_code;
ALTER TABLE track_e_lastaccess DROP COLUMN access_cours_code;
ALTER TABLE track_e_exercices DROP COLUMN exe_cours_id;
ALTER TABLE track_e_downloads DROP COLUMN down_cours_id;
ALTER TABLE track_e_hotpotatoes DROP COLUMN exe_cours_id;
ALTER TABLE track_e_links DROP COLUMN links_cours_id;
ALTER TABLE track_e_course_access DROP COLUMN course_code;
ALTER TABLE track_e_online DROP COLUMN course;
ALTER TABLE track_e_attempt DROP COLUMN course_code;

-- xxCOURSExx


