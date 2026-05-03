<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class LocaleController extends AbstractController
{
    private const SUPPORTED = ['fr', 'en', 'ar'];

    #[Route('/locale/{locale}', name: 'app_locale_switch')]
    public function switch(string $locale, Request $request): RedirectResponse
    {
        if (in_array($locale, self::SUPPORTED)) {
            $request->getSession()->set('_locale', $locale);
        }

        return $this->redirect($request->headers->get('referer') ?? '/');
    }
}
