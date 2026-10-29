# 🌍 Consulta de Países — PHP + API REST Countries

Aplicação web em **PHP** que consome uma **API pública** (REST Countries), recebe os dados em **JSON**, processa e apresenta as informações de forma dinâmica ao usuário: capital, população, moeda, região, idiomas e bandeira.

> Projeto acadêmico desenvolvido para praticar **requisições HTTP**, **consumo de APIs REST** e **manipulação de JSON** com PHP.

---

## 📸 Telas do sistema

> 📌 **Onde colocar os prints:** salve as imagens na pasta [`docs/prints/`](docs/prints) com os nomes abaixo. Os links já estão prontos — ao subir as imagens para o GitHub, elas aparecem automaticamente aqui.

### Página inicial
![Página inicial](https://i.postimg.cc/FsG0NWSN/01-pagina-inicial.png)

### Resultado de uma consulta (ex.: Brasil)
![Resultado da consulta](https://i.postimg.cc/qMQ24bKB/02-resultado.png)

### Consulta com vários resultados (ex.: "united")
![Vários resultados](https://i.postimg.cc/NfbRBJXs/03-varios-resultados.png)

### País não encontrado / erro de validação
![Mensagem de erro](https://i.postimg.cc/PrdmGsC5/04-erro.png)

---

## 🎯 Objetivo

Permitir que o usuário informe o nome de um país e que o PHP:

1. envie uma requisição **GET** à API REST Countries;
2. receba a resposta em **JSON**;
3. interprete (`json_decode`) e trate os dados;
4. exiba o resultado em uma página HTML estilizada.

### Exemplo de fluxo

```
Usuário digita: Brasil  →  [ CONSULTAR ]

PHP faz:  GET https://api.restcountries.com/countries/v5/name?q=Brasil
          (com o cabeçalho Authorization: Bearer SUA_CHAVE)
API retorna JSON

PHP exibe:
  País:       Brasil (BR)
  Capital:    Brasília
  População:  213.421.037 hab.
  Moeda:      Real brasileiro (BRL · R$)
  Região:     Americas — South America
```

---

## ✨ Funcionalidades

- Página inicial com **título**, **formulário**, **campo de pesquisa**, **botão de consulta** e **área de resultados**
- Busca por nome do país (ex.: `Brasil`, `Japan`, `Germany`) com busca de texto livre como alternativa
- Exibição de bandeira, capital, população (formatada em pt-BR), moeda, região, sub-região e idiomas
- Suporte a **múltiplos resultados** (ex.: buscar `united` retorna vários países)
- **Validação** da entrada e **tratamento de erros** (país não encontrado, API fora do ar, sem internet)
- Proteção contra **XSS** com `htmlspecialchars` em toda saída
- Layout responsivo com Bootstrap 5

---

## 🛠️ Tecnologias

| Tecnologia | Uso |
|---|---|
| PHP 8+ | Lógica do servidor, requisição HTTP (cURL) e leitura do JSON |
| HTML5 + CSS3 | Estrutura e estilo |
| Bootstrap 5 (CDN) | Layout responsivo e componentes |
| [REST Countries API v5](https://restcountries.com) (exige chave gratuita) | Fonte dos dados |

---

## 📁 Estrutura do projeto

```
consulta-paises/
├── index.php              # Página inicial: formulário + área de resultados
├── src/
│   └── RestCountries.php  # Funções: requisição, busca e formatação dos dados
├── assets/
│   └── css/style.css      # Estilos personalizados
├── docs/
│   └── prints/            # 📸 Prints das telas para o README
├── config.example.php     # Modelo do arquivo de configuração (copie para config.php)
├── .gitignore
└── README.md
```

---

## 🔎 Como funciona (lógica)

| Função | Arquivo | O que faz |
|---|---|---|
| `carregarChave()` | `src/RestCountries.php` | Lê a chave da API em `config.php` (ou variável de ambiente) |
| `requisitar()` | `src/RestCountries.php` | Faz o GET com cURL (cabeçalho `Authorization: Bearer`, timeout de 10 s) e devolve status HTTP + JSON decodificado |
| `buscarPaises()` | `src/RestCountries.php` | Consulta `/countries/v5/name?q=`; se não achar, tenta a busca livre `?q=`; trata erros |
| `formatarPais()` | `src/RestCountries.php` | Transforma o JSON bruto em um array simples (população formatada, moedas, idiomas…) |
| `index.php` | raiz | Valida a entrada, chama as funções acima e imprime os resultados |

### Tratamento de erros

| Situação | Comportamento |
|---|---|
| Campo vazio ou com caracteres inválidos | Mensagem de validação |
| API sem resultados (200 vazio ou 404) | "Nenhum país encontrado" |
| **401** | Chave da API ausente ou inválida |
| **403** | Limite mensal do plano gratuito atingido |
| **429** | Muitas requisições em pouco tempo |
| Outro erro (500…) | Mensagem informando o código HTTP |
| Falha de conexão / timeout | Mensagem pedindo para verificar a internet |

---

## ▶️ Como executar

**Pré-requisitos:** PHP 8.0+ com a extensão **cURL** habilitada e acesso à internet.

### Passo 0 — Obter a chave da API (gratuita)

A API REST Countries exige uma chave. O plano gratuito (500 requisições/mês, sem cartão de crédito) é suficiente para este projeto.

1. Crie sua conta em <https://restcountries.com/sign-up> e copie sua chave;
2. Na pasta do projeto, copie `config.example.php` para `config.php`;
3. Abra o `config.php` e troque `COLE_SUA_CHAVE_AQUI` pela sua chave.

> ⚠️ O `config.php` está no `.gitignore` e **não** deve ser enviado ao GitHub: a chave é pessoal.

### Opção 1 — Servidor embutido do PHP

```bash
git clone https://github.com/SEU-USUARIO/consulta-paises.git
cd consulta-paises
php -S localhost:8000
```

Acesse: <http://localhost:8000>

### Opção 2 — XAMPP / WAMP

1. Copie a pasta `consulta-paises` para `htdocs` (XAMPP) ou `www` (WAMP);
2. Inicie o Apache;
3. Acesse: <http://localhost/consulta-paises>

> Se aparecer erro de cURL, habilite `extension=curl` no `php.ini` e reinicie o servidor.

---

## 🧪 Sugestões de teste

| Busca | Resultado esperado |
|---|---|
| `Brasil` ou `Brazil` | Brasil |
| `Germany` | Alemanha |
| `united` | Vários países |
| `xyzabc` | Mensagem "Nenhum país encontrado" |
| `123` ou campo vazio | Mensagem de validação |

---

## 🚀 Melhorias futuras

- Busca por capital, moeda ou região
- Cache local das respostas para economizar a cota mensal da API
- Comparação entre dois países
- Versão com `fetch()` em JavaScript (sem recarregar a página)

---

## 👤 Autor

**Lucas Braga de Souza Lima**
Estudante de Desenvolvimento de Sistemas — ETEC Zona Leste

## 📄 Licença

Projeto acadêmico, de uso livre para fins educacionais.
Dados fornecidos por [REST Countries](https://restcountries.com).
