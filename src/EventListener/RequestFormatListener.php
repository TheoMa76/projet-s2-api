<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestFormatListener
{
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->getRequestFormat(null)) {
            $request->setRequestFormat('json');
        }
    }
}
