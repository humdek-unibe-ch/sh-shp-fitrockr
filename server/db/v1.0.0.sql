-- add plugin entry in the plugin table
INSERT IGNORE INTO plugins (name, version) 
VALUES ('fitrockr', 'v1.0.0');

-- add page type global_values
INSERT IGNORE INTO `pageType` (`name`) VALUES ('sh_module_fitrockr');

SET @id_page_modules = (SELECT id FROM pages WHERE keyword = 'sh_modules');
-- add translation page
INSERT IGNORE INTO `pages` (`id`, `keyword`, `url`, `protocol`, `id_actions`, `id_navigation_section`, `parent`, `is_headless`, `nav_position`, `footer_position`, `id_type`, `id_pageAccessTypes`) 
VALUES (NULL, 'sh_module_fitrockr', '/admin/module_fitrockr', 'GET|POST', (SELECT id FROM actions WHERE `name` = 'backend' LIMIT 0,1), NULL, @id_page_modules, 0, 100, NULL, (SELECT id FROM pageType WHERE `name` = 'sh_global_fitrockr' LIMIT 0,1), (SELECT id FROM lookups WHERE lookup_code = 'mobile_and_web'));
SET @id_page_values = (SELECT id FROM pages WHERE keyword = 'sh_module_fitrockr');
INSERT IGNORE INTO `acl_groups` (`id_groups`, `id_pages`, `acl_select`, `acl_insert`, `acl_update`, `acl_delete`) VALUES ('0000000001', @id_page_values, '1', '0', '1', '0');

-- add new filed `fitrockr_api_key` from type JSON
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'fitrockr_api_key', get_field_type_id('text'), '0');
-- add new filed `fitrockr_api_tenant` from type JSON
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'fitrockr_api_tenant', get_field_type_id('text'), '0');
-- add new filed `fitrockr_create_user` from type JSON
INSERT IGNORE INTO `fields` (`id`, `name`, `id_type`, `display`) VALUES (NULL, 'fitrockr_create_user', get_field_type_id('checkbox'), '0');

INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_fitrockr' LIMIT 0,1), get_field_id('fitrockr_api_key'), NULL, 'Fitrockr API key');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_fitrockr' LIMIT 0,1), get_field_id('fitrockr_api_tenant'), NULL, 'Fitrockr API tenant');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_fitrockr' LIMIT 0,1), get_field_id('fitrockr_create_user'), NULL, 'If selected, it will create automatically a user in Fitrockr platform once the user account is validated');
INSERT IGNORE INTO `pageType_fields` (`id_pageType`, `id_fields`, `default_value`, `help`) VALUES ((SELECT id FROM pageType WHERE `name` = 'sh_module_fitrockr' LIMIT 0,1), get_field_id('title'), NULL, 'Page title');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('title'), '0000000001', 'Module Fitrockr');
INSERT IGNORE INTO `pages_fields_translation` (`id_pages`, `id_fields`, `id_languages`, `content`) VALUES (@id_page_values, get_field_id('title'), '0000000002', 'Module Fitrockr');

--
-- Table structure for table `users_fitrockr`
--
CREATE TABLE IF NOT EXISTS `users_fitrockr` (
  `id_users` INT(10) UNSIGNED ZEROFILL NOT NULL,
  `id_fitrockr` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`id_users`),
  CONSTRAINT `users_fitrockrs_fk_id_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- register hook  for view fitrockr id
INSERT IGNORE INTO `hooks` (`id_hookTypes`, `name`, `description`, `class`, `function`, `exec_class`, `exec_function`) VALUES ((SELECT id FROM lookups WHERE lookup_code = 'hook_on_function_execute' LIMIT 0,1), 'fitrockr-user-view', 'Output view mode Fitrockr user', 'UserSelectView', 'output_user_blocking', 'FitrockrHooks', 'output_view_fitrockr_user');

-- register hook  for edit fitrockr id
INSERT IGNORE INTO `hooks` (`id_hookTypes`, `name`, `description`, `class`, `function`, `exec_class`, `exec_function`) VALUES ((SELECT id FROM lookups WHERE lookup_code = 'hook_overwrite_return' LIMIT 0,1), 'fitrockr-user-edit', 'Output edit mode for Fitrockr user', 'UserUpdateView', 'output_content', 'FitrockrHooks', 'output_edit_fitrockr_user');

-- register hook  for has access to edit fitrockr user
INSERT IGNORE INTO `hooks` (`id_hookTypes`, `name`, `description`, `class`, `function`, `exec_class`, `exec_function`) VALUES ((SELECT id FROM lookups WHERE lookup_code = 'hook_overwrite_return' LIMIT 0,1), 'fitrockr-user-edit-has-access', 'Check for access to edit fitrockr user', 'UserUpdateComponent', 'has_access', 'FitrockrHooks', 'has_access');

-- register hook  for creating fitrockr user automatically once the account is validated
INSERT IGNORE INTO `hooks` (`id_hookTypes`, `name`, `description`, `class`, `function`, `exec_class`, `exec_function`) VALUES ((SELECT id FROM lookups WHERE lookup_code = 'hook_overwrite_return' LIMIT 0,1), 'fitrockr-user-create', 'Create Fitrockr user on successful validation', 'ValidateModel', 'activate_user', 'FitrockrHooks', 'create_fitrockr_user');

-- register hook  for cleaning fitrockr user data
INSERT IGNORE INTO `hooks` (`id_hookTypes`, `name`, `description`, `class`, `function`, `exec_class`, `exec_function`) VALUES ((SELECT id FROM lookups WHERE lookup_code = 'hook_overwrite_return' LIMIT 0,1), 'clear-fitrockr-user-data', 'Clear Fitrockr user data', 'UserModel', 'clean_user_data', 'FitrockrHooks', 'clear_fitrockr_user_data');