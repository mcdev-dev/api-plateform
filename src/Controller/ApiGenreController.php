<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiGenreController extends AbstractController
{
    /**
     * @Route("/api/genres", name="api_genres", methods={"GET"})
     * @param GenreRepository $repository
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function listGenres(GenreRepository $repository, SerializerInterface $serializer)
    {

        $genres = $repository->findAll();
        $data = $serializer
            ->serialize(
                $genres,
                'json',
                [
                    // Les groups se mettent en annotation dans l'entité, en choisissant quels attributs je veux afficher ou non
                    // listeGenreSimple renvoie seulement l'id et le libelle
                    // listeGenreComplete renvoie plus d'infos (isbn, titre, prix, editeur(nom), auteur(nom, prenom, nationalité)
                    'groups' => ['listeGenreSimple', 'listeGenreComplete']
                ]
            );

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }


    /**
     * @Route("/api/genres/{id}", name="api_genres_show", methods={"GET"})
     * @param Genre $genre
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function showGenre(Genre $genre, SerializerInterface $serializer)
    {
        $data = $serializer
            ->serialize(
                $genre,
                'json',
                [
                    'groups' => ['listeGenreSimple', 'listeGenreComplete']
                ]
            );
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return JsonResponse
     * @Route("/api/genres", name="api_genre_create", methods={"POST"})
     */
    public function createGenre(EntityManagerInterface $entityManager, Request $request, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        // Je récupere les infos
        $data = $request->getContent();

        // Je transforme le Json en objet avec deserialize en creant un nouvelle objet Genre (Genre::class)
        $genre = $serializer
            ->deserialize(
                $data,
                Genre::class,
                'json'
            );
        // Je verifie s'il y a un erreur de validation, si c'est le cas, je retourne le message qui est dans l'entité "genre"
        $errors = $validator->validate($genre);
        if (count($errors)) {
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }
        // j'enregistre en BDD la nouvelle entrée
        $entityManager->persist($genre);
        $entityManager->flush();

        // je retourne une reponse en json, avec un message pour indiquer ce qu'il s'est passé (je peux mettre null si je ne veux pas de message), avec comme status 201 (Response::HTTP_CREATED)
        // Dans le header (location), je rajoute l'url qui contient l'id de la nouvelle entrée créée en BDD ($this->generateURL)
        return new JsonResponse(
            "Nouveau genre enregistré",
            Response::HTTP_CREATED,
            [
                'location' => "api/genres/" . $genre->getId()
            ],
            true
        );
    }

    /**
     * @Route("/api/genres/{id}", name="api_genre_update", methods={"PUT"})
     * @param Genre $genre
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return JsonResponse
     */
    public function updateGenre(Genre $genre, EntityManagerInterface $entityManager, Request $request, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $data = $request->getContent();
        // object_to_populate -> je cible l'object/entity que je veux modifier
        $serializer
            ->deserialize(
                $data,
                Genre::class,
                'json',
                ["object_to_populate" => $genre]
            );
        $errors = $validator->validate($genre);
        if (count($errors)) {
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }
        $entityManager->persist($genre);
        $entityManager->flush();

        return new JsonResponse(
            "Modification du genre effectué",
            Response::HTTP_OK,
            [],
            true
        );

    }

    /**
     * @Route("api/genres/{id}", name="api_genre_delete", methods={"DELETE"})
     * @param Genre $genre
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function delete(Genre $genre, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($genre);
        $entityManager->flush();

        return new JsonResponse(
            "Le genre a bien été supprimé",
            Response::HTTP_OK, []
        );
    }
}
