<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\Tweet;
use App\Entity\User;

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
        // Creamos un objeto genérico y lo rellenamos con la información.
        $result = new \stdClass();
        $result->id = $tweet->getId();
        $result->date = $tweet->getDate();
        $result->text = $tweet->getText();
        // Para enlazar al usuario, añadimos el enlace API para consultar su información.
        $result->user = $this->generateUrl('api_get_user', [
            'id' => $tweet->getUser()->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
        // Para enlazar a los usuarios que han dado like al tweet, añadimos sus enlaces API.
        $result->likes = array();
        foreach ($tweet->getLikes() as $user) {
            $result->likes[] = $this->generateUrl('api_get_user', [
                'id' => $user->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }
        // Al utilizar JsonResponse, la conversión del objeto $result a JSON se hace de forma automática.
        return new JsonResponse($result);
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
        // Creamos un objeto genérico y lo rellenamos con la información.
        $result = new \stdClass();
        $result->id = $user->getId();
        $result->name = $user->getName();
        $result->userName = $user->getUserName();
        // Para enlazar a los tweet que ha realizado el usuario, añadimos sus enlaces API.
        $result->tweets = array();
        foreach ($user->getTweets() as $tweet) {
            $result->tweets[] = $this->generateUrl('api_get_tweet', [
                'id' => $tweet->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }
        // Para enlazar a los tweets que ha dado like el usuario, añadimos sus enlaces API.
        $result->likes = array();
        foreach ($user->getLikes() as $tweet) {
            $result->likes[] = $this->generateUrl('api_get_tweet', [
                'id' => $tweet->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }
        // Al utilizar JsonResponse, la conversión del objeto $result a JSON se hace de forma automática.
        return new JsonResponse($result);
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

        $result = array();
        foreach ($tweets as $tweet) {
            $copy = new \stdClass();
            $copy->id = $tweet->getId();
            $copy->date = $tweet->getDate();
            $copy->text = $tweet->getText();

            $copy->user = $this->generateUrl('api_get_user', [
                'id' => $tweet->getUser()->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $copy->likes = array();
            foreach ($tweet->getLikes() as $user) {
                $copy->likes[] = $this->generateUrl('api_get_user', [
                    'id' => $user->getId(),
                ], UrlGeneratorInterface::ABSOLUTE_URL);
            }

            $result[] = $copy;
        }

        return new JsonResponse($result);
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

        $result = array();
        foreach ($users as $user) {
            $copy = new \stdClass();
            $copy->id = $user->getId();
            $copy->name = $user->getName();
            $copy->userName = $user->getUserName();

            $copy->tweets = array();
            foreach ($user->getTweets() as $tweet) {
                $copy->tweets[] = $this->generateUrl('api_get_tweet', [
                    'id' => $tweet->getId(),
                ], UrlGeneratorInterface::ABSOLUTE_URL);
            }

            $copy->likes = array();
            foreach ($user->getLikes() as $tweet) {
                $copy->likes[] = $this->generateUrl('api_get_tweet', [
                    'id' => $tweet->getId(),
                ], UrlGeneratorInterface::ABSOLUTE_URL);
            }

            $result[] = $copy;
        }

        return new JsonResponse($result);
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
        return new JsonResponse($this->generateUrl('api_get_user', [
            'id' => $user->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL), 201);
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

        return new JsonResponse($this->generateUrl('api_get_tweet', [
            'id' => $tweet->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL), 201);
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
        $result = new \stdClass();
        $result->id = $user->getId();
        $result->name = $user->getName();
        $result->userName = $user->getUserName();
        $result->likes = array();
        foreach ($user->getLikes() as $tweet) {
            $result->likes[] = $this->generateUrl('api_get_tweet', [
                'id' => $tweet->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }
        $result->tweets = array();
        foreach ($user->getTweets() as $tweet) {
            $result->tweets[] = $this->generateUrl('api_get_tweet', [
                'id' => $tweet->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }
        return new JsonResponse($result);
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

        $result = new \stdClass();

        $result->id = $tweet->getId();
        $result->text = $tweet->getText();
        $result->date = $tweet->getDate();

        $result->user = $this->generateUrl('api_get_user', [
            'id' => $tweet->getUser()->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $result->likes = array();
        foreach ($tweet->getLikes() as $user) {
            $result->likes[] = $this->generateUrl('api_get_user', [
                'id' => $user->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return new JsonResponse($result);
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
