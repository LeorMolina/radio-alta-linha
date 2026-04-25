# 📻 Rádio Alta Linha - Inteligência Sonora para Varejo

Sistema de rádio interna "White-Label" desenvolvido sob medida para a **Alta Linha Móveis Planejados e Decorações**. O projeto foca em transformar a experiência do cliente em loja através de uma grade de programação automatizada e inteligente.

## ✨ Funcionalidades Principais
- **Player Imersivo**: Interface moderna em Dark Mode com transição suave entre faixas.
- **Gestão de Conteúdo**: Painel administrativo para upload de músicas e jingles/propagandas.
- **Motor de Inteligência**: 
    - **Modo Fixo**: Toca 1 propaganda a cada X músicas.
    - **Modo Dinâmico**: Sorteia um intervalo aleatório (ex: entre 2 a 5 músicas) para uma programação mais orgânica.
- **Identidade Visual Premium**: Totalmente adaptado com as cores (Bordô e Cinza Escuro) e tipografia da marca.

## 🛠️ Tecnologias Utilizadas
- **Backend**: PHP 8.x com PDO para conexão segura ao banco de dados.
- **Frontend**: Tailwind CSS para um design responsivo e moderno.
- **Banco de Dados**: MySQL.
- **Tipografia**: Montserrat (Google Fonts).

## 🚀 Como Executar o Projeto Localmente
1. Certifique-se de ter o **XAMPP** instalado.
2. Clone este repositório na pasta `htdocs` ou em sua pasta de desenvolvimento.
3. Configure o banco de dados utilizando o script SQL fornecido (ver pasta `sql/`).
4. Ajuste as credenciais no arquivo `conexao.php`.
5. Acesse o player em: `http://localhost:8080/radio-varejo/radio/`
6. Acesse o gerenciador em: `http://localhost:8080/radio-varejo/radio/admin`

---
Desenvolvido por **Leo Molina** para a Alta Linha.