# AutoTrade

Dividimos a estratégia em 4 etapas:

1. Identificar a tendência do mercado usando médias móveis:

* Use a média móvel exponencial (EMA) de 50 períodos e a EMA de 200 períodos para determinar a tendência geral do mercado.
* Compre (CALL) apenas quando a EMA de 50 períodos estiver acima da EMA de 200 períodos, indicando uma tendência de alta.
* Venda (PUT) apenas quando a EMA de 50 períodos estiver abaixo da EMA de 200 períodos, indicando uma tendência de baixa.

2. Confirmar sinais de entrada com RSI e MACD:

* Em uma tendência de alta, procure oportunidades de compra (CALL) quando o RSI estiver acima de 50 e o MACD (linha MACD) cruzar acima da linha de sinal.
* Em uma tendência de baixa, procure oportunidades de venda (PUT) quando o RSI estiver abaixo de 50 e o MACD (linha MACD) cruzar abaixo da linha de sinal.

3. Refinar entradas com Bollinger Bands e Oscilador Estocástico:

* Em uma tendência de alta, confirme o sinal de compra (CALL) quando o preço estiver próximo à banda inferior de Bollinger e o Oscilador Estocástico %K cruzar acima do %D.
* Em uma tendência de baixa, confirme o sinal de venda (PUT) quando o preço estiver próximo à banda superior de Bollinger e o Oscilador Estocástico %K cruzar abaixo do %D.

Gestão de risco e saída de posição:

* Defina stop-loss e take-profit com base na volatilidade do mercado e nos níveis de suporte e resistência.
* Monitore o desempenho da posição e ajuste os níveis de stop-loss e take-profit conforme necessário para proteger os lucros e limitar as perdas.



Os indicadores criados até o momento e disponível são: SMA, EMA, Bollinger, RSI, MACD e oscilador estocástico
