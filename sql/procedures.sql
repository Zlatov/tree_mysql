DROP PROCEDURE IF EXISTS `%1$s_update_level_moved_descendants`;
-- DELIMITER ;;

CREATE PROCEDURE `%1$s_update_level_moved_descendants`(IN param_id INT(11))
procedure_label:BEGIN
	DECLARE count_descendant INT DEFAULT 0;
	DECLARE delta_level INT DEFAULT 0;

	-- Определяем есть ли потомки у перемещвемого элемента
	SELECT COUNT(`id`) INTO count_descendant FROM `%1$s` WHERE `pid` = param_id;

	-- Если есть потомки
	IF count_descendant > 0
	THEN
		-- Определяем на сколько неверно смещение уровня у потомов относительнопредка
		SELECT CAST(`moved_element`.`level` AS SIGNED) + 1 - CAST(`children`.`level` AS SIGNED) INTO delta_level
		FROM `%1$s` `moved_element`
		LEFT JOIN `%1$s` `children` ON `children`.`pid` = `moved_element`.`id`
		WHERE `moved_element`.`id` = param_id
		LIMIT 1;

		-- Если есть неверное смещение
		IF delta_level <> 0
		THEN
			-- Выбираем потомков и добавляем delta_level
			UPDATE `%1$s` `descendants`
			INNER JOIN `%2$s` `relations` ON `relations`.`did` = `descendants`.`id`
			SET `level` = `level` + delta_level
			WHERE `relations`.`aid` = param_id;
		END IF;
	END IF;

END;
