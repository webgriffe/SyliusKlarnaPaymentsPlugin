# Klarna plugin

## Klarna payment

Klarna dispone di due Payment Providers: Payments e Checkout. Il primo è utilizzabile come metodo di pagamento
selezionabile attraverso la piattaforma
ecommerce, mentre il secondo è un servizio di pagamento completo che include anche la gestione dell'intero checkout (di
conseguenza non è possibile usare altri metodi di pagamento).
Quest'ultimo metodo è quello integrato di default da Payum.
Per questo plugin utilizzeremo il metodo Klarna Payments.

## HPP

Klarna permette di integrare il metodo Payments tramite due strade: inline-widget e browser redirect (HPP).
Nella prima il gateway viene visualizzato attraverso un iframe e funziona attraverso l'sdk JS di Klarna, mentre nella
seconda il cliente viene reindirizzato su una pagina di Klarna. La prima è la soluzione consigliata da Klarna, mentre la
seconda è quella più simile alle altre soluzioni da noi implementate. Per cui almeno per il momento integreremo solo la
seconda soluzione.
Infatti, come scritto [qui](https://docs.klarna.com/hosted-payment-page/get-started/) la strada HPP è un aggiunta
rispetto al metodo Klarna Payments, quindi nulla vieta di cambiare strada in futuro senza dover rifare da capo
l'integrazione.

Di conseguenza la guida da utilizzare per lo sviluppo di questo plugin è [questa](https://docs.klarna.com/hosted-payment-page/get-started/accept-klarna-payments-using-hosted-payment-page/).

https://docs.klarna.com/hosted-payment-page/get-started/accept-klarna-payments-using-hosted-payment-page/#step-by-step-integration

- [ ] Create a KP session
- 
