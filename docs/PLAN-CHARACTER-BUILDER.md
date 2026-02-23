# Plano: Sistema de criação de personagem (estilo D&D Beyond)

Referência: [D&D Beyond Character Builder](https://www.dndbeyond.com/characters/161672930/builder/whats-next) — fluxo passo a passo ("what's next").

## Duas formas de ter uma ficha

1. **Criar no projeto** — wizard passo a passo, parecido com o site (etapas guiadas).
2. **Importar ficha em PDF** — upload do PDF gerado no D&D Beyond (ou ficha oficial) e extração dos dados; usuário pode editar depois no mesmo formulário da ficha.

Ambas resultam em uma **ficha salva** no backend, que pode ser usada para iniciar sessões de jogo (ex.: stats do jogador vindo da ficha).

---

## 1. Fluxo “Criar no projeto” (wizard estilo D&D Beyond)

Fluxo guiado por etapas, com “Próximo” / “Voltar” e indicação do que falta fazer.

### Etapas sugeridas (espelhando o builder)

| Ordem | Etapa        | Conteúdo resumido |
|-------|--------------|-------------------|
| 1     | **Basics**   | Nome do personagem, nome do jogador, espécie/raça (ex.: Humano, Elfo). |
| 2     | **Class**    | Classe (ex.: Guerreiro, Mago), nível 1. |
| 3     | **Abilities**| Atributos (FOR, DES, CON, INT, SAB, CAR): ponto a ponto, rolagem ou array padrão. |
| 4     | **Background** | Antecedente, tendência, idiomas. |
| 5     | **Equipment** | Equipamento inicial (armas, armadura, itens). |
| 6     | **Review**    | Resumo da ficha; botão “Finalizar” para salvar como ficha completa. |

Cada etapa pode ser salva como **rascunho** (dados parciais). Ao finalizar, a ficha fica disponível na lista “Minhas fichas” e pode ser usada em sessões.

### API (backend)

- **Estado do wizard:** uma “ficha em construção” por usuário (ou por personagem).
  - `POST /api/v1/character-sheets` — inicia nova ficha (rascunho).
  - `GET /api/v1/character-sheets/{id}` — retorna ficha (completa ou rascunho).
  - `PUT /api/v1/character-sheets/{id}` — atualiza (por etapa ou tudo).
  - `PATCH /api/v1/character-sheets/{id}/step/{step}` — salvar apenas uma etapa (ex.: `step=abilities`).
- **Finalizar:** `POST /api/v1/character-sheets/{id}/complete` — marca como completa e trava (ou permite edição limitada depois).
- **Listagem:** `GET /api/v1/character-sheets` — lista fichas do usuário (para escolher na sessão ou continuar o wizard).

---

## 2. Fluxo “Importar PDF”

- **Endpoint:** `POST /api/v1/character-sheets/import-pdf` (multipart; validação: tipo PDF, tamanho máximo 5 MB).
- **Backend:** parser (`CharacterSheetPdfParser`) que extrai texto do PDF (smalot/pdfparser) e mapeia para o mesmo formato de ficha usado pelo wizard (identidade, classe, atributos, PV, CA, etc.).
- **Retorno:** JSON com a ficha preenchida e, se `create=true` (default), uma nova ficha é criada; caso contrário apenas os dados parseados são retornados. Campo `unrecognized` lista o que não foi reconhecido.
- **Observação:** o layout do PDF do D&D Beyond (ou da Wizards) é fixo; o parser pode ser calibrado para esse layout. PDFs de outras fontes podem precisar de regras adicionais.

---

## 3. Modelo de dados (ficha)

Uma ficha completa deve ter pelo menos:

- **Identidade:** nome_personagem, nome_jogador, especie, classe, nivel, antecedente, tendencia.
- **Atributos:** FOR, DES, CON, INT, SAB, CAR (valor e modificador).
- **Combate:** CA, iniciativa, deslocamento, PV (atual, max, temp), dados de vida, sucesso/falha de morte.
- **Perícias:** lista de proficiências; bônus (calculado ou fixo).
- **Equipamento:** lista de itens (armas, armadura, outros).
- **Magias:** (opcional) lista ou JSON para personagens que usam magia.
- **Metadados:** user_id, status (draft | completed), created_at, updated_at.

Sugestão: modelo Eloquent `CharacterSheet` (tabela `character_sheets`) com `state` JSON para flexibilidade nas primeiras versões, ou colunas por seção. O `GameSession` pode ter `character_sheet_id` opcional para que o “player” da sessão use os stats da ficha (PV, CA, dano, etc.) via o mapper existente.

---

## 4. Integração com o jogo atual

- Ao criar uma **Game Session**, permitir (opcionalmente) escolher uma ficha do usuário. O estado inicial do “player” (hp, defense, damage) pode ser derivado da ficha (ex.: PV máximo, CA, bônus de atributo para dano).
- Se não escolher ficha, manter o comportamento atual (valores padrão fixos).

---

## 5. Ordem de implementação (backend)

1. **Modelo e migrations** — `CharacterSheet`, tabela(s), relação com `User`. (Implementado.)
2. **API do wizard** — CRUD de fichas + endpoint de “step” e “complete”. (Implementado.)
3. **Importação de PDF** — upload, parser, retorno no formato da ficha; opção de criar ficha. (Implementado.)
4. **Integração com Game Session** — escolher ficha ao criar sessão (`character_sheet_id`) e popular estado do jogador a partir da ficha. (Implementado.)

---

## 6. Resumo

- **Criar no projeto:** wizard em etapas (Basics → Class → Abilities → Background → Equipment → Review), parecido com o processo do D&D Beyond.
- **Importar PDF:** upload de PDF da ficha; backend extrai dados e devolve no mesmo formato; usuário revisa/edita e salva.
- As duas opções produzem a mesma “ficha” no sistema e podem ser usadas nas sessões de jogo.
