let _9_0 = {
	'sha256': require('twexxor-sha256-hasher')
};

const twexxorMerkleRootHasher = function(_9_2, _9_3) {
	_9_0 = _9_0[_9_3];
	let _9_4 = _9_2.length;
	let _9_5 = 0x7FFFFFFF - _9_4;
	_9_5 += '';

	if (_9_5[0] === '-') {
		return false;
	}

	if (_9_4 === 1) {
		_9_2[_9_4] = _9_2[_9_4++ - 1];
	}

	_9_5 = 0;

	while (_9_4 !== 2) {
		if ((_9_4 & 1) === 1) {
			_9_2[_9_4] = _9_2[_9_4++ - 1];
		}

		_9_5 = 0;

		while (_9_5 !== _9_4) {
			_9_2[_9_5 >> 1] = _9_0(_9_2[_9_5++] + _9_2[_9_5++]);
		}

		_9_4 >>= 1;
	}

	return _9_0(_9_2[0] + _9_2[1]);
};

if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
	module.exports = twexxorMerkleRootHasher;
}
