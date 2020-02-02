=== WooCommerce Payment Gateway - tpay.com  ===
Contributors: tpay.com
Donate link: https://tpay.com/
Tags: woocommerce, tpay, payment, polish gateway, polska brama płatności, bramka płatności, płatności internetowe
Requires at least: 4.7.0
Tested up to: 5.3.2
Requires PHP: 5.3
Stable tag: 2.8.4
License: GPLv2

Accept payments from all major polish banks directly on your WooCommerce site via tpay.com polish payment gateway system.

== Description ==

Brama płatności dla pluginu Woocommerce.

tpay.com to system szybkich płatności online należący do spółki Krajowy Integrator Płatności SA. Misją przedsiębiorstwa jest wprowadzanie oraz propagowanie nowatorskich metod płatności i rozwiązań płatniczych zapewniających maksymalną szybkość i bezpieczeństwo dokonywanych transakcji.

Jako lider technologiczny, tpay.com oferują największą liczbę metod płatności na rynku. W ofercie ponad 50 sposobów zapłaty znajdą Państwo m.in. największy wybór e-transferów, Zintegrowaną Bramkę Płatności Kartami, mobilną galerię handlową RockPay oraz narzędzie do zbiórek pieniężnych w sieci – serwis eHat.me. Dodatkowe funkcjonalności systemu obejmują pełen design w RWD, przelewy masowe oraz udostępnione biblioteki mobilne i dodatki do przeglądarek automatyzujące przelewy. tpay.com oferuje również płatności odroczone, raty online Premium SMS oraz płatność za pomocą kodu QR.

tpay.com zapewnia najwyższy poziom bezpieczeństwa potwierdzony certyfikatem PCI DSS Level 1. System gwarantuje wygodę oraz możliwość natychmiastowej realizacji zamówienia. Oferta handlowa tpay.com jest dokładnie dopasowana do Twoich potrzeb.

tpay.com Online Payment System belongs to Krajowy Integrator Płatności Inc. The company’s mission is to introduce and promote innovative payment methods and solutions ensuring maximum speed and safety of online transactions.

As technological leader, tpay.com offers the largest number of payment methods on market. Among over 50 ways of finalizing transactions you will find the widest choice of direct online payments, Integrated Card Payment Gate, mobile shopping center – RockPay and group payments tool – eHat.me. Additional features include: RWD design, mass pay-outs, mobile libraries and payment automation application. You can also pay using postponed payment, online installments, Premium SMS and QR code payment.

The highest level of security of payments processed by tpay.com is verified by PCI DSS Level 1 certificate. System guarantees convenience and instant order execution. Our business offer is flexible and prepared according to your needs.


== Installation ==

= Installation instruction =

https://support.tpay.com/pl/developer/addons/woocommerce/woocommerce-instrukcja

= Testy =

Moduł był testowany na systemie zbudowanym z wersji Woocommerce 3.8.1 i Wordpress 5.3.2


= KONTAKT =

W razie potrzeby odpowiedzi na pytania powstałe podczas lektury lub szczegółowe wyjaśnienie kwestii technicznych prosimy o kontakt poprzez formularz znajdujący się w Panelu Odbiorcy lub na adres e-mail: pt@tpay.com
== Changelog ==
v2.8.4
Naprawiono problem ładowania plików statycznych
v2.8.3
Zoptymalizowano kod wyzwalający wtyczkę.
Rozdzielono wykorzystanie wtyczki podstawowej i kartowej
Dodano niezależną opcję sprawdzania proxy dla wtyczki kartowej
Dodano informacje o kompatybilności z WooCommerce
Usunięto problem z nadpisywaniem styli checkbox'u akceptacji regulaminu Tpay
v2.8.2
Poprawiono błąd w składni SQL powodujący brak możliwości aktualizacji niektórych instancji modułu.
v2.8.1
Dodano wiele poprawek wyświetlania formularzy
v2.8.0
Rebranding - wprowadzono nowe style formularzy oraz zmieniono logotypy Tpay.
v2.7.9
Poprawiono wyświetlanie formularza banków w widoku listy rozwijanej.
Poprawiono walidację kodu BLIK i treść formularza BLIK.
v2.7.8
Poprawiono rozpoznawanie języka w transakcjach kartą
v2.7.7
Dostosowano opcjonalność niektórych parametrów adresowych przy tworzeniu transakcji BLIK
v2.7.6
Dodano możliwość zapisania karty przez wszystkich użytkowników.
Poprawiono funkcjonalność formularza kartowego.
v2.7.5
Poprawiono sposób walidacji formularza płatności kartą.
v2.7.4
Dodano przechowywanie informacji o języku płatnika w module Tpay credit cards, w celu poprawnego informowania o zwrotach.
v2.7.3
Poprawiono wyświetlanie bramki kartowej i obsługę skryptów
v2.7.2
Dodano obsługę subskrypcji z dodatkiem Woocommerce Subscriptions
Dodano opcję wyświetlania kanałów księgujących wyłącznie online lub wszystkich
v2.7.01
Naprawiono błąd wywoływania klasy WC_Shipping
v2.7.0
Dodano możliwość zapisywania kart płatniczych do ponownego użytku w module tpay.com credit cards dla zalogowanych użytkowników.
Dodano mechanizm ponawiania płatności po nieudanej płatności nową lub zapisaną kartą.
Dodano obsługę usuwania wyrejestrowanych kart.
Dodano obszerniejsze logowanie czynności klienta w szczegółach zamówienia.
Dodano obsługę zwrotów wykonywanych w Panelu Odbiorcy Płatności.
v2.6.55
Poprawka logowania powiadomień o wpłatach
v2.6.54
Poprawka kalkulacji wartości koszyka dla starszych wersji woocommerce
v2.6.53
Aktualizacja płatności ratami
v2.6.52
Poprawiono błąd działania wtyczki spowodowany przestarzałymi danymi konfiguracyjnymi metod wysyłki.
v2.6.51
Usunięto zdublowane wyświetlanie informacji o prowizji za płatność
v2.6.50
Wyświetlanie bramki płatności kartami w języku polskim i angielskim
v2.6.47
Poprawiono kompatybilność z edytorem menu
v2.6.46
Naprawiono konflikt z przestarzałą metodą wysyłki local_pickup
v2.6.45
Poprawki kodu źródłowego
v2.6.44
Dodano zabezpieczenie przed błędami pobierania listy dostępnych metod wysyłki
v2.6.43
Dodano logowanie błędów BLIK
Dodano automatycznie przekierowanie do panelu transakcyjnego w przypadku błędnego kodu blik
Dodano zabezpieczenie przed błędami pobierania listy dostępnych metod wysyłki
v2.6.42
Zabezpieczenie przed wyjątkami w pobieraniu metod wysyłki
v2.6.41
Zabezpieczenie przed wyjątkami w pobieraniu metod wysyłki
Rozpoznawanie języka w opisie transakcji
v2.6.4
Aktualizacja kompatybilności ze starszymi wersjami Woocommerce
v2.6.3
Dodano parowanie metody płatności z metodą wysyłki.
Zaktualizowano bibliotekę szyfrującą dane karty.
v2.6.2
Poprawiony błędny link w formularzu płatności kartą.
v.2.6.1
Poprawiono błąd zwracany przy odrzuconej płatności kartowej bez 3DS.
v2.6.0
Poprawiono przekierowania na uszkodzone adresy URL stron sukcesu i niepowodzenia.
v2.5.9
Zmodyfikowano wyświetlanie kanału Raty
Usunięto nadpisanie CSS zaznaczenia tekstu
v2.5.8
Zmodyfikowano zarządzanie stanem magazynowym. Zmieniono stronę błędu na adres ponawiania zapłaty.
v2.5.7
Wyłączono wyświetlanie regulaminu w trybie przekierowania do panelu transakcyjnego.
v2.5.6
Rozwiązano problem z autokorektą wprowadzanego numeru karty kredytowej
v2.5.5
Dodano możliwość wyłączenia walidacji IP serwera powiadomień
v2.5.4
Rozwiązano konflikt z PHP 7.1
v2.5.3
Dodano zbieranie statystyk modułu
v2.5.2
Poprawiono nadpisywanie wyglądu pól formularza płatności.
v2.5.1
Dodano opcję automatycznego oznaczania zamówienia jako zrealizowane
v2.5.0
Dodano obsługę zwrotów z panelu widoku zamówienia. Aby opcja zwrotów była aktywna i działała poprawnie, należy wygenerować i wprowadzić klucz API w ustawienia wtyczki zgodnie z instrukcją https://secure.tpay.com/integration/instruction/64

== Frequently Asked Questions ==
Feel free to contact us on info@tpay.com
== Upgrade Notice ==
= 2.5.0 =
Dodano obsługę zwrotów z panelu widoku zamówienia. Aby opcja zwrotów była aktywna i działała poprawnie, należy wygenerować i wprowadzić klucz API w ustawienia wtyczki zgodnie z instrukcją https://secure.tpay.com/integration/instruction/64
== Screenshots ==
no screenshots
