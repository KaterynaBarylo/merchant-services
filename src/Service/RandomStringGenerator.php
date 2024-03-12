<?php

namespace App\Service;

use App\Entity\Link;
use App\Repository\LinkRepository;

class RandomStringGenerator
{
    private const MIN_STRING_LENGTH = 3;
    private const MAX_ATTEMPTS = 10;

    public function __construct(private LinkRepository $linkRepository)
    {
    }

    public function generateRandomString(int $length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public function generateCode(): string
    {
        $codeLength = $this->linkRepository->getMaxCodeLength() ?? self::MIN_STRING_LENGTH;
        $code = $this->generateRandomString($codeLength);
        $countOfAttempts = 1;
        while ($this->linkRepository->findOneBy(['code' => $code]) instanceof Link) {
            if ($countOfAttempts > self::MAX_ATTEMPTS) {
                $countOfAttempts = 0;
                ++$codeLength;
            }
            $code = $this->generateRandomString($codeLength);
            ++$countOfAttempts;
        }

        return $code;
    }
}
