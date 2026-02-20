# 🎲 Dungeons & Dragons Backend API

Uma API RESTful desenvolvida em Laravel para gerenciar sessões de RPG de Dungeons & Dragons 5e, com integração de IA para criar experiências imersivas e dinâmicas.

<p align="center">
<a href="https://github.com/laravel/framework"><img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel" alt="Laravel 12"></a>
<a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=flat&logo=php" alt="PHP 8.4"></a>
<a href="https://pestphp.com"><img src="https://img.shields.io/badge/Pest-4-9C4FFF?style=flat" alt="Pest 4"></a>
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License"></a>
</p>

---

## 📖 Sobre o Projeto

Este projeto é um backend para gerenciar sessões de Dungeons & Dragons, utilizando agentes de IA para criar narrativas dinâmicas, gerenciar combates e simular NPCs. A aplicação integra o poder do Laravel 12 com Laravel AI para proporcionar uma experiência única de RPG.

### ✨ Principais Funcionalidades

- **🎭 Agentes de IA Especializados**
  - **DDMaster**: Dungeon Master experiente que narra aventuras
  - **LoreKeeper**: Guardião do conhecimento e lore do mundo
  - **NPCMind**: Simula personalidade e ações de NPCs

- **⚔️ Sistema de Combate**
  - Resolução automática de turnos
  - Gerenciamento de estado de combate
  - Sistema de dados (dice rolling)

- **🎮 Gerenciamento de Sessões**
  - Criação e controle de sessões de jogo
  - Persistência de estado da campanha
  - Histórico de eventos e logs

- **🔐 Autenticação & Autorização**
  - Sistema completo com Laravel Sanctum
  - Políticas de acesso por sessão
  - API versionada (v1)

---

## 🏗️ Arquitetura

O projeto segue princípios de **Domain-Driven Design (DDD)** e **Clean Architecture**:

```
app/
├── Ai/
│   └── Agents/              # Agentes de IA (DDMaster, LoreKeeper, NPCMind)
├── Application/
│   ├── Actions/             # Casos de uso da aplicação
│   └── Services/            # Serviços de aplicação
├── Domain/
│   ├── AI/                  # Lógica de domínio de IA
│   ├── Campaign/            # Gestão de campanhas
│   ├── Character/           # Gerenciamento de personagens
│   ├── Combat/              # Sistema de combate
│   └── Turn/                # Engine de turnos
├── Http/
│   ├── Controllers/         # Controllers da API
│   ├── Middleware/          # Middlewares customizados
│   ├── Requests/            # Form Requests para validação
│   └── Responses/           # Respostas padronizadas
├── Infrastructure/          # Implementações de infraestrutura
├── Models/                  # Modelos Eloquent
└── Policies/                # Políticas de autorização
```

### 📦 Camadas da Aplicação

- **Domain**: Regras de negócio puras e lógica central
- **Application**: Coordena casos de uso e orquestra o domínio
- **Infrastructure**: Implementações técnicas e integrações
- **Http**: Camada de apresentação (API REST)

---

## 🚀 Tecnologias

### Core
- **PHP 8.4.1** - Linguagem de programação
- **Laravel 12** - Framework web moderno
- **Laravel AI 0.2** - Integração com modelos de IA
- **Laravel Sanctum 4** - Autenticação de API

### Desenvolvimento
- **Pest 4** - Framework de testes moderno
- **Laravel Pint** - Code style fixer
- **Laravel Boost 2.1** - Ferramentas MCP para desenvolvimento
- **Laravel Pail** - Log viewer em tempo real
- **L5-Swagger** - Documentação de API

### IA
- **Ollama** - Provider padrão para modelos LLM locais

---

## ⚙️ Instalação

### Pré-requisitos

- PHP >= 8.4
- Composer
- Node.js >= 18
- Ollama (para modelos de IA locais)

### Passos

1. **Clone o repositório**
   ```bash
   git clone <repository-url>
   cd DungeonAndDragons_Backend
   ```

2. **Configuração rápida**
   ```bash
   composer run setup
   ```

   Este comando irá:
   - Instalar dependências PHP
   - Criar arquivo `.env`
   - Gerar chave da aplicação
   - Executar migrations
   - Instalar dependências npm
   - Compilar assets

3. **Configure as variáveis de ambiente**
   
   Edite o arquivo `.env`:
   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=/absolute/path/to/database.sqlite
   
   # Configuração de IA
   AI_DEFAULT_PROVIDER=ollama
   OLLAMA_BASE_URL=http://localhost:11434
   ```

4. **Inicie o servidor de desenvolvimento**
   ```bash
   composer run dev
   ```

   Este comando iniciará:
   - Servidor Laravel (`php artisan serve`)
   - Worker de filas
   - Visualizador de logs (Pail)
   - Vite dev server

---

## 📚 API Endpoints

### Autenticação

```http
POST   /api/v1/auth/register    # Registrar novo usuário
POST   /api/v1/auth/login       # Login
POST   /api/v1/auth/logout      # Logout
GET    /api/v1/auth/me          # Obter usuário autenticado
```

### Sessões de Jogo

```http
POST   /api/v1/game-sessions           # Criar nova sessão
GET    /api/v1/game-sessions/{id}      # Obter sessão
DELETE /api/v1/game-sessions/{id}      # Deletar sessão
GET    /api/v1/game-sessions/{id}/logs # Obter logs da sessão
POST   /api/v1/game-sessions/{id}/turn # Processar turno
```

### Saúde da API

```http
GET    /api/v1/health    # Health check
```

> **Documentação completa**: Acesse `/api/documentation` quando o servidor estiver rodando

---

## 🧪 Testes

O projeto utiliza **Pest 4** para testes.

```bash
# Executar todos os testes
composer test

# Executar um teste específico
php artisan test --filter=TestName

# Testes com cobertura
php artisan test --coverage
```

---

## 🎯 Estrutura de Domínio

### Turn Engine

O motor de turnos é responsável por:
- Processar ações dos jogadores
- Resolver combates
- Rolar dados
- Atualizar estado do jogo

### Agentes de IA

#### DDMaster
Dungeon Master que narra a aventura, mantendo a imersão e coerência narrativa.

#### LoreKeeper
Gerencia o conhecimento do mundo, história e lore da campanha.

#### NPCMind
Simula comportamento e personalidade de NPCs com base no contexto.

---

## 🔧 Scripts Disponíveis

```bash
# Configuração inicial
composer run setup

# Ambiente de desenvolvimento
composer run dev

# Executar testes
composer run test

# Formatar código
./vendor/bin/pint
```

---

## 📝 Convenções de Código

- **PSR-12**: Padrão de código PHP
- **Strict Types**: Habilitado em todos os arquivos
- **Type Hints**: Obrigatório em todos os métodos
- **DDD**: Separação clara entre domínio, aplicação e infraestrutura

---

## 🤝 Contribuindo

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

### Checklist

- [ ] Código segue as convenções do projeto
- [ ] Testes foram adicionados/atualizados
- [ ] Executou `./vendor/bin/pint` para formatar o código
- [ ] Todos os testes estão passando

---

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

---

## 👨‍💻 Autor

Desenvolvido com ❤️ para a comunidade D&D

---

## 🔗 Links Úteis

- [Documentação Laravel 12](https://laravel.com/docs)
- [Laravel AI](https://github.com/laravel/ai)
- [Pest PHP](https://pestphp.com)
- [D&D 5e SRD](https://dnd.wizards.com/resources/systems-reference-document)
