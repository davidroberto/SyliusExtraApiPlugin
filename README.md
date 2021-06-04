A plugin that extends the Sylius new API: find products by slug, search for products, add stripe gateway...

This plugin is mandatory if you use the sylius next.js boilerplate

## Install and configure the Sylius API

- Install Sylius (https://docs.sylius.com/en/1.9/book/installation/installation.html) 
- Install the Sylius Extra API Plugin has to be installed in order to use the theme:

```
composer require davidroberto/sylius-extra-api-plugin
```

- If you want to use Stripe, create in your Stripe account a new payment_intent.succeeded hook, calling this URL: 
https://yourSyliusAPIDomainName/api/v2/shop/payments/stripe/notify/success
- Add this env variables into your .env.local file:

```
# your stripe secret key
STRIPE_SECRET_KEY=sk_test_51IWnwaGhkxw8ABpLx60ZYzWcq2ffcxLkDaFPtZULJtBDyjQgOnaTHABSCzzIrbEL34EnJj5eVPRZBDAjDC4mpTaV00KAZYhe3n
# your stripe payment_intent.succeeded webhook secret key
STRIPE_SUCCESS_ENDPOINT_SECRET_KEY=whsec_3jwQgifKzj8TKoOQGwASPdEdPbPvgxvq
# the Next.js front end URL
CLIENT_URL=
# the front end url for the stripe payment success (by default "https://yourNextBoilerPlateThemeDomainName/cart/confirmation")
CLIENT_URL_PAYMENT_SUCCESS=
# the front end url for the stripe payment failure (by default "https://yourNextBoilerPlateThemeDomainName/cart/failed")
CLIENT_URL_PAYMENT_FAILED=
```
- create a davidroberto_sylius_extra_plugin.yaml file in your config/packages file and import the plugin config file in it:

```
imports:
    - { resource: "@DavidRobertoSyliusExtraApiPlugin/Resources/app/config/config.yml" }
```

- configure the nelmio cors bundle to match your next js frontend url
- In your admin, in the "channel", set this values:
-- ignore shipping selection step where only one shipping method exists: TRUE
-- ignore payment selection step where only one payment method exists: TRUE
-- the account verification is mandatory: false

- In your admin, foreach product, set the variant selection method to "Variant choice"

- configure the nelmio cors bundle to match your next js frontend url

Important:
- The theme don't work well yet with taxons slug containing slashes, so remove them if you want to test the theme.
- Depending on yout nelmio cors configuration, the theme might not be able to perform the fetch request if you use https for your local sylius api url. You can fix it using http (only for localhost).


## Install and configure the next.js boilerplate theme

- clone the Sylius next.js boilerplate theme: https://github.com/davidroberto/sylius-next-boilerplate-theme
- fill the .env variables into a .env.local file
```
# Your Sylius API URL. Exemple: "https://yourSyliusAPIDomainName/api/v2/"
NEXT_PUBLIC_API_RESOURCE_BASE_URL=
# Your Sylius API base URL (without the /api/v2). Exemple: "https://yourSyliusAPIDomainName")
NEXT_PUBLIC_API_PUBLIC_URL=
# Your Sylius API Hostname (without the protocol). Exemple: "yourSyliusAPIDomainName"
API_HOST_NAME=
# your stripe public key
NEXT_PUBLIC_STRIPE_PUBLIC_KEY=
# Your Mailchimp Newsletter Form Action URL
MAILCHIMP_URL=
```
- install the node modules: 
```
yarn install
```
- change the default locale in lib/config/locales.ts
- launch the local dev server:

```
yarn dev
```

- it's done ! You can browse you shop!
