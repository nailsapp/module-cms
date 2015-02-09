ALTER TABLE `{{NAILS_DB_PREFIX}}cms_block` ADD `value` TEXT  NULL  AFTER `located`;
ALTER TABLE `{{NAILS_DB_PREFIX}}cms_block` CHANGE `title` `label` VARCHAR(150)  CHARACTER SET utf8  COLLATE utf8_general_ci  NOT NULL  DEFAULT '';
ALTER TABLE `{{NAILS_DB_PREFIX}}cms_block` MODIFY COLUMN `created_by` INT(11) UNSIGNED DEFAULT NULL AFTER `created`;
UPDATE `{{NAILS_DB_PREFIX}}cms_block` cb SET `value` = (SELECT `cbt`.`value` FROM `nails_cms_block_translation` cbt WHERE `cbt`.`block_id` = `cb`.`id` AND `cbt`.`language` = 'english');
DROP TABLE `{{NAILS_DB_PREFIX}}cms_block_translation_revision`;
DROP TABLE `{{NAILS_DB_PREFIX}}cms_block_translation`;
ALTER TABLE `{{NAILS_DB_PREFIX}}cms_slider_item` CHANGE `object_id` `object_id` INT(11)  UNSIGNED  NULL;
ALTER TABLE `{{NAILS_DB_PREFIX}}cms_slider_item` DROP FOREIGN KEY `{{NAILS_DB_PREFIX}}cms_slider_item_ibfk_7`;
ALTER TABLE `{{NAILS_DB_PREFIX}}cms_slider_item` DROP `page_id`;
ALTER TABLE `{{NAILS_DB_PREFIX}}cms_slider_item` DROP `title`;