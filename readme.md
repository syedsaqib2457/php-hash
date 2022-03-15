## PHP SHA-256

Hash SHA-256 strings with PHP.

#### Requirements

- PHP version 4 or greater  

#### Usage

``` console
git clone https://github.com/twexxor/php-sha256.git
```

Include `twexxor-sha256.php` and hash a string with `twexxorSha256()`.

``` php
require_once('twexxor-sha256.php');
echo twexxorSha256('twexxor');
```

ASCII and Unicode string inputs are supported in binary, decimal or hexidecimal format.  
Responses are in hexidecimal string format.

``` console
c24ff22de19a7e809a6547045dba9519be05d92ef4ff0eb45559de884e6e717a
```
