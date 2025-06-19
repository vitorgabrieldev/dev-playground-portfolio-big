@component('mail::layout')
	{{-- Header --}}
	@slot('header')
		@component('mail::header', ['url' => config('app.url_site')])
			<img src="{{ asset('images/frontend/logos/mail-logo.png') }}" alt="{{ config('app.name_short') }}">
		@endcomponent
	@endslot

	{{-- Body --}}
	{{ $slot }}

	{{-- Subcopy --}}
	@isset($subcopy)
		@slot('subcopy')
			@component('mail::subcopy')
				{{ $subcopy }}
			@endcomponent
		@endslot
	@endisset

	{{-- Footer --}}
	@slot('footer')
		@component('mail::footer')
			© {{ date('Y') }} {{ config('app.name_short') }}. @lang('All rights reserved.')
		@endcomponent
	@endslot
@endcomponent
