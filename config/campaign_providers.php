<?php

use App\Services\Campaigns\LogSmsProvider;
use App\Services\Campaigns\LogWhatsAppProvider;

/*
|--------------------------------------------------------------------------
| Campaign Message Provider Registry
|--------------------------------------------------------------------------
|
| Maps a campaign channel to the class implementing MessageProviderContract.
| Both default to a log provider - there is no real SMS/WhatsApp provider
| account, TRAI DLT template, or Meta-approved template in this environment.
| Swap the class here once real credentials exist; nothing else changes.
|
*/

return [
    'sms' => LogSmsProvider::class,
    'whatsapp' => LogWhatsAppProvider::class,
];
