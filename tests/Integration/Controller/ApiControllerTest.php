<?php

namespace App\Tests\Integration\Controller;

use App\Entity\Link;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiControllerTest extends WebTestCase
{
    /**
     * @dataProvider contentAndResponseProvider
     */
    public function testItSendsCorrectResponseForInvalidData($content, $expectedResponse)
    {
        $client = static::createClient();
        $client->request(
            method: 'POST',
            uri: '/api/link-shortener',
            content: $content
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $responseContent = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame($expectedResponse, $responseContent);
    }

    public static function contentAndResponseProvider(): \Generator
    {
        yield 'URL not found' => [
            '{"ur":"https://www.google.com/"}',
            ['error' => 'URL not found'],
        ];
        yield 'Url is not string' => [
            '{"url":234}',
            ['error' => 'URL is not string'],
        ];
        yield 'Not valid URL' => [
            '{"url":"httpswww.google.com/"}',
            ['error' => 'Not valid URL'],
        ];
        yield 'Invalid data' => [
            'not valid json',
            ['error' => 'Invalid data'],
        ];
    }

    public function testItCreatesShortUrl()
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $links = $entityManager->getRepository(Link::class)->findAll();
        foreach ($links as $link) {
            $entityManager->getRepository(Link::class)->remove($link);
        }
        $entityManager->getRepository(Link::class)->flush();

        $client->request(
            method: 'POST',
            uri: '/api/link-shortener',
            content: '{"url":"https://www.google.com/"}'
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $link = $entityManager->getRepository(Link::class)->findOneBy(['url' => 'https://www.google.com/']);
        self::assertInstanceOf(Link::class, $link);

        $responseContent = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('http://localhost/'.$link->getCode(), $responseContent['link']);
    }
}
