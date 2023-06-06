=== Appmax ===
Contributors: appmaxplataforma
Tags: woocommerce, appmax, payment
Requires at least: 5.8.1
Tested up to: 6.1.1
Stable tag: 1.0.3
Requires PHP: 8.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Novo plugin Appmax: integra com seu Woocommerce e oferece um conjunto de soluções para turbinar o resultado do seu negócio digital. Tenha uma infraestrutura de vendas online altamente otimizada e aumente o faturamento do seu negócio.

= Compatibilidade =

Compatível desde a versão 5.9.0 do WooCommerce.

= Instalação =

Confira o nosso guia de instalação e configuração do plugin na aba [Installation](http://wordpress.org/plugins/appmax-woocommerce/installation/).

= Dúvidas? =

Você pode esclarecer suas dúvidas usando:

* Criando um tópico no [fórum de ajuda do WordPress](http://wordpress.org/support/plugin/appmax-woocommerce).

== Frequently Asked Questions ==

= Quais métodos de pagamentos estão disponíveis no plugin  =

Cartão de Crédito, Boleto e Pix.

= Como integrar sua conta na Appmax com a plataforma Woocommerce =

Confira o nosso guia nesse [Artigo](https://help.appmax.com.br/pt-BR/articles/5220733-integrando-com-o-woocommerce)

== Installation ==

= Instalação do plugin =

Antes de instalarmos o plugin, sua loja precisa respeitar alguns requisitos mínimos, que são:

* Versão PHP >= 8.0
* Extensão **php-calendar** habilitada/instalada
* Pluguin [WooCommerce](http://wordpress.org/plugins/woocommerce/) - versão >= 5.9.0 habilitado/instalado

Após verificar os requisitos, vá para a aba "Plugins" > "Adicionar novo" e pesquise pelo nome **Appmax**.

= Configuração do plugin =

Uma vez instalado o plugin e ativado, haverá alguns botões de atalho, que são:

* Configurações
* Cartão de Crédito
* Boleto
* Pix

Clicando no botão Configurações, seremos direcionados ao painel de meios de pagamento, no final dessa listagem, serão exibidos
os métodos de pagamento da Appmax, são eles:

* **Appmax - Cartão de Crédito**
* **Appmax - Boleto Bancário**
* **Appmax - Pix**

Habilite ambos e em seguida vamos configurar cada método de pagamento.

= Configurando o Appmax - Cartão de Crédito =

Após de clicar em "Gerenciar", deixe sempre checado a opção "Ativar Appmax - Cartão de Crédito".

Mantenha sempre o padrão de valores nos campos "Título" e "Descrição".

No campo **Appmax API Key**, cole o token gerado na plataforma da Appmax.

No campo **Número de parcelas**, selecione a quantidade de parcelas.

No campo **Exibir total na parcela**, selecione a opção "sim" caso queira que seja exibido o total da parcela ou selecione a opção "não" para não exibir o total na parcela.

No campo **Juros de cartão de crédito**, informe os juros de cartão de crédito. Exemplo: 1.5

No campo **Receber Pedidos de CallCenter** de **Cartão de Crédito**, selecione a opção "Quando estiver integrado" para receber os pedidos de CallCenter da plataforma Appmax quando os mesmos estiverem com status "Integrado" ou selecione a opção "Quando estiver pago" para receber os pedidos de CallCenter da plataforma Appmax quando os mesmos estiverem com status "Aprovado".

No campo **Status dos pedidos em análise antifraude**, selecione a opção "Em processamento" para atualizar o status dos seus pedidos para "Em processamento" ou selecione "Aguardando" para atualizar o status dos seus pedidos para "Aguardando confirmação de pagamento". Isso uma vez que, o status do pedido na plataforma Appmax esteja em 'Análise Antifraude'.

No campo **Criar pedido na loja com status**, selecione a opção "Em processamento" para que seus pedidos sejam criados com o status 'Em processamento' ou selecione a opção "Pagamento pendente" para que seus pedidos seja criados com o status 'Pagamento pendente'.

> **Atenção**: Deixe habilitado a opção "Habilitar log". Estando essa opção habilitado, podemos ver os logs de transações de Cartão de Crédito.

= Configurando o Appmax - Boleto Bancário =

Após de clicar em "Gerenciar", deixe sempre checado a opção "Ativar Appmax - Boleto Bancário".

Mantenha sempre o padrão de valores nos campos "Título" e "Descrição".

No campo **Appmax API Key**, cole o token gerado na plataforma da Appmax.

No campo **Dias de Vencimento**, informe o número de dias de vencimento dos boletos. Por padrão o número de dias é 3.

No campo **Receber Pedidos de CallCenter** de **Boleto**, selecione a opção "Quando estiver integrado" para receber os pedidos de CallCenter da plataforma Appmax quando os mesmos estiverem com status "Integrado" ou selecione a opção "Quando estiver pago" para receber os pedidos de CallCenter da paltaforma Appmax quando os mesmos estiverem com status "Aprovado".

> **Atenção**: Deixe habilitado a opção "Habilitar log". Estando essa opção habilitado, podemos ver os logs de transações de Boleto Bancário.

= Configurando o Appmax - Pix =

Após de clicar em "Gerenciar", deixe sempre checado a opção "Ativar Appmax - Pix".

Mantenha sempre o padrão de valores nos campos "Título" e "Descrição".

No campo **Appmax API Key**, cole o token gerado na plataforma da Appmax.

No campo **Receber Pedidos de CallCenter** de **Pix**, selecione a opção "Quando estiver integrado" para receber os pedidos de CallCenter da plataforma Appmax quando os mesmos estiverem com status "Integrado" ou selecione a opção "Quando estiver pago" para receber os pedidos de CallCenter da paltaforma Appmax quando os mesmos estiverem com status "Aprovado".

> **Atenção**: Deixe habilitado a opção "Habilitar log". Estando essa opção habilitado, podemos ver os logs de transações de Pix.

= Checkout por Cartão de Crédito =

Quando o checkout for a opção Appmax - Cartão de Crédito, todos os campos são **obrigatórios** e devem ser preenchidos.

= Checkout por Boleto Bancário =

Quando o checkout for a opção Appmax - Boleto Bancário, todos os campos são **obrigatórios** e devem ser preenchidos.

= Checkout por Pix =

Quando o checkout for a opção Appmax - Pix, todos os campos são **obrigatórios** e devem ser preenchidos.

= Logs =

Para visualizar os logs das transações vá para a aba "WooCommerce" > "Status". E em seguida, clique na tab "Logs".

Descendo um pouco a página, você verá um seletor de logs.

> **Atenção**: Sempre verifique se no seletor está selecionado o arquivo de log do dia atual.

Uma vez feita essa verificação, clique no botão "Visualizar" ao lado do seletor.

Então você estará acompanhando os logs das transações.

Os arquivos de logs das transações são gerados automaticamente e nomeados conforme a escolha do checkout.

Logs de transações de Cartão de Crédito: **appmax-credit-card-{DATA-ATUAL}-{HASH}**

Logs de transações de Boleto: **appmax-billet-{DATA-ATUAL}-{HASH}**

Logs de transações de Pix: **appmax-pix-{DATA-ATUAL}-{HASH}**

== Changelog ==

= 1.0.0 =

* Versão inicial do plugin

= 1.0.1 =

* Correções métodos de pagamento

= 1.0.2 =

* Correções qrcode pix

= 1.0.3 =

* Correções qrcode pix