# Crypto Trading app

This application allows you to manage a virtual portfolio of cryptocurrencies using data from various cryptocurrency
APIs. You can view the top cryptocurrencies, buy and sell them using virtual money, and track your transaction history.

## Getting Started

### Prerequisites

- PHP >= 7.4
- Composer (https://getcomposer.org/) for dependency management
- API keys from supported cryptocurrency APIs:
    - CoinGecko (https://coingecko.com/) (default setup)
    - CoinMarketCap (https://coinmarketcap.com/)

### Installation

Clone the repository:

``` sh 
git clone https://github.com/ievasinke/crypto-trade-v4.git
 ```  

Navigate to the project directory:

``` sh 
cd crypto-trade-v4
  ```  

Install dependencies using Composer:

``` sh
composer install
  ```  

Create a `.env` file in the root directory from `.env.example` file and add your API keys:

``` sh 
cp .env.example .env
 ```  

Then, edit the `.env` file to include your API keys:

``` COINGECKO_API_KEY=your_api_key_here ```  
``` CRYPTO_API_KEY=your_api_key_here ```

### Usage

1. Create Database Schema

This project uses an SQLite database. To create the database schema and set up initial users, run the following script:

```sh 
php setup.php
 ```

2. Run the application

To start the application, run:

``` sh
php index.php 
```  

### Database

This application uses an SQLite database for storing user and transaction information. The database file is located
at `storage/database.sqlite`.