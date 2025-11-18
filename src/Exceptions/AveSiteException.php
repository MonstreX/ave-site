<?php

namespace Monstrex\AveSite\Exceptions;

use Exception;
use Monstrex\AveSite\Traits\PageTrait;

class AveSiteException extends Exception
{
    use PageTrait;

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        //
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        $statusCode = $this->code;

        $slugMap = config('ave-site.error_pages', []);
        $slug = $slugMap[$statusCode] ?? ($statusCode == '404'
                ? config('ave-site.not_found_page', 'error-404')
                : null);

        if ($slug) {
            try {
                $this->create($slug, config('ave-site.default_model_table', 'ave_site_pages'), false);

                return response($this->view(), (int) $statusCode);
            } catch (\Exception $e) {
                // Fall through to plain responses
            }
        }

        $message = match ($statusCode) {
            403 => __('ave-site::errors.access_denied'),
            404 => __('ave-site::errors.page_not_found'),
            default => __('ave-site::errors.undefined'),
        };

        return response($message, (int) $statusCode);
    }
}
