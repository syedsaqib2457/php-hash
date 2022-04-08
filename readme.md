In development.

Hash merkle roots with JavaScript.

The first input is an array of hexadecimal strings to hash.

The second input is the algorithm to hash with.

The supported hashing algorithm is `sha256`.

The third input is the number of hashes during each compression.

**Node.js**
``` console
npm install twexxor-merkle-root-hasher
```
``` javascript
const twexxorMerkleRootHasher = require('twexxor-merkle-root-hasher');
console.log(twexxorMerkleRootHasher(['abc', 'def', '012', '345', '678', '910'], 'sha256', 2));
```
**Web Browser**
``` console
git clone https://github.com/twexxor/javascript-merkle-root-hasher.git
```
``` html
<script src="twexxor-merkle-root-hasher.js" type="text/javascript"></script>
<script type="text/javascript">console.log(twexxorMerkleRootHasher(['abc', 'def', '012', '345', '678', '910'], 'sha256', 2));</script>
```
