# WeatherInfo

<p align="center">
  <img src="/pict/WeatherInfo.jpg?raw=true" width="150px" alt="Sublime's custom image"/>
</p>

**Main Systems**

weather_info.php (System to get the current weather information: OpenWeatherMap API)  
my_module/weather_table_creator.php (Table creator for MySQL)

**Self-Made Modules**

common/header.php  
common/db_connect.php  
common/timezone.php  
my_module/openweathermap.php  
my_module/weather_table_creator.php  
css/weather_info_css.php

**.gitignore**

config/db_info.php  
config/opn_wm_api_key.php

## Description

`my_module/weather_table_creator.php` is an executable module for creating MySQL table.  
`weather_info.php` is a system to get the current weather information by using OpenWeatherMap API.

***DEMO:***

From 05:00 to 16:59 (JST)

![DEMO Pict 1](/pict/05001659JST.png)


From 17:00 to 28:59 (JST)

![DEMO Pict 2](/pict/17002859JST.png)


## Features

Register the latest weather information in the DB and display it on your web browser.

## Requirement

macOS 10.11.6 or later  
Apache 2.4.28 or later  
MySQL 5.7.25 or later  
PHP 5.5.38 or later

## Usage

**Start Apache.**

```bash
sudo apachectl start
```

**Enter the following url in your web browser.**

`localhost/WeatherInfo/weather_info.php`

## Setup 1 - MySQL Commands. -

**Create a database.**

```mysql
create database weather;
```

**Check the value of password policy.**

```mysql
show variables like 'validate_password%';
```

**Change the value of password policy.**

```mysql
set global validate_password_policy=LOW;
```

**(MySQL 5.7) User Authorization (Set USERNAME, HOST, PASSWORD) .**

```mysql
grant all on weather.* to 'USERNAME'@'HOST' identified by 'PASSWORD';
```

**(MySQL 8) User Authorization (Set USERNAME, HOST, PASSWORD) .**

```mysql
create user 'USERNAME'@'HOST' identified by 'PASSWORD' with grant option;
grant all on weather.* to 'USERNAME'@'HOST';
flush privileges;
```

## Setup 2 - File creation and parameter settings. -

**Check the location of "DocumentRoot".**

```bash
sudo git clone https://github.com/gitmori/WeatherInfo /Library/WebServer/Documents/WeatherInfo
sudo mkdir /Library/WebServer/Documents/WeatherInfo/config
sudo touch /Library/WebServer/Documents/WeatherInfo/config/{db_info.php,opn_wm_api_key.php}
```

**Write the following PHP code in `config/db_info.php` and save it. (Set USERNAME, HOST and PASSWORD.)**

```php
<?php
# DB接続情報を返す関数
function db_info() {
    $ip = 'HOST';
    $user = 'USERNAME';
    $pass = 'PASSWORD';

    return [$ip, $user, $pass];
}
```

**Write the following PHP code in `config/opn_wm_api_key.php` and save it. (Set API Key.)**

```php
<?php
# OpenWeatherMapのAPI Keyを返す関数
function opn_wm_api_key() {
    $api_key = 'API Key';
    return $api_key;
}
```

**Run "weather_table_creator.php".**

```bash
php /Library/WebServer/Documents/WeatherInfo/my_module/weather_table_creator.php
```

## Note

All tools and modules contain Japanese comments.  

## Author

Yuki Moriya ([gitmori](https://github.com/gitmori/))  
ym19820219@gmail.com

## Licence

Copyright (c) 2021 Yuki Moriya  
This software is released under the MIT License, see LICENSE.
