SELECT * FROM u112356623_hacademico.disciplina WHERE id_disciplina BETWEEN 448 AND 457 or id_disciplina BETWEEN 463 AND 473 or id_disciplina BETWEEN 479 AND 489;
SELECT * FROM u112356623_hacademico.disciplina WHERE id_disciplina BETWEEN 511 AND 516 or id_disciplina BETWEEN 517 AND 522 or id_disciplina BETWEEN 523 AND 528;
SELECT * FROM u112356623_hacademico.disciplina WHERE id_disciplina BETWEEN 448 AND 493;
SELECT * FROM u112356623_hacademico.disciplina WHERE id_disciplina > 510;

#INSERT INTO grade (id_disciplina,id_curso,modulo)
SELECT
	#descricao,
	id_disciplina,
    36 as id_curso,
    IF (id_disciplina < 463, 1, IF(id_disciplina < 478, 2,3)) as modulo
FROM 
	u112356623_hacademico.disciplina 
WHERE 
	id_disciplina BETWEEN 448 AND 493;

#INSERT INTO grade (id_disciplina,id_curso,modulo)
SELECT
	#descricao,
	id_disciplina,
    37 as id_curso,
    IF (id_disciplina < 463, 1, IF(id_disciplina < 478, 2,3)) as modulo
FROM 
	u112356623_hacademico.disciplina 
WHERE 
	id_disciplina BETWEEN 448 AND 493;     

#INSERT INTO grade (id_disciplina,id_curso,modulo)
SELECT
	#descricao,
	id_disciplina,
    37 as id_curso,
    IF (id_disciplina < 517, 1, IF(id_disciplina < 523, 2,3)) as modulo
FROM 
	u112356623_hacademico.disciplina 
WHERE (id_disciplina > 510 AND id_disciplina < 527) OR id_disciplina = 528;    