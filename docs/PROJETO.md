# Visão do projeto

## Objetivo

O Painel Comercial Riviera centraliza o planejamento e acompanhamento da operação de vendas da Riviera Pescados. Ele substitui a persistência local do protótipo por uma base Laravel compartilhada, mantendo sua aparência, navegação e cálculos.

## Perfis e responsabilidades

### Diretoria

- acompanha indicadores consolidados por período, área, gestor e representante;
- mantém áreas comerciais, gestores e catálogo de SKUs;
- define a meta global mensal e distribui cotas entre áreas;
- consulta a hierarquia e exporta a base de vendas em CSV.

### Gestor de área

- acompanha sua estrutura comercial na interface;
- cadastra e mantém representantes da área;
- distribui a meta recebida entre os representantes;
- usa os três meses anteriores como apoio para a distribuição;
- lança faturamento semanal, produto, valor e volume;
- exporta relatórios da operação.

### Representante

- consulta sua meta, faturamento, percentual atingido e volume;
- acompanha resultados semanais e participação por produto;
- troca a senha provisória no primeiro acesso.

## Fluxo operacional

1. A diretoria cadastra áreas, gestores e produtos.
2. A diretoria informa a meta global de um mês e distribui 100% entre as áreas.
3. O gestor recebe a cota da área e a distribui entre representantes.
4. O gestor registra faturamentos semanais associados a representante e SKU.
5. Dashboards recalculam metas, realizado, volume, run rate, ranking e evolução.
6. Os dados filtrados podem ser exportados em CSV.

## Regras preservadas do protótipo

- a distribuição de metas deve fechar com o total antes de salvar;
- uma área com gestor vinculado não pode ser excluída pela interface;
- um SKU com venda vinculada não pode ser excluído;
- representantes novos recebem senha inicial e troca obrigatória;
- vendas são agrupadas em quatro semanas (`S1` a `S4`);
- valores usam Real brasileiro e volumes usam quilogramas;
- a recuperação de senha continua simulando o envio e exibe a senha temporária na tela.

## Escopo desta primeira estruturação

Esta etapa prioriza equivalência funcional: mesma interface e mesmos fluxos, agora com autenticação por sessão, senhas com hash, banco persistente, migrations, seed, Docker e testes. Melhorias de produto e alterações visuais ficam deliberadamente para uma próxima etapa.
