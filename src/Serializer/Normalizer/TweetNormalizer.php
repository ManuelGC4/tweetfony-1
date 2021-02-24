<?php

namespace App\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TweetNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
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
        $data['date'] = $object->getDate();
        $data['text'] = $object->getText();
        // Para enlazar al usuario, añadimos el enlace API para consultar su información.
        $data['user'] = $this->router->generate('api_get_user', [
            'id' => $object->getUser()->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        // Para enlazar a los usuarios que han dado like al tweet, añadimos sus enlaces API.
        $data['likes'] = array();
        foreach ($object->getLikes() as $user) {
            $data['likes'][] = $this->router->generate('api_get_user', [
                'id' => $user->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof \App\Entity\Tweet;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
