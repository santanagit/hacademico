CREATE TABLE IF NOT EXISTS `bd_hacademico`.`aluno` (
  `id_aluno` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `id_curso` INT NOT NULL,
  PRIMARY KEY (`id_aluno`),
  INDEX `fk_aluno_usuario1_idx` (`id_usuario` ASC),
  INDEX `fk_aluno_curso1_idx` (`id_curso` ASC),
  CONSTRAINT `fk_aluno_usuario1`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `bd_hacademico`.`usuario` (`id_usuario`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
  CONSTRAINT `fk_aluno_curso1`
    FOREIGN KEY (`id_curso`)
    REFERENCES `bd_hacademico`.`curso` (`id_curso`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE)
ENGINE = INNODB