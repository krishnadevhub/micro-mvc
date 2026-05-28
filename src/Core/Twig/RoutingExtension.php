<?php

declare(strict_types=1);

namespace App\Core\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\TwigFunction;

/**
 * Twig extension providing path() and url() functions for URL generation from named routes.
 *
 * @package App\Core\Twig
 */
class RoutingExtension extends AbstractExtension
{
    /**
     * @param UrlGeneratorInterface $generator The URL generator for resolving named routes
     */
    public function __construct(
        private readonly UrlGeneratorInterface $generator
    ) {
    }

    /**
     * Returns the list of Twig functions registered by this extension.
     *
     * @return array<TwigFunction>
     */
    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'url',
                $this->getUrl(...),
                ['is_safe_callback' => $this->isUrlGenerationSafe(...)]
            ),
            new TwigFunction(
                'path',
                $this->getPath(...),
                ['is_safe_callback' => $this->isUrlGenerationSafe(...)]
            ),
        ];
    }

    /**
     * Generates a relative or absolute path for the given route name.
     *
     * @param string $name The route name
     * @param array<string, mixed> $parameters Route parameters
     * @param bool $relative Whether to generate a relative path
     * @return string
     */
    public function getPath(string $name, array $parameters = [], bool $relative = false): string
    {
        return $this->generator->generate(
            $name,
            $parameters,
            $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }

    /**
     * Generates an absolute or network-relative URL for the given route name.
     *
     * @param string $name The route name
     * @param array<string, mixed> $parameters Route parameters
     * @param bool $schemeRelative Whether to generate a scheme-relative URL
     * @return string
     */
    public function getUrl(string $name, array $parameters = [], bool $schemeRelative = false): string
    {
        return $this->generator->generate(
            $name,
            $parameters,
            $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    /**
     * Determines at compile time whether the generated URL will be safe, saving
     * unnecessary automatic escaping for performance.
     *
     * The URL generation process percent-encodes non-alphanumeric characters, so there is no risk
     * that malicious characters are part of the URL. The only character that must be escaped in HTML
     * is the ampersand ("&") which separates query params. We can only mark URL generation as safe
     * when we are certain there will not be multiple query params.
     *
     * @param Node $argsNode The arguments of the path/url function
     * @return array<string> An array with the contexts the URL is safe in
     */
    public function isUrlGenerationSafe(Node $argsNode): array
    {
        $paramsNode = $argsNode->hasNode('parameters') ? $argsNode->getNode('parameters') : (
            $argsNode->hasNode('1') ? $argsNode->getNode('1') : null
        );

        if (
            $paramsNode === null
            || ($paramsNode instanceof ArrayExpression
                && \count($paramsNode) <= 2
                && (!$paramsNode->hasNode('1') || $paramsNode->getNode('1') instanceof ConstantExpression))
        ) {
            return ['html'];
        }

        return [];
    }
}
