-- add plugin entry in the plugin table
INSERT IGNORE INTO plugins (name, version) 
VALUES ('fitrockr', 'v1.0.0');

-- add page type global_values
INSERT IGNORE INTO `pageType` (`name`) VALUES ('sh_global_fitrockr');

SET @id_page_globals = (SELECT id FROM pages WHERE keyword = 'sh_globals');
-- add translation page
INSERT IGNORE INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'sh_global_fitrockr', '/admin/global_fitrockr', 'GET|POST', (SELECT id FROM actions WHERE `name` = 'backend' LIMIT 0,1), NULL, @id_page_globals, '0', 0, NULL, (SELECT id FROM pageType WHERE `name` = 'sh_global_fitrockr' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'));
SET @id_page_values = (SELECT id FROM pages WHERE keyword = 'sh_global_fitrockr');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_values, '1', '0', '1', '0');

-- add new filed `fitrockr_api_key` from type JSON
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'fitrockr_api_key', get_field_type_id('text'), '0');
-- add new filed `fitrockr_api_tenant` from type JSON
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'fitrockr_api_tenant', get_field_type_id('text'), '0');

INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_global_fitrockr' LIMIT 0,1), get_field_id('fitrockr_api_key'), NULL, 'Fitrockr API key');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_global_fitrockr' LIMIT 0,1), get_field_id('fitrockr_api_tenant'), NULL, 'Fitrockr API tenant');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_global_fitrockr' LIMIT 0,1), get_field_id('title'), NULL, 'Page title');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('title'), '0000000001', 'Fitrockr');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('title'), '0000000002', 'Fitrockr');
