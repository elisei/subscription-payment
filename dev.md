## Alterar o form no pagamento:
[x] capturar ciclo desejado

## Incorporar o flag recurring
[x] Observer
[x] Gateway
[x] view

## System.xml
[x] Criar dinamico para input das opções de assinatura (1h, 1d, 14d, 30d, 45d)

## Criar tabela para:
[x] registrar o tempo de ciclo (permite ao cliente depois altera-lo)
[x] relacionar pedido feito como inicial
[x] registrar o customer id do cliente
[x] registrar o status da assinatura (inicial)
[ ] registrar as ordens criadas por essa assinatura
[ ] registrar o cartão usado (esse evento tem que ser after order, gravarei apenas o ID)

## Novo Ciclo
[x] Criar cron para listar e processar os novos pagamentos
[ ] registrar o status da assinatura
[ ] Criar notificação ao admin para casos de erros no ciclo
[ ] Criar retentativas para transações não autorizadas

## Gerenciamento de assinaturas
[ ] Criar pagina na minha conta
[ ] Identificar order de origem
[ ] Criar controllers para pausar, alterar ciclo, deletar...
[ ] Listar orders subsequentes
