<?php

namespace Monstrex\AveSite\Http\Controllers;

use Illuminate\Http\Response;
use Monstrex\AveSite\Services\PageService;

class PageController
{
    protected PageService $pageService;

    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    /**
     * Display a page by slug
     *
     * @param string $slug
     * @return Response
     */
    public function show(string $slug): Response
    {
        try {
            $html = $this->pageService
                ->create($slug)
                ->view();

            return response($html);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
    }
}
