const _9_0 = {
	'sha256': [
		require('twexxor-sha256-hasher'),
		64
	]
};

const _9_1 = function(_9_0, _9_1) {
	const _9_2 = {'0': 0, '1': 16, '2': 32, '3': 48, '4': 64, '5': 80, '6': 96, '7': 112, '8': 128, '9': 144, 'a': 160, 'b': 176, 'c': 192, 'd': 208, 'e': 224, 'f': 240};
	const _9_3 = {'0': 0, '1': 1, '2': 2, '3': 3, '4': 4, '5': 5, '6': 6, '7': 7, '8': 8, '9': 9, 'a': 10, 'b': 11, 'c': 12, 'd': 13, 'e': 14, 'f': 15};
	let _9_4 = '';
	let _9_5 = 0;

	while (_9_5 !== _9_1) {
		_9_4 += String.fromCharCode(_9_2[_9_0[_9_5++]] + _9_3[_9_0[_9_5++]]);
	}

	return _9_4;
};

const twexxorMerkleRootHasher = function(_9_2, _9_3) {
	_9_0 = _9_0[_9_3];
	let _9_4 = _9_2.length;

	if (_9_4 === 1) {
		return _9_2[0];
	}

	let _9_5 = 0;

	while (_9_4 !== 2) {
		
	}

	// todo
};

if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
	module.exports = twexxorMerkleRootHasher;
}
