<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\Tweet;
use App\Entity\User;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use App\Serializer\Normalizer\TweetNormalizer;
use App\Serializer\Normalizer\UserNormalizer;

class ApiController extends AbstractController
{
    function getTweet($id)
    {
        // Obtenemos el tweet
        $entityManager = $this->getDoctrine()->getManager();
        $tweet = $entityManager->getRepository(Tweet::class)->find($id);
        // Si el tweet no existe devolvemos un error con código 404.
        if ($tweet == null) {
            return new JsonResponse([
                'error' => 'Tweet not found'
            ], 404);
        }

        $encoders = [new JsonEncoder()]; // Codificador JSON

        $tweetNormalizer = new TweetNormalizer($this->get('router'));
        $normalizers = [$tweetNormalizer];

        $serializer = new Serializer($normalizers, $encoders);

        return JsonResponse::fromJsonString($serializer->serialize($tweet, 'json'));
    }

    function getTweetfonyUser($id)
    {
        // Obtenemos el usuario
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);
        // Si el usuario no existe devolvemos un error con código 404.
        if ($user == null) {
            return new JsonResponse([
                'error' => 'User not found'
            ], 404);
        }

        $encoders = [new JsonEncoder()]; // Codificador JSON

        $userNormalizer = new UserNormalizer($this->get('router'));
        $normalizers = [$userNormalizer];

        $serializer = new Serializer($normalizers, $encoders);

        return JsonResponse::fromJsonString($serializer->serialize($user, 'json'));
    }

    function getTweets()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $tweets = $entityManager->getRepository(Tweet::class)->findAll();
        if ($tweets == null) {
            return new JsonResponse([
                'error' => 'Tweets not found'
            ], 404);
        }

        $encoders = [new JsonEncoder()]; // Codificador JSON

        $tweetNormalizer = new TweetNormalizer($this->get('router'));
        $normalizers = [$tweetNormalizer];

        $serializer = new Serializer($normalizers, $encoders);

        return JsonResponse::fromJsonString($serializer->serialize($tweets, 'json'));
    }

    function getTweetfonyUsers()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $users = $entityManager->getRepository(User::class)->findAll();
        if ($users == null) {
            return new JsonResponse([
                'error' => 'Users not found'
            ], 404);
        }

        $encoders = [new JsonEncoder()]; // Codificador JSON

        $userNormalizer = new UserNormalizer($this->get('router'));
        $normalizers = [$userNormalizer];

        $serializer = new Serializer($normalizers, $encoders);

        return JsonResponse::fromJsonString($serializer->serialize($users, 'json'));
    }

    function index()
    {
        $result = array();
        $result['users'] = $this->generateUrl(
            'api_get_users',
            array(),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $result['tweets'] = $this->generateUrl(
            'api_get_tweets',
            array(),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        return new JsonResponse($result);
    }

    function postTweetfonyUser(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(array("userName" => $request->request->get("userName")));
        if ($user) {
            return new JsonResponse([
                'error' => 'UserName already exists'
            ], 409);
        }
        $user = new User();
        $user->setName($request->request->get("name"));
        $user->setUserName($request->request->get("userName"));
        $entityManager->persist($user);
        $entityManager->flush();

        $encoders = [new JsonEncoder()]; // Codificador JSON

        $userNormalizer = new UserNormalizer($this->get('router'));
        $normalizers = [$userNormalizer];

        $serializer = new Serializer($normalizers, $encoders);

        return JsonResponse::fromJsonString($serializer->serialize($user, 'json'), 201);
    }

    function postTweet(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $tweet = new Tweet();
        $fecha = new \DateTime();
        $fecha->format("Y-m-d H:i:s");
        $usuario = $entityManager->getRepository(User::class)->findOneBy(array("id" => $request->request->get("user")));
        if (!$usuario) {
            return new JsonResponse([
                'error' => 'User not exists'
            ], 404);
        }

        $tweet->setText($request->request->get("text"));
        $tweet->setDate($fecha);
        $tweet->setUser($usuario);

        $entityManager->persist($tweet);
        $entityManager->flush();

        $encoders = [new JsonEncoder()]; // Codificador JSON

        $tweetNormalizer = new TweetNormalizer($this->get('router'));
        $normalizers = [$tweetNormalizer];

        $serializer = new Serializer($normalizers, $encoders);

        return JsonResponse::fromJsonString($serializer->serialize($tweet, 'json'), 201);
    }

    function putTweetfonyUser(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);
        if ($user == null) {
            return new JsonResponse([
                'error' => 'User not found'
            ], 404);
        }
        if ($user->getUserName() != $request->request->get("userName")) {
            $user2 = $entityManager->getRepository(User::class)->findOneBy(array("userName" => $request->request->get("userName")));
            if ($user2) {
                return new JsonResponse([
                    'error' => 'UserName already in use'
                ], 409);
            }
        }
        $user->setName($request->request->get("name"));
        $user->setUserName($request->request->get("userName"));
        $entityManager->flush();

        $encoders = [new JsonEncoder()]; // Codificador JSON

        $userNormalizer = new UserNormalizer($this->get('router'));
        $normalizers = [$userNormalizer];

        $serializer = new Serializer($normalizers, $encoders);

        return JsonResponse::fromJsonString($serializer->serialize($user, 'json'), 201);
    }

    function putTweet(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $tweet = $entityManager->getRepository(Tweet::class)->find($id);
        if ($tweet == null) {
            return new JsonResponse([
                'error' => 'Tweet not found'
            ], 404);
        }

        $fecha = new \DateTime();
        $fecha->format("Y-m-d H:i:s");
        $usuario = $entityManager->getRepository(User::class)->find($request->request->get("user"));
        if (!$usuario) {
            return new JsonResponse([
                'error' => 'User not exists'
            ], 404);
        }

        $tweet->setText($request->request->get("text"));
        $tweet->setDate($fecha);
        $tweet->setUser($usuario);

        $entityManager->flush();

        $encoders = [new JsonEncoder()]; // Codificador JSON

        $tweetNormalizer = new TweetNormalizer($this->get('router'));
        $normalizers = [$tweetNormalizer];

        $serializer = new Serializer($normalizers, $encoders);

        return JsonResponse::fromJsonString($serializer->serialize($tweet, 'json'), 201);
    }

    function deleteTweetfonyUser($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);
        if ($user == null) {
            return new JsonResponse([
                'error' => 'User not found'
            ], 404);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(null, 204);
    }

    function deleteTweet($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $tweet = $entityManager->getRepository(Tweet::class)->find($id);
        if ($tweet == null) {
            return new JsonResponse([
                'error' => 'Tweet not found'
            ], 404);
        }

        $entityManager->remove($tweet);
        $entityManager->flush();

        return new JsonResponse(null, 204);
    }

    function putLike(Request $request, $idUsuario, $idTweet)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($idUsuario);
        if ($user == null) {
            return new JsonResponse([
                'error' => 'User not found'
            ], 404);
        }

        $tweet = $entityManager->getRepository(Tweet::class)->find($idTweet);
        if ($tweet == null) {
            return new JsonResponse([
                'error' => 'Tweet not found'
            ], 404);
        }

        switch ($request->request->get("like")) {
            case "true":
                $tweet->addLike($user);
                break;
            case "false":
                $tweet->removeLike($user);
                break;
            default:
                return new JsonResponse([
                    'error' => 'Invalid request'
                ], 400);
                break;
        }

        $entityManager->flush();

        return new JsonResponse(null, 201);
    }
}
