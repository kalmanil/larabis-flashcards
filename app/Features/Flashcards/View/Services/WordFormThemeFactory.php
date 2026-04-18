<?php

declare(strict_types=1);

namespace App\Features\Flashcards\View\Services;

use App\Features\Flashcards\View\Contracts\WordFormThemeInterface;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Container\Container;

/**
 * Resolves WordFormTheme by UI view code (Default vs Admin under View\Ui\).
 */
final class WordFormThemeFactory
{
    private const WORD_FORM_THEME_FQCN = 'App\\Features\\Flashcards\\View\\Ui\\%s\\WordFormTheme';

    public function __construct(
        private readonly Container $app,
    ) {}

    public function resolve(?string $viewCode): WordFormThemeInterface
    {
        $viewCode = $viewCode ?? 'default';
        $viewPart = $this->viewCodeToNamespacePart($viewCode);
        $fqcn = sprintf(self::WORD_FORM_THEME_FQCN, $viewPart);

        return $this->app->make($fqcn);
    }

    public function resolveFromContext(TenantContext $context): WordFormThemeInterface
    {
        $view = $context->getView();

        return $this->resolve($view?->code ?? 'default');
    }

    /**
     * @return array<string, string>
     */
    public function variables(?string $viewCode): array
    {
        return $this->resolve($viewCode)->variables();
    }

    private function viewCodeToNamespacePart(string $viewCode): string
    {
        return match (strtolower($viewCode)) {
            'admin' => 'Admin',
            default => 'Default',
        };
    }
}
