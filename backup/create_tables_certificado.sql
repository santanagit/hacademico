-- -----------------------------------------------------
-- Table `bd_hacademico`.`tipo_certificado`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_hacademico`.`tipo_certificado` (
  `id_tipo_certificado` INT NOT NULL AUTO_INCREMENT,
  `descricao` ENUM('FIC', 'Evento', 'Curso de Extensão') NOT NULL,
  `texto_padrao` TEXT NOT NULL,
  `fundo_padrao` VARCHAR(100) NULL,
  PRIMARY KEY (`id_tipo_certificado`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `bd_hacademico`.`certificado`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_hacademico`.`certificado` (
  `id_certificado` INT NOT NULL AUTO_INCREMENT,
  `id_tipo_certificado` INT NOT NULL,
  `id_periodo` INT NOT NULL,
  `descricao` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_certificado`),
  INDEX `fk_certificado_tipo_certificado1_idx` (`id_tipo_certificado`),
  INDEX `fk_certificado_periodo1_idx` (`id_periodo`),
  CONSTRAINT `fk_certificado_tipo_certificado1`
    FOREIGN KEY (`id_tipo_certificado`)
    REFERENCES `bd_hacademico`.`tipo_certificado` (`id_tipo_certificado`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
  CONSTRAINT `fk_certificado_periodo1`
    FOREIGN KEY (`id_periodo`)
    REFERENCES `bd_hacademico`.`periodo` (`id_periodo`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `bd_hacademico`.`certificado_atividades`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_hacademico`.`certificado_atividades` (
  `id_certificado_atividades` INT NOT NULL AUTO_INCREMENT,
  `id_certificado` INT NOT NULL,
  `descricao` VARCHAR(200) NOT NULL,
  `ch` FLOAT NOT NULL,
  PRIMARY KEY (`id_certificado_atividades`),
  INDEX `fk_certificado_atividades_certificado1_idx` (`id_certificado`),
  CONSTRAINT `fk_certificado_atividades_certificado1`
    FOREIGN KEY (`id_certificado`)
    REFERENCES `bd_hacademico`.`certificado` (`id_certificado`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `bd_hacademico`.`certificado_usuario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_hacademico`.`certificado_usuario` (
  `id_certificado_usuario` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `id_certificado_atividades` INT NOT NULL,
  PRIMARY KEY (`id_certificado_usuario`),
  INDEX `fk_certificado_detalhes_usuario1_idx` (`id_usuario`),
  INDEX `fk_certificado_usuario_certificado_atividades1_idx` (`id_certificado_atividades`),
  CONSTRAINT `fk_certificado_detalhes_usuario1`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `bd_hacademico`.`usuario` (`id_usuario`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
  CONSTRAINT `fk_certificado_usuario_certificado_atividades1`
    FOREIGN KEY (`id_certificado_atividades`)
    REFERENCES `bd_hacademico`.`certificado_atividades` (`id_certificado_atividades`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE)
ENGINE = InnoDB;
