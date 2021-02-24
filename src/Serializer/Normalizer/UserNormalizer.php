<?php

namespace App\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function normalize($object, $format = null, array $context = []): array
    {
        $data = array();
        $data['id'] = $object->getId();
        $data['name'] = $object->getName();
        $data['userName'] = $object->getUserName();
        $data['tweets'] = array();
        foreach ($object->getTweets() as $tweet) {
            $data['tweets'][] = $this->router->generate('api_get_tweet', [
                'id' => $tweet->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }
        $data['likes'] = array();
        foreach ($object->getLikes() as $tweet) {
            $data['likes'][] = $this->router->generate('api_get_tweet', [
                'id' => $tweet->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof \App\Entity\User;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
