DsAuth
===========

dsauth is an oauth(and openid)-only auth package for fuelphp depends on [fuel-ninjauth](https://github.com/happyninjas/fuel-ninjauth)

## Installation

### setup packages
place these packages under`fuel/packages` folder

 + fuel-dsauth
 + fuel-ninjauth
 + fuel-oauth
 + fuel-oauth2


### setup config 

```php
		'packages'  => array(
			// packages…
			'ninjauth',
			'oauth',
			'oauth2',
			'dsauth',
		),
```

### setup NinjAuth config

change adapter to DsAuth

```php
	'adapter' => 'DsAuth',
```



### run migration

make authentications table for ninjauth

```
oil refine migrate --packages=ninjauth
```

make users table for dsauth

```
oil refine migrate --packages=dsauth
```


## Usage

create `Controller_Auth` extends `\DsAuth\Controller`




## Configuration


```php
<?php
return array (
	'db_connection' => null,
	'login_hash_salt' => 'dassaiauthentication',
	// table columns to select when user logged in
	'table_columns' => array('*'),
	// user table name
	'table_name' => 'users',
	// if true, always show confirm form for  new user
	'always_confrim_username' => false,
	// if true, check same user name for new user
	'allow_duplicated_username' => false,
	'auto_modify_userinfo' => true,
);
```