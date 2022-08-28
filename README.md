#### About
LiamLoads is a fast cryptographic hashing algorithm in PHP with no known collisions.

It outputs persistent 32-byte hexadecimal digests from variable-length `string` message payloads.

The return value is a `string` hexadecimal number digest with a fixed character length of `64`.

#### Installation
``` console
git clone https://github.com/liamloads/php-hash.git
```

#### Usage
``` php
<?php
    require_once('liamloads.php');

    echo LiamLoads('0123456789');
    // 'f06dcc113fbffd00fe814d62e93b91c0ab0ddb5505abaac68f5209c1185ad96c'

    echo LiamLoads('◯');
    // '6e9e92171e9b118b63f7fd2c0d7242581253047d198478c4a91cc7138ce670f4'

    echo LiamLoads('');
    // '9ad3de7c1e4478fd872e6340de0ac2db4bae21b1bcada7cf92b52adf24601c63'

    echo LiamLoads(array('◯'));
    // ''
?>
```
