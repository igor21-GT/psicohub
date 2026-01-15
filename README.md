<div align="center">
  
  <img src="https://cdn-icons-png.flaticon.com/512/3074/3074765.png" width="100px" alt="PsicoHub Logo" />

  # 🧠 PsicoHub

  **Sistema de Gestão Escolar & Apoio Pedagógico**

  <p>
    <img src="https://img.shields.io/badge/STATUS-FINALIZADO-10b981?style=for-the-badge&logo=checkbox" alt="Status" />
    <img src="https://img.shields.io/badge/VERSÃO-1.0.0-blue?style=for-the-badge" alt="Version" />
    <img src="https://img.shields.io/badge/LICENSE-MIT-green?style=for-the-badge" alt="License" />
  </p>

  <p style="font-size: 1.2rem;">
    Uma solução Full-Stack robusta para professores e gestores. <br>
    Organize turmas, aplique provas online e acompanhe o desempenho dos alunos com uma interface moderna.
  </p>

  <a href="#-funcionalidades">Funcionalidades</a> •
  <a href="#-tecnologias">Tecnologias</a> •
  <a href="#-instalação">Instalação</a> •
  <a href="#-autor">Autor</a>

</div>

<br>

---

## 📸 Galeria do Projeto

<div align="center">
  <table>
    <tr>
      <td align="center"><b>🖥️ Dashboard (Modo Escuro)</b></td>
      <td align="center"><b>☀️ Planejador (Modo Claro)</b></td>
    </tr>
    <tr>
      <td><img src="https://via.placeholder.com/400x200/1e293b/ffffff?text=Dashboard+Dark" width="400" /></td>
      <td><img src="https://via.placeholder.com/400x200/f0f2f5/1e293b?text=Planejador+Light" width="400" /></td>
    </tr>
    <tr>
      <td align="center"><b>📝 Criador de Quiz</b></td>
      <td align="center"><b>📊 Relatório de Notas</b></td>
    </tr>
    <tr>
      <td><img src="https://via.placeholder.com/400x200/1e293b/ffffff?text=Criar+Quiz" width="400" /></td>
      <td><img src="https://via.placeholder.com/400x200/1e293b/ffffff?text=Notas+Alunos" width="400" /></td>
    </tr>
  </table>
</div>

---

## 🚀 Funcionalidades

### 🔐 Acesso & Segurança
* [x] Sistema de Login seguro (Hash de senhas).
* [x] Controle de sessão por usuário.

### 🏫 Gestão Acadêmica
* [x] **Gerenciamento de Turmas:** Cadastro por turno e período.
* [x] **Sala de Aula Virtual:** Lista de alunos, materiais (PDF/Vídeo) e anotações.
* [x] **Diário de Classe:** Histórico de ocorrências e observações.

### 📝 Avaliações Inteligentes (Quiz)
* [x] **Criação Dinâmica:** Monte provas com múltiplas questões.
* [x] **Link Externo:** Alunos respondem sem precisar de conta no sistema.
* [x] **Correção Automática:** O sistema calcula a nota instantaneamente.
* [x] **Feedback Visual:** Gráficos de aprovação e reprovação.

### 🎨 UI/UX Design (Destaque)
* [x] **Dark & Light Mode:** Alternância de tema com persistência local.
* [x] **Responsividade:** Funciona em Desktop e Tablets.
* [x] **Design System:** Criado com CSS Puro (Sem Frameworks).

---

## 🛠️ Tecnologias

Este projeto foi desenvolvido para demonstrar domínio em tecnologias web fundamentais, sem dependência de frameworks pesados.

<div align="center">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" />
  <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" />
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" />
</div>

---

## 📥 Instalação

```bash
# 1. Clone este repositório
$ git clone [https://github.com/SEU_USUARIO/PsicoHub.git](https://github.com/SEU_USUARIO/PsicoHub.git)

# 2. Configure o Banco de Dados
# Importe o arquivo 'database.sql' no seu phpMyAdmin

# 3. Configure a Conexão
# Edite 'config/db.php' com suas credenciais

# 4. Inicie o Servidor
# Mova a pasta para o diretório do Apache (htdocs)