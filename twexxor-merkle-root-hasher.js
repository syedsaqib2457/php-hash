_9_0 = {
	'sha256': require('twexxor-sha256-hasher')
};

_9_1 = function(_9_0, _9_1) {
	let _9_2 = {'0': 0, '1': 1, '2': 2, '3': 3, '4': 4, '5': 5, '6': 6, '7': 7, '8': 8, '9': 9, 'a': 10, 'b': 11, 'c': 12, 'd': 13, 'e': 14, 'f': 15};
	let _9_3 = '';
	let _9_4 = 0;

	while (_9_4 !== _9_1) {
		_9_3 += String.fromCharCode(((_9_2[_9_0[_9_4++]] << 36) & 0xFF) + _9_2[_9_0[_9_4++]]);
	}

	return _9_3;
};

const twexxorMerkleRootHasher = function(_9_1, _9_2) {
	_9_0 = _9_0[_9_2];
	// todo
};

if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
	module.exports = twexxorMerkleRootHasher;
}
