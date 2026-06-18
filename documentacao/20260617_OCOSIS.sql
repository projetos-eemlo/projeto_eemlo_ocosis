-- PROJETO OCOSIS - 2026
DROP DATABASE IF EXISTS sistema_ocorrencia;
CREATE DATABASE sistema_ocorrencia;
USE sistema_ocorrencia;

-- 1. Turma
CREATE TABLE turma (
  id_turma INT NOT NULL AUTO_INCREMENT,
  desc_turma VARCHAR(255) NOT NULL,
  turno VARCHAR(50) NOT NULL,
  ano_letivo INT NOT NULL,
  semestre_letivo INT DEFAULT NULL,
  trimestre_letivo INT DEFAULT NULL,
  PRIMARY KEY (id_turma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Alunos
CREATE TABLE alunos (
  id_aluno INT NOT NULL AUTO_INCREMENT,
  id_turma INT DEFAULT NULL,
  nome_aluno VARCHAR(150) NOT NULL,
  num_simade VARCHAR(50) NOT NULL,
  dt_nascimento DATE NOT NULL,
  PRIMARY KEY (id_aluno),
  UNIQUE KEY (num_simade),
  CONSTRAINT fk_alunos_turma FOREIGN KEY (id_turma) REFERENCES turma(id_turma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tipo Funcionário
CREATE TABLE tipo_func(
  id_tipo_func INT NOT NULL AUTO_INCREMENT,
  desc_funcionario VARCHAR(150) NOT NULL, 
  PRIMARY KEY (id_tipo_func)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Funcionários
CREATE TABLE funcionarios (
  id_funcionario INT NOT NULL AUTO_INCREMENT,
  id_tipo_func INT DEFAULT NULL,
  nome_funcionario VARCHAR(150) NOT NULL,
  email_funcionario VARCHAR(150) NOT NULL,
  senha_hash VARCHAR(255) NOT NULL,
  cargo_funcionario VARCHAR(100) NOT NULL,
  PRIMARY KEY (id_funcionario),
  CONSTRAINT fk_tipo_funcionario FOREIGN KEY (id_tipo_func) REFERENCES tipo_func(id_tipo_func)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Disciplinas
CREATE TABLE disciplinas(
  id_disciplina INT NOT NULL AUTO_INCREMENT,
  desc_disciplina VARCHAR(150) NOT NULL,
  PRIMARY KEY (id_disciplina)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Professor / Disciplina (Tabela Intermediária)
CREATE TABLE professor_disciplina (
  id_funcionario INT NOT NULL,
  id_disciplina INT NOT NULL,
  ano_letivo INT NOT NULL,  
  PRIMARY KEY (id_funcionario, id_disciplina, ano_letivo),
  CONSTRAINT fk_pd_funcionario FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id_funcionario),
  CONSTRAINT fk_pd_disciplina  FOREIGN KEY (id_disciplina) REFERENCES disciplinas(id_disciplina)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tipo Ocorrência (Corrigido o nome da PRIMARY KEY)
CREATE TABLE tipo_ocorrencia (
  id_tipo_ocorrencia INT NOT NULL AUTO_INCREMENT,
  desc_ocorrencia VARCHAR(150) NOT NULL,
  PRIMARY KEY (id_tipo_ocorrencia) -- Ajustado aqui
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Ocorrências (Interligando todas as tabelas corretamente)
CREATE TABLE ocorrencias (
  id_ocorrencia INT NOT NULL AUTO_INCREMENT,
  id_aluno INT NOT NULL,
  id_funcionario INT NOT NULL,
  id_turma INT NOT NULL,
  id_tipo_ocorrencia INT NOT NULL, -- Alterado de VARCHAR para INT
  id_disciplina INT NOT NULL,       -- Alterado de VARCHAR para INT (relacionando com disciplinas)
  data_ocorrencia DATE NOT NULL,
  horario TIME NOT NULL,
  desc_ocorrencia TEXT NOT NULL,
  data_registro_sistema DATETIME DEFAULT CURRENT_TIMESTAMP,  
  PRIMARY KEY (id_ocorrencia),
  CONSTRAINT FK_AlunoOcorrencia FOREIGN KEY (id_aluno) REFERENCES alunos(id_aluno),
  CONSTRAINT FK_FuncionarioOcorrencia FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id_funcionario),
  CONSTRAINT FK_TurmaOcorrencia FOREIGN KEY (id_turma) REFERENCES turma(id_turma),
  CONSTRAINT FK_TipoOcorrencia FOREIGN KEY (id_tipo_ocorrencia) REFERENCES tipo_ocorrencia(id_tipo_ocorrencia),
  CONSTRAINT FK_DisciplinaOcorrencia FOREIGN KEY (id_disciplina) REFERENCES disciplinas(id_disciplina)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;