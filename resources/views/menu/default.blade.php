{{-- Default menu template --}}
@if(!empty($menu))
    <nav class="ave-site-menu">
        {!! renderMenuItems($menu, 0) !!}
    </nav>
@endif

@php
/**
 * Recursive function to render menu items
 *
 * @param array $items Menu items
 * @param int $level Nesting level
 * @return string Rendered HTML
 */
function renderMenuItems(array $items, int $level = 0): string
{
    if (empty($items)) {
        return '';
    }

    $html = '<ul class="menu-level-' . $level . '">';

    foreach ($items as $item) {
        $url = $item['slug'] ?? '#';
        $title = $item['title'] ?? 'Untitled';
        $hasChildren = !empty($item['children']);

        // Build full URL from slug
        $fullUrl = '/' . ltrim($url, '/');

        // Check if current page
        $isActive = request()->is(ltrim($url, '/')) || request()->is(ltrim($url, '/') . '/*');
        $activeClass = $isActive ? ' active' : '';

        $html .= '<li class="menu-item' . ($hasChildren ? ' has-children' : '') . $activeClass . '">';
        $html .= '<a href="' . e($fullUrl) . '">' . e($title) . '</a>';

        if ($hasChildren) {
            $html .= renderMenuItems($item['children'], $level + 1);
        }

        $html .= '</li>';
    }

    $html .= '</ul>';

    return $html;
}
@endphp
