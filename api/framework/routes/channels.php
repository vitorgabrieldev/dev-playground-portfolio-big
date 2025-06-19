<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('app.user.{uuid}', function ($user, $uuid) {
	return $user->uuid === $uuid;
});

Broadcast::channel('profissionais.{uuid}', function ($uuid) {
	return 'hello world';
});
