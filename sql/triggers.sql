DROP TRIGGER IF EXISTS `tbi_%1$s_addlevel`;
DROP TRIGGER IF EXISTS `tai_%1$s`;
DROP TRIGGER IF EXISTS `tbd_%1$s`;
DROP TRIGGER IF EXISTS `tbu_%1$s`;

-- Перед вставкой в структуру устанавливаем уровень
CREATE TRIGGER `tbi_%1$s_addlevel` BEFORE INSERT ON `%1$s` FOR EACH ROW
trigger_label:BEGIN
	DECLARE level_of_parent INT DEFAULT 1;
	IF @disable_triggers IS NULL THEN
		-- Если родитель указан - выбираем его уровень и плюс 1
		IF NEW.`pid` IS NOT NULL THEN
			SELECT `level` + 1 INTO level_of_parent
			FROM `%1$s`
			WHERE `id` = NEW.`pid`;
		END IF;
		-- Устанавливаем уровень в любом случае (1 если нет родителя)
		SET NEW.`level` = level_of_parent;
	END IF;
END;

-- После вставки в структуру добавляем связи
CREATE TRIGGER `tai_%1$s` AFTER INSERT ON `%1$s` FOR EACH ROW
trigger_label:BEGIN
	IF @disable_triggers IS NULL THEN
		-- Если вставляем элемент в корень, то связи не добавляем
		IF NEW.`pid` IS NULL THEN
			LEAVE trigger_label;
		END IF;
		-- Вставляем связи
		INSERT INTO `%2$s` (`aid`, `did`)
			-- Выбираем предков указанного родителя (did = idРодителя)
			-- и вставляем записи типа: idПредка, нашId
			SELECT `aid`, NEW.`id`
			FROM `%2$s`
		    WHERE `did` = NEW.`pid`
			-- Родитель тоже предок
		    UNION ALL
		    SELECT NEW.`pid`, NEW.`id`;
	END IF;
END;

-- Перед удалением из структуры удаляем связи
CREATE TRIGGER `tbd_%1$s` BEFORE DElETE ON `%1$s` FOR EACH ROW
BEGIN
	DECLARE count_childrens INT DEFAULT 0;

	-- Проверить существование наследников.
	SELECT count(`id`)
	INTO count_childrens
	FROM `%1$s`
	WHERE `pid` = OLD.`id`;

	IF count_childrens > 0 THEN
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Нельзя удалять вместе с детьми.';
	END IF;

	DElETE FROM `%2$s`
	WHERE `aid` = OLD.`id` OR `did` = OLD.`id`;
END;

-- Перед обновлением структуры обновляем связи
CREATE TRIGGER `tbu_%1$s` BEFORE UPDATE ON `%1$s` FOR EACH ROW
trigger_label:BEGIN
	DECLARE count_descendant INT DEFAULT 0;
	DECLARE delta_level INT DEFAULT 0;

	-- Если родитель изменился.
	IF NOT OLD.`pid` <=> NEW.`pid` THEN

		-- Нельзя перемещать в своих потомков
		SELECT count(*)
		INTO count_descendant
		FROM `%2$s` r
		WHERE
			r.`aid` = OLD.`id`
			AND r.`did` = NEW.`pid`;

		IF count_descendant > 0
		THEN
			SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Нельзя перемещать в своих потомков.';
		END IF;

		-- Удалить связи потомков перемещаемого элемента с предками перемещаемого элемента
		DElETE r2 FROM `%2$s` r1
		-- Присоединим всех предков найденных потомков (r2.aid)
		LEFT JOIN `%2$s` r2 ON r2.`did` = r1.`did`
		-- Присоединим только тех предков, которые являются предком для узла
		INNER JOIN `%2$s` r3 ON r3.`aid` = r2.`aid` and r3.`did` = OLD.`id`
		-- Условие нахождения потомков элемента (r1.did)
		WHERE r1.`aid` = OLD.`id`;

		-- Удаляем связи с перемещаемого элемента с предками.
		DElETE FROM `%2$s`
		WHERE `did` = OLD.`id`;

		IF NEW.`pid` IS NOT NULL THEN

		-- Вставляем связи между перемещаемым элементом с новыми предками
		INSERT INTO `%2$s` (`aid`, `did`)
			SELECT `aid`, OLD.`id`
			FROM `%2$s`
			WHERE `did` = NEW.`pid`
			UNION ALL
			SELECT NEW.`pid`, OLD.`id`;

		-- Вставляем связи между предками нового родителя и потомками перемещаемго элемента
		INSERT INTO `%2$s` (`aid`, `did`)
			SELECT r1.`aid`, r2.`did`
			FROM `%2$s` r1
			CROSS JOIN `%2$s` r2
			WHERE r1.`did` = NEW.`pid` AND r2.`aid` = OLD.`id`
			-- А так же связи между новым родителем перемещаемого элемента и его потомками
			UNION ALL
			SELECT NEW.`pid`, r1.`did`
			FROM `%2$s` r1
			WHERE r1.`aid` = OLD.`id`;

		-- Обновляем уровень перемещаемого элемента
		-- Определяем смещение по уровню
		SELECT CAST(`newparent`.`level` + 1 AS SIGNED) - CAST(`moveditem`.`level` AS SIGNED) INTO delta_level
		FROM `%1$s` `newparent`
		LEFT JOIN `%1$s` `moveditem` ON `moveditem`.`id` = OLD.`id`
		WHERE `newparent`.`id` = NEW.`pid`;

		IF delta_level <> 0
		THEN
			SET NEW.`level` = OLD.`level` + delta_level;
		END IF;
		ELSE
			SET NEW.`level` = 1;
		END IF;

	END IF;
END;
