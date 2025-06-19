/**
 * Address
 *
 * @returns {string}
 */
export function concat_address_2(item) {
	let address = [];

	let streetNumberComplement = '';

	if( item.street )
	{
		streetNumberComplement += item.street;
	}

	if( item.number )
	{
		streetNumberComplement += (item.street ? ', ' : '') + item.number;
	}

	if( item.complement )
	{
		streetNumberComplement += (streetNumberComplement ? ' ' : '') + item.complement;
	}

	if( streetNumberComplement )
	{
		address.push(streetNumberComplement);
	}

	if( item.district )
	{
		address.push(item.district);
	}

	if( item.zipcode )
	{
		address.push('CEP ' + item.zipcode);
	}

	if( item.city )
	{
		address.push(item.city);
	}

	if( item.state )
	{
		address.push(item.state);
	}

	return address.join(' - ');
}
