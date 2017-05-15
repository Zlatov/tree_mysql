DROP PROCEDURE IF EXISTS `%1$s_update_level_moved_descendants`;
DROP PROCEDURE IF EXISTS `%1$s_order_after`;
DROP PROCEDURE IF EXISTS `%1$s_order_first`;
DROP PROCEDURE IF EXISTS `%1$s_reorder_cildrens`;
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

CREATE PROCEDURE `%1$s_order_after`(IN param_id INT(11), IN param_after_id INT(11))
procedure_label:BEGIN
	DECLARE param_pid INT unsigned DEFAULT NULL;

	UPDATE `%1$s` `moved_items`
	LEFT JOIN `%1$s` `after_item` ON `after_item`.`id` = param_after_id
	SET `moved_items`.`order` = `after_item`.`order` + 1
	WHERE
		`moved_items`.`id` = param_id;

	UPDATE `%1$s` `next_items`
	RIGHT JOIN `%1$s` `after_item` ON
		`next_items`.`pid` <=> `after_item`.`pid`
		AND `next_items`.`order` >= `after_item`.`order`
		AND if (`next_items`.`order` = `after_item`.`order`, `next_items`.`id` > param_after_id, 1)
		AND `next_items`.`id` <> param_id
	SET `next_items`.`order` = `next_items`.`order` + 2
	WHERE
		`after_item`.`id` = param_after_id;

	SELECT `%1$s`.`pid` INTO param_pid FROM `%1$s` WHERE `%1$s`.`id` = param_after_id;
	CALL %1$s_reorder_cildrens(param_pid);

END;

CREATE PROCEDURE `%1$s_order_first`(IN param_id INT(11), IN param_pid INT(11))
procedure_label:BEGIN
	UPDATE `%1$s` `moved_items`
	SET `moved_items`.`order` = 0
	WHERE
		`moved_items`.`id` = param_id;

	UPDATE `%1$s` `next_items`
	SET `next_items`.`order` = `next_items`.`order` + 1
	WHERE
		`next_items`.`pid` = param_pid
		AND `next_items`.`id` <> param_id;

	CALL %1$s_reorder_cildrens(param_pid);
END;

CREATE PROCEDURE `%1$s_reorder_cildrens`(IN param_pid INT(11))
procedure_label:BEGIN
	UPDATE `%1$s` `childrens`
	INNER JOIN (
		SELECT
			`ch`.`id` as `chid`,
			@rn := @rn + 1 as `row_number`
		FROM `%1$s` `ch`
		JOIN (SELECT @rn := 0) r
		WHERE `ch`.`pid` <=> param_pid
		ORDER BY `ch`.`order` ASC, `ch`.`id` ASC
	) `childrens_and_row_number` ON `childrens_and_row_number`.`chid` = `childrens`.`id`
	SET `childrens`.`order` = `childrens_and_row_number`.`row_number`;
END;
