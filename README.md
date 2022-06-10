``` console
npm install twexxor-merkle-root-hasher
```
``` javascript
const twexxorMerkleRootHasher = require('twexxor-merkle-root-hasher');
twexxorMerkleRootHasher([0], 'sha256'); // '5feceb66ffc86f38d952786c6d696c79c2dbc239dd4e91b46729d73a27fb57e9'
twexxorMerkleRootHasher([0, 0], 'sha256'); // '5feceb66ffc86f38d952786c6d696c79c2dbc239dd4e91b46729d73a27fb57e9'
twexxorMerkleRootHasher([0, 1, 2], 'sha256'); // 'dda582814949d19c78c7a6cd5fd4732912a52f2275b50014992112584b284074'
twexxorMerkleRootHasher([0, 1, 2]); // false
twexxorMerkleRootHasher('twexxor', 'sha256'); // false
```
