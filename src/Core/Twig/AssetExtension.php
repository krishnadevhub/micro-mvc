<?php

namespace App\Core\Twig;

use App\Application;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AssetExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset', [$this, 'getAssetUrl']),
        ];
    }

    /**
     * Returns the public url/path of an asset.
     */
    public function getAssetUrl(string $path): string
    {
        return Application::getBaseUrl().DIRECTORY_SEPARATOR.$path;
    }
}
