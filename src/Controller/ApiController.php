<?php

namespace App\Controller;

use App\Entity\Link;
use App\Repository\LinkRepository;
use App\Service\RandomStringGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

#[Route(path: '/api')]
class ApiController extends AbstractController
{
    #[Route(path: '/link-shortener', methods: ['POST'])]
    public function shortLink(
        Request $request,
        LinkRepository $linkRepository,
        RandomStringGenerator $randomStringGenerator
    ): JsonResponse {
        try {
            $data = $request->toArray();
            Assert::keyExists($data, 'url', 'URL not found');
            $url = $data['url'];
            Assert::string($url, 'URL is not string');
            Assert::notFalse(filter_var($url, FILTER_VALIDATE_URL), 'Not valid URL');
        } catch (JsonException) {
            return $this->json(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        } catch (InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $code = $randomStringGenerator->generateCode();
        $link = new Link(trim($data['url']), $code);
        $linkRepository->add($link);

        $linkRepository->flush();

        return $this->json([
            'link' => $this->generateUrl(
                'redirectToUrl',
                ['code' => $link->getCode()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);
    }
}
