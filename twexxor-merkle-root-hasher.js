let _9_0 = {
	'sha256': require('twexxor-sha256-hasher')
};

const twexxorMerkleRootHasher = function(_9_1, _9_2) {
	if (typeof _9_1 !== 'object' || typeof _9_2 === 'undefined') {
		return false;
	}

	let _9_3 = _9_0[_9_2];
	let _9_4 = _9_1.length;
	let _9_5 = 0x7FFFFFFF - _9_4;
	_9_5 += '';

	if (_9_5[0] === '-') {
		return false;
	}

	if (_9_4 === 1) {
		_9_1[_9_4] = _9_1[_9_4++ - 1];
	}

	_9_5 = 0;

	while (_9_4 !== 2) {
		if ((_9_4 & 1) === 1) {
			_9_1[_9_4] = _9_1[_9_4++ - 1];
		}

		_9_5 = 0;

		while (_9_5 !== _9_4) {
			_9_1[_9_5 >> 1] = _9_3(_9_1[_9_5++] + _9_1[_9_5++]);
		}

		_9_4 >>= 1;
	}

	return _9_3(_9_1[0] + _9_1[1]);
};

if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
	module.exports = twexxorMerkleRootHasher;
}
