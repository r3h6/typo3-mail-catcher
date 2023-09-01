<?php

namespace R3H6\MailCatcher\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class MimeHeaderViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('headers', 'array', 'Headers to format as string');
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $formatedHeaders = [];
        $headers = $arguments['headers'] ?? $renderChildrenClosure();
        foreach ($headers as $header) {
            $formatedHeaders[] = $header->toString();
        }
        return implode(', ', $formatedHeaders);
    }
}
