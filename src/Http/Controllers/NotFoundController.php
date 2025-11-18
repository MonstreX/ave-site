<?php

namespace Monstrex\AveSite\Http\Controllers;

use Illuminate\Routing\Controller;
use Monstrex\AveSite\Traits\PageTrait;
use Monstrex\AveSite\Facades\AveData;
use Monstrex\AveSite\Facades\AvePage;

class NotFoundController extends Controller
{
    use PageTrait;

    public function __invoke()
    {
        $slug = config('ave-site.not_found_page');

        if (!$slug) {
            abort(404);
        }

        $page = AveData::findFirst($slug, null, false);

        if (!$page) {
            abort(404);
        }

        AvePage::setResponseCode(404);

        $this->setPage($page);
        $this->create($slug, null, false);

        $layout = config('ave-site.template') . '.' . config('ave-site.template_layout', 'layouts.main');

        return $this->view($layout);
    }
}
