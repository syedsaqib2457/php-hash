
This is a fast and cryptographically-secure hashing algorithm with no known collisions.

It's written in PHP and outputs persistent 32-byte hexadecimal digests from variable-length `string` payloads.

The return value is a `string` hexadecimal number digest with a fixed character length of `64`.

If the argument isn't a `string`, the return value is an empty `string`.

``` php
require_once('liamloads-hash.php');

print_r(liamloadsHash('0123456789'));
// 'f06dcc113fbffd00fe814d62e93b91c0ab0ddb5505abaac68f5209c1185ad96c'

print_r(liamloadsHash('◯'));
// '6e9e92171e9b118b63f7fd2c0d7242581253047d198478c4a91cc7138ce670f4'

print_r(liamloadsHash(''));
// '9ad3de7c1e4478fd872e6340de0ac2db4bae21b1bcada7cf92b52adf24601c63'

console.log(liamloadsHash(['◯']));
// ''
```
