# Social Proof for Magento 2

Proofo is Social Proof app for Magento 2 which helps merchants increase conversion rate 30%, trust, credibility, and sales with real-time social proof.

## How to install

### 1. Install via composer (recommend)
We recommend you to install Avada_Proofo module via composer. It is easy to install, update and maintaince.
Run the following command in Magento 2 root folder:

#### 1.1 Install

```
composer require avada/module-proofo
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

#### 1.2 Upgrade

```
composer update avada/module-proofo
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

Run compile if your store in Product mode:

```
php bin/magento setup:di:compile
```
### 2. Copy and paste
If you don't want to install via composer, you can use this way. 

- Download [the latest version here](https://github.com/Proofo/proofo-magento-2/archive/master.zip) 
- Extract `master.zip` file to `app/code/Avada/Proofo` ; You should create a folder path `app/code/Avada/Proofo` if not exist.
- Go to Magento root folder and run upgrade command line to install `Avada_Proofo`:

## How to use

See our official documentation here: https://help.avada.io/en-us/category/proofo-social-proof-16ahtap/

## Information
- Website: https://proofo.io
- Support: https://avada.io/contact.html
