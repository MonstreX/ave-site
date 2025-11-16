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

        // System Pages with IDs 1 and 2 should exist for 403/404 errors
        switch ($statusCode) {
            case '403':
                // Try to find 403 error page (slug: 'error-403' or id: 1)
                try {
                    $this->create('error-403', 'pages', false);
                } catch (\Exception $e) {
                    return response('Access Denied', 403);
                }
                break;

            case '404':
                // Try to find 404 error page (slug: 'error-404' or id: 2)
                try {
                    $this->create('error-404', 'pages', false);
                } catch (\Exception $e) {
                    return response('Not Found', 404);
                }
                break;
        }

        if ($statusCode == '403' || $statusCode == '404') {
            return response($this->view(), (int) $statusCode);
        }

        return response('Undefined Error', (int) $statusCode);
    }
}
