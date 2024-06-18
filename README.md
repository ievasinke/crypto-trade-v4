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
```  git clone https://github.com/ievasinke/crypto-trade-v4.git  ```  
Navigate to the project directory:  
```  cd crypto-trade-v4  ```  
Install dependencies using Composer:  
``` composer install  ```  
Create a `.env` file in the root directory from `.env.example` file and add your API keys:  
``` COINGECKO_API_KEY=your_api_key_here ```  
``` CRYPTO_API_KEY=your_api_key_here ```

### Usage

#### Create Database Schema

To create database schema and create users in `setup.php`, use the following script:  
``` php setup.php ```

#### Run the application

To start the application, run:  
``` php index.php ```