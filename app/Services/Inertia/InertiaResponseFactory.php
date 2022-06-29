<?php

namespace App\Services\Inertia;

use App\Http\Kernel;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Request;
use Inertia\ResponseFactory;
use Symfony\Component\HttpFoundation\Response;

class InertiaResponseFactory extends ResponseFactory
{
    /**
     * @var Collection
     */
    public Collection $modals;

    public function __construct()
    {
        $this->modals = collect([]);
    }

    /**
     * @param string $component
     * @param array $props
     * @return Response
     */
    public function render(string $component, $props = []): InertiaResponse
    {
        if ($props instanceof Arrayable) {
            $props = $props->toArray();
        }

        return new InertiaResponse(
            $component,
            array_merge($this->sharedProps, $props),
            $this->rootView,
            $this->getVersion()
        );
    }

    /**
     * @param $modal
     * @param array $params
     * @return $this
     */
    public function modal($modal, array $params = []): static
    {
        $this->modals->prepend(collect($params)->put('component', $modal));

        return $this;
    }

    /**
     * @param $name
     * @param $params
     * @return JsonResponse
     */
    public function basePageRoute($name, $params): JsonResponse|Response
    {
        /**
         * Create new request.
         */
        $request = request();

        /**
         * Get current model.
         */
        $currentModal = $this->modals->first();

        /**
         * Set current modal redirect page.
         */
        $currentModal->put('redirect', route($name, $params));

        /**
         * Set current modal url.
         */
        $currentModal->put('url', $request->getRequestUri());

        /**
         * Share all modal\s data.
         */
        $this->share('modals', $this->modals);

        /**
         * Get app kernel.
         */
        $kernel = App::make(Kernel::class);

        /**
         * Set response headers
         */
        $headers = $request->headers->all();
        $headers['Accept'] = 'text/html, application/xhtml+xml';
        $headers['X-Requested-With'] = 'XMLHttpRequest';
//            $headers['X-Inertia'] = true;
        $headers['X-Inertia-Version'] = inertia()->getVersion();
        $headers['X-inertia-Modal'] = $this->modals->last()['url'];

        /**
         * Add base page route.
         */
        $inertiaRequest = Request::create(route($name, $params), 'GET');
        $inertiaRequest->headers->replace($headers);

        App::instance('request', $request);
        Facade::clearResolvedInstance('request');

        /**
         * Fill modal url.
         * @var JsonResponse $response
         */
        $response = $kernel->handle($inertiaRequest);

        $content = json_decode($response->getContent());

        if ($content) {
            $content->url = $request->getRequestUri();
            $response->setContent(json_encode($content));
        }


        return $response;
    }
}
