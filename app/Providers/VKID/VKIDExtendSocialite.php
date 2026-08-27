<?php

namespace App\Providers\VKID;

use SocialiteProviders\Manager\SocialiteWasCalled;

class VKIDExtendSocialite
{
    public function handle(SocialiteWasCalled $event): void
    {
        $event->extendSocialite(
            'vkid',
            Provider::class
        );
    }
}
