CREATE DATABASE sistema_ocorrencia
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_ocorrencia;

-- ─── 1. Turma ────────────────────────────────────────────────────────────────
CREATE TABLE turma (
  id_turma        INT          NOT NULL AUTO_INCREMENT,
  desc_turma      VARCHAR(255) NOT NULL,
  turno           VARCHAR(50)  NOT NULL,
  ano_letivo      INT          NOT NULL,
  semestre_letivo INT          DEFAULT NULL,
  trimestre_letivo INT         DEFAULT NULL,
  ativo           TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id_turma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 2. Alunos ───────────────────────────────────────────────────────────────
CREATE TABLE alunos (
  id_aluno        INT          NOT NULL AUTO_INCREMENT,
  id_turma        INT          DEFAULT NULL,
  nome_aluno      VARCHAR(150) NOT NULL,
  num_simade      VARCHAR(50)  NOT NULL,
  dt_nascimento   DATE         NOT NULL,
  PRIMARY KEY (id_aluno),
  UNIQUE KEY uq_simade (num_simade),
  CONSTRAINT fk_alunos_turma
    FOREIGN KEY (id_turma) REFERENCES turma(id_turma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 3. Tipo Funcionário ─────────────────────────────────────────────────────
CREATE TABLE tipo_func (
  id_tipo_func    INT          NOT NULL AUTO_INCREMENT,
  desc_funcionario VARCHAR(150) NOT NULL,
  PRIMARY KEY (id_tipo_func)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 4. Funcionários ─────────────────────────────────────────────────────────
CREATE TABLE funcionarios (
  id_funcionario     INT          NOT NULL AUTO_INCREMENT,
  id_tipo_func       INT          DEFAULT NULL,
  nome_funcionario   VARCHAR(150) NOT NULL,
  email_funcionario  VARCHAR(150) NOT NULL,
  senha_hash         VARCHAR(255) NOT NULL,
  cargo_funcionario  VARCHAR(100) NOT NULL,
  ativo              TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id_funcionario),
  UNIQUE KEY uq_email_func (email_funcionario),
  CONSTRAINT fk_tipo_funcionario
    FOREIGN KEY (id_tipo_func) REFERENCES tipo_func(id_tipo_func)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 5. Disciplinas ──────────────────────────────────────────────────────────
CREATE TABLE disciplinas (
  id_disciplina   INT          NOT NULL AUTO_INCREMENT,
  desc_disciplina VARCHAR(150) NOT NULL,
  PRIMARY KEY (id_disciplina)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 6. Professor / Disciplina ───────────────────────────────────────────────
CREATE TABLE professor_disciplina (
  id_funcionario INT NOT NULL,
  id_disciplina  INT NOT NULL,
  ano_letivo     INT NOT NULL,
  PRIMARY KEY (id_funcionario, id_disciplina, ano_letivo),
  CONSTRAINT fk_pd_funcionario
    FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id_funcionario),
  CONSTRAINT fk_pd_disciplina
    FOREIGN KEY (id_disciplina)  REFERENCES disciplinas(id_disciplina)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 7. Tipo Ocorrência ──────────────────────────────────────────────────────
-- Populado com os 17 itens exatos da folha da escola
CREATE TABLE tipo_ocorrencia (
  id_tipo_ocorrencia INT          NOT NULL AUTO_INCREMENT,
  num_item           INT          NOT NULL,       
  desc_ocorrencia    VARCHAR(255) NOT NULL,
  exige_disciplina   TINYINT(1)   NOT NULL DEFAULT 0, 
  PRIMARY KEY (id_tipo_ocorrencia),
  UNIQUE KEY uq_num_item (num_item)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tipo_ocorrencia (num_item, desc_ocorrencia, exige_disciplina) VALUES
  ( 1, 'Indisciplina durante a aula de',                                                                              1),
  ( 2, 'Desrespeitou o(a) professor(a)',                                                                              1),
  ( 3, 'Agrediu o(a) colega',                                                                                         0),
  ( 4, 'Não trouxe o material necessário',                                                                            0),
  ( 5, 'Não fez as atividades e/ou trabalho solicitado',                                                              0),
  ( 6, 'Tem deixado as atividades de sala incompletas',                                                               0),
  ( 7, 'Chegou atrasado, após o horário de entrada permitido',                                                        0),
  ( 8, 'Chegou atrasado na sala, no horário do(a) professor(a)',                                                      1),
  ( 9, 'Praticou bullying',                                                                                           0),
  (10, 'Atrapalha o bom andamento das aulas com brincadeiras inadequadas/comportamento inconveniente',                0),
  (11, 'Fez uso do celular ou outro aparelho eletrônico durante as aulas',                                            0),
  (12, 'Não estava usando uniforme',                                                                                  0),
  (13, 'Estava usando roupas inadequadas para o ambiente escolar',                                                    0),
  (14, 'Estava "matando aula" do(a) professor(a)',                                                                    1),
  (15, 'Se envolveu em boatos e fofocas, causando transtornos na convivência escolar',                               0),
  (17, 'Outros (especificado abaixo)',                                                                                0);


-- ─── 8. Ocorrências ──────────────────────────────────────────────────────────
CREATE TABLE ocorrencias (
  id_ocorrencia          INT          NOT NULL AUTO_INCREMENT,
  id_aluno               INT          NOT NULL,
  id_funcionario         INT          NOT NULL,
  id_turma               INT          NOT NULL,
  id_disciplina          INT          DEFAULT NULL,
  data_ocorrencia        DATE         NOT NULL,
  horario                TIME         NOT NULL,
  desc_ocorrencia        TEXT         NOT NULL,
  outros_desc            TEXT         DEFAULT NULL,  
  notificar_responsavel  TINYINT(1)   NOT NULL DEFAULT 0, 
  status                 ENUM('pendente','resolvida') NOT NULL DEFAULT 'pendente', 
  data_registro_sistema  DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_ocorrencia),
  CONSTRAINT fk_aluno_ocorrencia
    FOREIGN KEY (id_aluno)         REFERENCES alunos(id_aluno),
  CONSTRAINT fk_funcionario_ocorrencia
    FOREIGN KEY (id_funcionario)   REFERENCES funcionarios(id_funcionario),
  CONSTRAINT fk_turma_ocorrencia
    FOREIGN KEY (id_turma)         REFERENCES turma(id_turma),
  CONSTRAINT fk_disciplina_ocorrencia
    FOREIGN KEY (id_disciplina)    REFERENCES disciplinas(id_disciplina)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 9. Ocorrência × Tipo ─────────────────────────
-- Resolve o problema de uma ocorrência ter múltiplos tipos (checkboxes na folha)
CREATE TABLE ocorrencia_tipos (
  id_ocorrencia      INT NOT NULL,
  id_tipo_ocorrencia INT NOT NULL,
  PRIMARY KEY (id_ocorrencia, id_tipo_ocorrencia),
  CONSTRAINT fk_ot_ocorrencia
    FOREIGN KEY (id_ocorrencia)      REFERENCES ocorrencias(id_ocorrencia)
      ON DELETE CASCADE,
  CONSTRAINT fk_ot_tipo
    FOREIGN KEY (id_tipo_ocorrencia) REFERENCES tipo_ocorrencia(id_tipo_ocorrencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 
