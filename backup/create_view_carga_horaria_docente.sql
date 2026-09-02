USE `u112356623_hacademico`;
DROP VIEW carga_horaria_docente;
CREATE VIEW carga_horaria_docente AS
select 
	`turma`.`id_periodo` AS `id_periodo`,
    `oferta_disciplina`.`id_usuario` AS `id_usuario`,
    `usuario`.`nome` AS `nome`,sum(`oferta_disciplina`.`chs`) AS `chs`,
    sum(`oferta_disciplina`.`chs_ead`) AS `chs_ead`,
    curso.modulo
from 
	`oferta_disciplina` inner join `usuario` 
		on `oferta_disciplina`.`id_usuario` = `usuario`.`id_usuario`
	inner join `disciplina` 
		on `oferta_disciplina`.`id_disciplina` = `disciplina`.`id_disciplina`
	inner join `turma`
		on `oferta_disciplina`.`id_turma` = `turma`.`id_turma`
	inner join curso
		on turma.id_curso = curso.id_curso	
group by 
	curso.modulo,
	`turma`.`id_periodo`,
    `usuario`.`nome`,`usuario`.`id_usuario`;
