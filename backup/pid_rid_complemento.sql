-- MySQL Workbench Synchronization
-- Generated: 2023-07-30 11:14
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: santana

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

ALTER SCHEMA `bd_hacademico`  DEFAULT CHARACTER SET utf8  DEFAULT COLLATE utf8_general_ci ;

CREATE TABLE IF NOT EXISTS `bd_hacademico`.`atividade_docente` (
  `id_atividade_docente` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pid` INT(11) NOT NULL,
  `id_atividade` INT(11) NOT NULL,
  `descricao` TEXT NULL DEFAULT NULL,
  `horas_planejadas` FLOAT(11) NOT NULL,
  `horas_executadas` FLOAT(11) NULL DEFAULT NULL,
  `id_comprovante` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id_atividade_docente`),
  INDEX `fk_pid_atividade_docente1_idx` (`id_atividade`),
  INDEX `fk_atividade_docente_comprovante1_idx` (`id_comprovante`),
  INDEX `fk_atividade_docente_pid_rid1_idx` (`id_pid`),
  CONSTRAINT `fk_pid_atividade_docente1`
    FOREIGN KEY (`id_atividade`)
    REFERENCES `bd_hacademico`.`atividade` (`id_atividade`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
  CONSTRAINT `fk_atividade_docente_comprovante1`
    FOREIGN KEY (`id_comprovante`)
    REFERENCES `bd_hacademico`.`comprovante` (`id_comprovante`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `fk_atividade_docente_pid_rid1`
    FOREIGN KEY (`id_pid`)
    REFERENCES `bd_hacademico`.`pid` (`id_pid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `bd_hacademico`.`comprovante` (
  `id_comprovante` INT(11) NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(200) NOT NULL,
  `inicio_vigencia` DATE NULL DEFAULT NULL,
  `fim_vigencia` DATE NULL DEFAULT NULL,
  PRIMARY KEY (`id_comprovante`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `bd_hacademico`.`historico_atividade` (
  `id_historico_atividade` INT(11) NOT NULL AUTO_INCREMENT,
  `id_atividade_docente` INT(11) NOT NULL,
  `etapa` ENUM('PID', 'RID') NOT NULL,
  `situacao` ENUM('AGUARDANDO AVALIAÇÃO', 'APROVADA', 'REPROVADA', 'CANCELADA', 'NÃO EXECUTADA') NOT NULL,
  `observacao` TEXT NULL DEFAULT NULL,
  `data_situacao` DATETIME NOT NULL,
  `id_usuario_avaliador` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id_historico_atividade`),
  INDEX `fk_historico_atividade_atividade_docente1_idx` (`id_atividade_docente`),
  INDEX `fk_historico_atividade_usuario1_idx` (`id_usuario_avaliador`),
  CONSTRAINT `fk_historico_atividade_atividade_docente1`
    FOREIGN KEY (`id_atividade_docente`)
    REFERENCES `bd_hacademico`.`atividade_docente` (`id_atividade_docente`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_historico_atividade_usuario1`
    FOREIGN KEY (`id_usuario_avaliador`)
    REFERENCES `bd_hacademico`.`usuario` (`id_usuario`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `bd_hacademico`.`pid` (
  `id_pid` INT(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` INT(11) NOT NULL,
  `id_periodo` INT(11) NOT NULL,
  `pid_correcao_inicio` DATE NULL DEFAULT NULL,
  `pid_correcao_fim` DATE NULL DEFAULT NULL,
  `rid_correcao_inicio` DATE NULL DEFAULT NULL,
  `rid_correcao_fim` DATE NULL DEFAULT NULL,
  PRIMARY KEY (`id_pid`),
  INDEX `fk_pid_rid_usuario1_idx` (`id_usuario`),
  INDEX `fk_pid_rid_periodo1_idx` (`id_periodo`),
  CONSTRAINT `fk_pid_rid_usuario1`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `bd_hacademico`.`usuario` (`id_usuario`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pid_rid_periodo1`
    FOREIGN KEY (`id_periodo`)
    REFERENCES `bd_hacademico`.`periodo` (`id_periodo`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `bd_hacademico`.`historico_pid` (
  `id_historico_pid` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pid` INT(11) NOT NULL,
  `etapa` ENUM('PID', 'RID') NOT NULL DEFAULT 'PID',
  `situacao` ENUM('AGUARDANDO ENVIO', 'ENVIADO', 'APROVADO', 'REPROVADO', 'RETORNADO PARA CORREÇÃO') NOT NULL,
  `data_situacao` DATETIME NOT NULL,
  PRIMARY KEY (`id_historico_pid`),
  INDEX `fk_historico_pid_pid1_idx` (`id_pid`),
  CONSTRAINT `fk_historico_pid_pid1`
    FOREIGN KEY (`id_pid`)
    REFERENCES `bd_hacademico`.`pid` (`id_pid`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `bd_hacademico`.`comprovante_docente` (
  `id_comprovante_docente` INT(11) NOT NULL AUTO_INCREMENT,
  `id_comprovante` INT(11) NOT NULL,
  `id_usuario` INT(11) NOT NULL,
  `id_atividade` INT(11) NOT NULL,
  `horas` FLOAT(11) NOT NULL,
  PRIMARY KEY (`id_comprovante_docente`),
  INDEX `fk_comprovante_docente_comprovante_atividade1_idx` (`id_comprovante`),
  INDEX `fk_comprovante_docente_usuario1_idx` (`id_usuario`),
  INDEX `fk_comprovante_docente_atividade1_idx` (`id_atividade`),
  CONSTRAINT `fk_comprovante_docente_comprovante_atividade1`
    FOREIGN KEY (`id_comprovante`)
    REFERENCES `bd_hacademico`.`comprovante` (`id_comprovante`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_comprovante_docente_usuario1`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `bd_hacademico`.`usuario` (`id_usuario`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_comprovante_docente_atividade1`
    FOREIGN KEY (`id_atividade`)
    REFERENCES `bd_hacademico`.`atividade` (`id_atividade`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
