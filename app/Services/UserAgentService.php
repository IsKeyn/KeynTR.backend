<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Jenssegers\Agent\Agent;

class UserAgentService
{
    public static function setData(Request $request, $entity) {
        $userAgentData = array();
        $userAgentData = Arr::add($userAgentData, 'ip', $request->ip());

        if ($httpReferer = $request->get('referer')) {
            $userAgentData = Arr::add($userAgentData, 'http_referer', $httpReferer);
        }

        if ($userAgent = $request->header('User-Agent')) {
            $userAgentData = Arr::add($userAgentData, 'user_agent', $userAgent);

            $agent = new Agent();
            $agent->setUserAgent($userAgent);

            if ($device = $agent->device())
                $userAgentData = Arr::add($userAgentData, 'device', $device);

            if ($platform = $agent->platform() ) {
                if ($version = $agent->version($platform)) {
                    $platform .= ' ' . $version;
                }

                $userAgentData = Arr::add($userAgentData, 'platform', $platform);
            }

            if ($browser = $agent->browser()) {
                if ($version = $agent->version($browser)) {
                    $browser .= ' ' . $version;
                }

                $userAgentData = Arr::add($userAgentData, 'browser', $browser);
            }

            if ($agent->isRobot()) {
                $robot = 'Robot';

                if ($robotName = $agent->robot()) {
                    $robot .= '' . $robotName;
                }

                $userAgentData = Arr::add($userAgentData, 'robot', $robot);
            }
        }

        $entity->userAgentData()->create($userAgentData);
    }
}
