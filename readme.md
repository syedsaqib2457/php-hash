In development.

Hash merkle roots with JavaScript.

The first input is an array of strings to hash and the second input is the algorithm to hash with.

The supported hashing algorithm is `sha256`.

**Node.js**
``` console
npm install twexxor-merkle-root-hasher
```
``` javascript
const twexxorMerkleRootHasher = require('twexxor-merkle-root-hasher');
console.log(twexxorMerkleRootHasher(['abcdefghijklmn', 'opqrstuvwxyz'], 'sha256'));
```
**Web Browser**
``` console
git clone https://github.com/twexxor/javascript-merkle-root-hasher.git
```
``` html
<script src="twexxor-merkle-root-hasher.js" type="text/javascript"></script>
<script type="text/javascript">console.log(twexxorMerkleRootHasher(['abcdefghijklmn', 'opqrstuvwxyz'], 'sha256'));</script>
```
