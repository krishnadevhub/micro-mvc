<?php

declare(strict_types=1);

namespace App\Core\Twig;

use App\Application;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension providing the asset() function for resolving public asset URLs.
 *
 * @package App\Core\Twig
 */
final class AssetExtension extends AbstractExtension
{
    /**
     * Returns the list of Twig functions registered by this extension.
     *
     * @return array<TwigFunction>
     */
    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset', $this->getAssetUrl(...)),
        ];
    }

    /**
     * Returns the public URL of an asset.
     *
     * @param string $path The asset path relative to the public directory
     * @return string
     */
    public function getAssetUrl(string $path): string
    {
        return Application::getBaseUrl() . DIRECTORY_SEPARATOR . $path;
    }
}
