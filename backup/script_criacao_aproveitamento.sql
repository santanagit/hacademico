CREATE TABLE IF NOT EXISTS `bd_hacademico`.`aluno` (
  `id_aluno` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `id_curso` INT NOT NULL,
  PRIMARY KEY (`id_aluno`),
  INDEX `fk_aluno_usuario1_idx` (`id_usuario`),
  INDEX `fk_aluno_curso1_idx` (`id_curso`),
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
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `bd_hacademico`.`aproveitamento_solicitacao` (
  `id_aproveitamento_solicitacao` INT NOT NULL AUTO_INCREMENT,
  `id_aluno` INT NOT NULL,
  `id_grade` INT NOT NULL,
  PRIMARY KEY (`id_aproveitamento_solicitacao`),
  INDEX `fk_aproveitamento_solicitacao_aluno1_idx` (`id_aluno`),
  INDEX `fk_aproveitamento_solicitacao_grade1_idx` (`id_grade`),
  CONSTRAINT `fk_aproveitamento_solicitacao_aluno1`
    FOREIGN KEY (`id_aluno`)
    REFERENCES `bd_hacademico`.`aluno` (`id_aluno`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
  CONSTRAINT `fk_aproveitamento_solicitacao_grade1`
    FOREIGN KEY (`id_grade`)
    REFERENCES `bd_hacademico`.`grade` (`id_grade`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `bd_hacademico`.`aproveitamento_disciplina` (
  `id_aproveitamento_disciplina` INT NOT NULL AUTO_INCREMENT,
  `id_aproveitamento_solicitacao` INT NOT NULL,
  `instituicao` VARCHAR(100) NOT NULL,
  `curso` VARCHAR(100) NOT NULL,
  `nivel` ENUM('FIC', 'Técnico', 'Graduação', 'Especialização', 'Mestrado Acadêmico', 'Mestrado Profissional', 'Doutorado', 'Pós-Doutorado') NOT NULL,
  `disciplina` VARCHAR(100) NOT NULL,
  `cht` FLOAT NOT NULL,
  `ementa` VARCHAR(100) NOT NULL,
  `historico` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_aproveitamento_disciplina`),
  INDEX `fk_table1_aproveitamento_solicitacao1_idx` (`id_aproveitamento_solicitacao`),
  CONSTRAINT `fk_table1_aproveitamento_solicitacao1`
    FOREIGN KEY (`id_aproveitamento_solicitacao`)
    REFERENCES `bd_hacademico`.`aproveitamento_solicitacao` (`id_aproveitamento_solicitacao`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `bd_hacademico`.`aproveitamento_tramitacao` (
  `id_aproveitamento_tramitacao` INT NOT NULL AUTO_INCREMENT,
  `id_aproveitamento_solicitacao` INT NOT NULL,
  `id_usuario` INT NOT NULL,
  `situacao` ENUM('TRIAGEM', 'EM ANÁLISE', 'APROVADO', 'REPROVADO', 'RECURSO', 'CANCELADO') NOT NULL,
  `observacao` TEXT NULL,
  `data_tramitacao` DATETIME NOT NULL,
  PRIMARY KEY (`id_aproveitamento_tramitacao`),
  INDEX `fk_aproveitamento_tramitacao_usuario1_idx` (`id_usuario`),
  INDEX `fk_aproveitamento_tramitacao_aproveitamento_solicitacao1_idx` (`id_aproveitamento_solicitacao`),
  CONSTRAINT `fk_aproveitamento_tramitacao_usuario1`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `bd_hacademico`.`usuario` (`id_usuario`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
  CONSTRAINT `fk_aproveitamento_tramitacao_aproveitamento_solicitacao1`
    FOREIGN KEY (`id_aproveitamento_solicitacao`)
    REFERENCES `bd_hacademico`.`aproveitamento_solicitacao` (`id_aproveitamento_solicitacao`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE)
ENGINE = InnoDB;
