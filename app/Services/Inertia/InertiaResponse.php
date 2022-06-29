<?php

namespace App\Services\Inertia;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response as ResponseFactory;
use Inertia\LazyProp;
use Inertia\Response;

class InertiaResponse extends Response
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $only = array_filter(explode(',', $request->header('X-Inertia-Partial-Data', '')));

        $props = ($only && $request->header('X-Inertia-Partial-Component') === $this->component)
            ? Arr::only($this->props, $only)
            : array_filter($this->props, static function ($prop) {
                return ! ($prop instanceof LazyProp);
            });

        $props = $this->resolvePropertyInstances($props, $request);

        /*
         * Fill page or modal url.
         */
        $url = $request->hasHeader('X-inertia-Modal') ?
            $request->header('X-inertia-Modal') : $request->getRequestUri();
        $page = [
            'component' => $this->component,
            'props' => $props,
            'url' => $request->getBaseUrl().$url,
            'version' => $this->version,
        ];

        if ($request->header('X-Inertia')) {
            return new JsonResponse($page, 200, [
                'Vary' => 'Accept',
                'X-Inertia' => 'true',
            ]);
        }

        return ResponseFactory::view($this->rootView, $this->viewData + ['page' => $page]);
    }

}