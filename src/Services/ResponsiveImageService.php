<?php

namespace Monstrex\AveSite\Services;

use Illuminate\Support\HtmlString;

class ResponsiveImageService
{
    protected DataService $dataService;

    /**
     * Default breakpoint multipliers for srcset generation
     * Based on common device pixel ratios and responsive patterns
     */
    protected array $defaultMultipliers = [0.5, 1, 1.5, 2];

    /**
     * Default image format
     */
    protected string $defaultFormat = 'webp';

    /**
     * Default image quality
     */
    protected int $defaultQuality = 80;

    public function __construct(DataService $dataService)
    {
        $this->dataService = $dataService;
    }

    /**
     * Generate responsive <img> tag with srcset
     *
     * @param string $src Image source URL
     * @param int $width Base width
     * @param int|null $height Base height (null for proportional)
     * @param array $options Additional options
     * @return HtmlString
     */
    public function image(string $src, int $width, ?int $height = null, array $options = []): HtmlString
    {
        $format = $options['format'] ?? $this->defaultFormat;
        $quality = $options['quality'] ?? $this->defaultQuality;
        $alt = $options['alt'] ?? '';
        $class = $options['class'] ?? '';
        $lazy = $options['lazy'] ?? true;
        $sizes = $options['sizes'] ?? $this->generateDefaultSizes($width);
        $srcsetWidths = $options['srcset'] ?? $this->generateSrcsetWidths($width);

        // Generate main src
        $mainSrc = $this->dataService->getImageOrCreate($src, $width, $height, $format, $quality);

        // Generate srcset
        $srcset = $this->generateSrcset($src, $width, $height, $srcsetWidths, $format, $quality);

        // Build attributes
        $attrs = [
            'src' => $mainSrc,
            'srcset' => $srcset,
            'sizes' => $sizes,
            'width' => $width,
            'alt' => $alt,
        ];

        if ($height) {
            $attrs['height'] = $height;
        }

        if ($lazy) {
            $attrs['loading'] = 'lazy';
        }

        if ($class) {
            $attrs['class'] = $class;
        }

        // Add custom attributes
        foreach ($options as $key => $value) {
            if (!in_array($key, ['format', 'quality', 'alt', 'class', 'lazy', 'sizes', 'srcset'])) {
                $attrs[$key] = $value;
            }
        }

        return new HtmlString($this->buildTag('img', $attrs));
    }

    /**
     * Generate responsive <picture> tag with WebP and fallback
     *
     * @param string $src Image source URL
     * @param int $width Base width
     * @param int|null $height Base height (null for proportional)
     * @param array $options Additional options
     * @return HtmlString
     */
    public function picture(string $src, int $width, ?int $height = null, array $options = []): HtmlString
    {
        $quality = $options['quality'] ?? $this->defaultQuality;
        $alt = $options['alt'] ?? '';
        $class = $options['class'] ?? '';
        $lazy = $options['lazy'] ?? true;
        $sizes = $options['sizes'] ?? $this->generateDefaultSizes($width);
        $srcsetWidths = $options['srcset'] ?? $this->generateSrcsetWidths($width);
        $breakpoints = $options['breakpoints'] ?? [];
        $formats = $options['formats'] ?? ['webp'];
        $fallbackFormat = $options['fallback'] ?? $this->detectOriginalFormat($src);

        $html = "<picture>\n";

        // Art direction sources (different crops for different screens)
        foreach ($breakpoints as $bp) {
            $bpWidth = $bp['width'];
            $bpHeight = $bp['height'] ?? null;
            $media = $bp['media'];
            $bpSrcsetWidths = $bp['srcset'] ?? $this->generateSrcsetWidths($bpWidth);

            foreach ($formats as $format) {
                $bpSrcset = $this->generateSrcset($src, $bpWidth, $bpHeight, $bpSrcsetWidths, $format, $quality);
                $html .= "    <source media=\"{$media}\" type=\"image/{$format}\" srcset=\"{$bpSrcset}\">\n";
            }
        }

        // Main sources (WebP and other formats)
        foreach ($formats as $format) {
            $srcset = $this->generateSrcset($src, $width, $height, $srcsetWidths, $format, $quality);
            $html .= "    <source type=\"image/{$format}\" srcset=\"{$srcset}\" sizes=\"{$sizes}\">\n";
        }

        // Fallback img tag
        $fallbackSrc = $this->dataService->getImageOrCreate($src, $width, $height, $fallbackFormat, $quality);

        $imgAttrs = [
            'src' => $fallbackSrc,
            'width' => $width,
            'alt' => $alt,
        ];

        if ($height) {
            $imgAttrs['height'] = $height;
        }

        if ($lazy) {
            $imgAttrs['loading'] = 'lazy';
        }

        if ($class) {
            $imgAttrs['class'] = $class;
        }

        $html .= "    " . $this->buildTag('img', $imgAttrs) . "\n";
        $html .= "</picture>";

        return new HtmlString($html);
    }

    /**
     * Generate srcset string
     */
    protected function generateSrcset(
        string $src,
        int $baseWidth,
        ?int $baseHeight,
        array $widths,
        string $format,
        int $quality
    ): string {
        $srcset = [];

        foreach ($widths as $w) {
            // Calculate proportional height if base height is set
            $h = $baseHeight ? (int) round($baseHeight * ($w / $baseWidth)) : null;

            $url = $this->dataService->getImageOrCreate($src, $w, $h, $format, $quality);
            $srcset[] = "{$url} {$w}w";
        }

        return implode(', ', $srcset);
    }

    /**
     * Generate default srcset widths based on base width
     */
    protected function generateSrcsetWidths(int $baseWidth): array
    {
        $widths = [];

        foreach ($this->defaultMultipliers as $multiplier) {
            $w = (int) round($baseWidth * $multiplier);
            // Cap at reasonable maximum
            if ($w <= 2400 && $w >= 100) {
                $widths[] = $w;
            }
        }

        // Ensure unique and sorted
        $widths = array_unique($widths);
        sort($widths);

        return $widths;
    }

    /**
     * Generate default sizes attribute based on width
     */
    protected function generateDefaultSizes(int $width): string
    {
        $sizes = [];
        $breakpoints = $this->generateSrcsetWidths($width);

        foreach ($breakpoints as $i => $bp) {
            if ($i < count($breakpoints) - 1) {
                $sizes[] = "(max-width: {$bp}px) {$bp}px";
            } else {
                $sizes[] = "{$bp}px";
            }
        }

        return implode(', ', $sizes);
    }

    /**
     * Detect original format from file extension
     */
    protected function detectOriginalFormat(string $src): string
    {
        $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION));

        return match ($ext) {
            'png' => 'png',
            'gif' => 'gif',
            'webp' => 'webp',
            default => 'jpg',
        };
    }

    /**
     * Build HTML tag from attributes
     */
    protected function buildTag(string $tag, array $attrs): string
    {
        $attrStr = [];

        foreach ($attrs as $key => $value) {
            if ($value === true) {
                $attrStr[] = $key;
            } elseif ($value !== false && $value !== null && $value !== '') {
                $attrStr[] = $key . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
            }
        }

        $selfClosing = in_array($tag, ['img', 'input', 'br', 'hr', 'meta', 'link']);

        if ($selfClosing) {
            return "<{$tag} " . implode(' ', $attrStr) . ">";
        }

        return "<{$tag} " . implode(' ', $attrStr) . "></{$tag}>";
    }
}
