<?php

namespace App\Controller;

use App\Entity\Link;
use App\Repository\LinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class RedirectController extends AbstractController
{
    #[Route(path: '/go/{code}', name: 'redirectToUrl', methods: ['GET'])]
    public function redirectToUrl(string $code, LinkRepository $linkRepository): RedirectResponse
    {
        $url = $linkRepository->findOneBy(['code' => $code]);

        if ($url instanceof Link) {
            $url->incrementCountOfUsages();
            $linkRepository->flush();

            return $this->redirect($url->getUrl());
        } else {
            throw $this->createNotFoundException('URL not found!');
        }
    }
}
