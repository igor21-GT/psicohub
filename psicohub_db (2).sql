-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 19/01/2026 às 18:21
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `psicohub_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `data_evento` datetime NOT NULL,
  `tipo` varchar(50) DEFAULT 'Sessão'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `usuario_id`, `titulo`, `data_evento`, `tipo`) VALUES
(1, 0, 'Sessão com Grupo A', '2026-01-06 10:38:52', 'Grupo'),
(2, 0, 'Reunião Clínica', '2026-01-08 10:38:52', 'Administrativo'),
(3, 0, 'Sessão Individual - João', '2026-01-10 10:38:52', 'Individual'),
(4, 0, 'Evento de consults', '2026-04-12 12:00:00', 'Sessão Individual');

-- --------------------------------------------------------

--
-- Estrutura para tabela `alunos`
--

CREATE TABLE `alunos` (
  `id` int(11) NOT NULL,
  `turma_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `matricula` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `anotacoes`
--

CREATE TABLE `anotacoes` (
  `id` int(11) NOT NULL,
  `turma_id` int(11) NOT NULL,
  `conteudo` text NOT NULL,
  `data_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `entregas`
--

CREATE TABLE `entregas` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `aluno_nome` varchar(100) NOT NULL,
  `arquivo_path` varchar(255) NOT NULL,
  `data_entrega` datetime DEFAULT current_timestamp(),
  `nota` decimal(4,1) DEFAULT NULL,
  `resposta_texto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `materiais`
--

CREATE TABLE `materiais` (
  `id` int(11) NOT NULL,
  `turma_id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `tipo` varchar(20) DEFAULT NULL,
  `conteudo` text DEFAULT NULL,
  `arquivo_path` varchar(255) DEFAULT NULL,
  `caminho_arquivo` varchar(255) NOT NULL,
  `tipo_arquivo` varchar(20) DEFAULT NULL,
  `data_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_postagem` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_limite` date DEFAULT NULL,
  `valor_nota` decimal(4,1) DEFAULT NULL,
  `formato` varchar(20) DEFAULT 'upload',
  `opcoes_json` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `materiais`
--

INSERT INTO `materiais` (`id`, `turma_id`, `titulo`, `tipo`, `conteudo`, `arquivo_path`, `caminho_arquivo`, `tipo_arquivo`, `data_upload`, `data_postagem`, `data_limite`, `valor_nota`, `formato`, `opcoes_json`) VALUES
(9, 3, 'Evento de consults', 'PDF', '', 'uploads/696e4977643b6.pdf', '', NULL, '2026-01-19 15:10:47', '2026-01-19 15:10:47', NULL, NULL, 'upload', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `opcoes`
--

CREATE TABLE `opcoes` (
  `id` int(11) NOT NULL,
  `questao_id` int(11) NOT NULL,
  `texto_opcao` varchar(255) DEFAULT NULL,
  `eh_correta` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `opcoes`
--

INSERT INTO `opcoes` (`id`, `questao_id`, `texto_opcao`, `eh_correta`) VALUES
(16, 4, 'rbrfr', 1),
(17, 4, 'rbr', 0),
(18, 4, 'rthbr', 0),
(19, 4, 'rthbrh', 0),
(20, 4, 'brbrt', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `planos_aula`
--

CREATE TABLE `planos_aula` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `conteudo` text DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `planos_aula`
--

INSERT INTO `planos_aula` (`id`, `titulo`, `conteudo`, `data_criacao`) VALUES
(1, 'Aula sobre Emoções Básicas', 'Dinâmica do espelho e identificação de expressões faciais.', '2026-01-05 12:57:55'),
(2, 'Introdução à TCC', 'Apresentação dos conceitos de pensamento, sentimento e comportamento.', '2026-01-05 12:57:55');

-- --------------------------------------------------------

--
-- Estrutura para tabela `questoes`
--

CREATE TABLE `questoes` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `enunciado` text DEFAULT NULL,
  `tipo` varchar(20) DEFAULT 'multipla_escolha'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `questoes`
--

INSERT INTO `questoes` (`id`, `quiz_id`, `enunciado`, `tipo`) VALUES
(4, 4, 'trbrt', 'multipla_escolha');

-- --------------------------------------------------------

--
-- Estrutura para tabela `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `turma_id` int(11) NOT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `token_acesso` varchar(50) DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `quizzes`
--

INSERT INTO `quizzes` (`id`, `turma_id`, `titulo`, `descricao`, `token_acesso`, `data_criacao`) VALUES
(4, 3, 'Evento de consults', 'gbfb', 'c622e2dfcf66c8ad', '2026-01-19 13:16:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `respostas_alunos`
--

CREATE TABLE `respostas_alunos` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `nome_aluno` varchar(100) DEFAULT NULL,
  `email_aluno` varchar(150) DEFAULT NULL,
  `matricula_aluno` varchar(50) DEFAULT NULL,
  `nota_final` decimal(5,2) DEFAULT NULL,
  `data_envio` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `turmas`
--

CREATE TABLE `turmas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `disciplina` varchar(100) DEFAULT NULL,
  `turno` enum('Manhã','Noite') DEFAULT 'Noite',
  `descricao` text DEFAULT NULL,
  `horario` varchar(50) DEFAULT NULL,
  `status` enum('Ativo','Inativo') DEFAULT 'Ativo',
  `periodo` varchar(50) DEFAULT 'Geral',
  `professor_id` int(11) NOT NULL DEFAULT 1,
  `token_acesso` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `turmas`
--

INSERT INTO `turmas` (`id`, `nome`, `disciplina`, `turno`, `descricao`, `horario`, `status`, `periodo`, `professor_id`, `token_acesso`) VALUES
(2, 'Acompanhamento Infantil', NULL, 'Noite', 'Desenvolvimento cognitivo para crianças.', 'Terças - 09h', 'Ativo', 'Geral', 1, 'cde040cb51dcf4153968318b307bbcd4'),
(3, 'Plantão Psicológico', NULL, 'Noite', 'Atendimento de emergência e triagem.', 'Sextas - 18h', 'Inativo', 'Geral', 1, '10d3f9b43c653cf98f2b5e94aae76815'),
(4, 'EU SOU DEMAIS ', 'IGOR É FODAAAA', 'Manhã', '', '', 'Ativo', 'Geral', 1, 'f1746975d37b321c121fefd7281eddf8');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `criado_em`) VALUES
(1, 'Admin', 'admin@teste.com', '1234', '2026-01-05 14:28:34');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `turma_id` (`turma_id`);

--
-- Índices de tabela `anotacoes`
--
ALTER TABLE `anotacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `turma_id` (`turma_id`);

--
-- Índices de tabela `entregas`
--
ALTER TABLE `entregas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `material_id` (`material_id`);

--
-- Índices de tabela `materiais`
--
ALTER TABLE `materiais`
  ADD PRIMARY KEY (`id`),
  ADD KEY `turma_id` (`turma_id`);

--
-- Índices de tabela `opcoes`
--
ALTER TABLE `opcoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `questao_id` (`questao_id`);

--
-- Índices de tabela `planos_aula`
--
ALTER TABLE `planos_aula`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `questoes`
--
ALTER TABLE `questoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Índices de tabela `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_acesso` (`token_acesso`),
  ADD KEY `turma_id` (`turma_id`);

--
-- Índices de tabela `respostas_alunos`
--
ALTER TABLE `respostas_alunos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Índices de tabela `turmas`
--
ALTER TABLE `turmas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `alunos`
--
ALTER TABLE `alunos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `anotacoes`
--
ALTER TABLE `anotacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `entregas`
--
ALTER TABLE `entregas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `materiais`
--
ALTER TABLE `materiais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `opcoes`
--
ALTER TABLE `opcoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `planos_aula`
--
ALTER TABLE `planos_aula`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `questoes`
--
ALTER TABLE `questoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `respostas_alunos`
--
ALTER TABLE `respostas_alunos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `turmas`
--
ALTER TABLE `turmas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `alunos`
--
ALTER TABLE `alunos`
  ADD CONSTRAINT `alunos_ibfk_1` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `anotacoes`
--
ALTER TABLE `anotacoes`
  ADD CONSTRAINT `anotacoes_ibfk_1` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `entregas`
--
ALTER TABLE `entregas`
  ADD CONSTRAINT `entregas_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materiais` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `materiais`
--
ALTER TABLE `materiais`
  ADD CONSTRAINT `materiais_ibfk_1` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `opcoes`
--
ALTER TABLE `opcoes`
  ADD CONSTRAINT `opcoes_ibfk_1` FOREIGN KEY (`questao_id`) REFERENCES `questoes` (`id`);

--
-- Restrições para tabelas `questoes`
--
ALTER TABLE `questoes`
  ADD CONSTRAINT `questoes_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`);

--
-- Restrições para tabelas `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`);

--
-- Restrições para tabelas `respostas_alunos`
--
ALTER TABLE `respostas_alunos`
  ADD CONSTRAINT `respostas_alunos_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
