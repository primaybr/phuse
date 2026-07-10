<?php

declare(strict_types=1);

namespace Core\Template;

/**
 * Marks a string as pre-rendered, trusted HTML so the template engine's
 * default output-escaping skips it. Wrap only framework-built fragments
 * (nested render() output, sidebar/navbar markup, generated <link>/<script>
 * tags) - never wrap raw user/API data with this.
 */
final class SafeHtml
{
    public function __construct(private readonly string $html)
    {
    }

    public function __toString(): string
    {
        return $this->html;
    }
}
