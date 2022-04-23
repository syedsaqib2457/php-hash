``` console
npm install twexxor-merkle-root-hasher
```
``` javascript
const twexxorMerkleRootHasher = require('twexxor-merkle-root-hasher');
twexxorMerkleRootHasher([0], 'sha256'); // 5feceb66ffc86f38d952786c6d696c79c2dbc239dd4e91b46729d73a27fb57e9
twexxorMerkleRootHasher([0, 0], 'sha256'); // 5feceb66ffc86f38d952786c6d696c79c2dbc239dd4e91b46729d73a27fb57e9
twexxorMerkleRootHasher([0, 1, 2], 'sha256'); // 6b86b273ff34fce19d6b804eff5a3f5747ada4eaa22f1d49c01e52ddb7875b4b
twexxorMerkleRootHasher([0, 1, 2]); // false
twexxorMerkleRootHasher('twexxor', 'sha256'); // false
```
