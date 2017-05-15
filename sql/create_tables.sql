DROP TABLE IF EXISTS `%2$s`;
DROP TABLE IF EXISTS `%1$s`;

CREATE TABLE IF NOT EXISTS `%1$s` (
  `id` INT unsigned NOT NULL AUTO_INCREMENT,
  -- pid - Parent ID
  `pid` INT unsigned NULL DEFAULT NULL,
  `level` INT unsigned NOT NULL,
  `header` VARCHAR(180) NOT NULL,
  `order` INT unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_%1$s_header` (`header` ASC)
)
ENGINE InnoDB
AUTO_INCREMENT 1
DEFAULT CHARACTER SET utf8
COLLATE utf8_general_ci;

ALTER TABLE `%1$s`
  ADD CONSTRAINT `fk_%1$s_pid` FOREIGN KEY (`pid`) REFERENCES `%1$s` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

CREATE TABLE IF NOT EXISTS `%2$s` (
  -- aid - Ancestor ID
  `aid` INT unsigned NOT NULL,
  -- did - Descendant ID
  `did` INT unsigned NOT NULL,
  UNIQUE KEY `uq_%2$s_adid` (`aid` ASC, `did` ASC),
  CONSTRAINT `fk_%2$s_aid` FOREIGN KEY (`aid`) REFERENCES `%1$s` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_%2$s_did` FOREIGN KEY (`did`) REFERENCES `%1$s` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
)
ENGINE InnoDB
DEFAULT CHARACTER SET utf8
COLLATE utf8_general_ci;
