<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| JWT Secret Key
|--------------------------------------------------------------------------
|
| Dipakai untuk sign & verify token di Auth::login_post() dan JwtAuth library.
|
*/
$config['jwt_secret_key'] = 'ae99d88c06ca4408e031e4023cbae3d687b583e33a8f3ec259e9a90300e098e1';

/*
|--------------------------------------------------------------------------
| JWT Expiration
|--------------------------------------------------------------------------
|
| Masa berlaku token dalam detik.
|
*/
$config['jwt_expire_seconds'] = 86400;
