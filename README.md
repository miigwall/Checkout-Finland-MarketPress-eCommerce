# MarketPress eCommerce Checkout Finland (beta) -maksutapa
Mahdollistaa MarketPress eCommerce -verkkokaupasta tehtyjen ostoksien maksamisen Checkout.fi -maksutavalla.

## Asennus
1. Kopioi <b>checkout_finland.php</b> sijaintiin<br /> [WP_ROOT]/wp-content/plugins/wordpress-ecommerce/marketpress-includes/plugins-gateway/
2. Siirry Products -> Store Settings -> Payments 
3. Ota käyttöön "Checkout.fi" -maksutapa
4. Määritä alapuolelle ilmestyviin asetuksiin kauppiastunnus, turva-avain ja keskimääräinen toimitusaika

Testitunnukset:
<pre>
Kauppiastunnus: 375917 
Turva-avain: SAIPPUAKAUPPIAS
</pre>
Maksutapaa luodessa moduuli ottaa yhteyttä Checkout:n palvelimiin lähettääkseen tiedot maksutapahtumasta. Palvelimen tuleesallia yhteydet ulkopuolelle. Palvelimen PHP:n asetuksissa tulee olla sallittu allow_url_fopen. Lisäksi asennettuna tulee olla cURL ja PHP:n tarvitsemat cURL-kirjastot.

# MarketPress eCommerce Checkout Finland Payment Gateway (beta)
Pay via Checkout.fi when using MarketPress eCommerce WordPress plugin.

## Installation
1. Copy <b>checkout_finland.php</b> to<br /> [WP_ROOT]/wp-content/plugins/wordpress-ecommerce/marketpress-includes/plugins-gateway/
2. Go to Products -> Store Settings -> Payments 
3. Enable "Checkout.fi"
4. Set merchant id (kauppiastunnus), security key (turva-avain) and delivery time (toimitusaika)

You can test the installation by using the following merchant id and security key
<pre>
Merchant id: 375917
Security key: SAIPPUAKAUPPIAS
</pre>
When creating a payment this plugin makes a request to Checkout Finland servers. If not, check that firewall is not blocking outgoing connections. Check also that you have allow_url_fopen allowed in your PHP settings and that your PHP installation can use cURL to make the requests.
