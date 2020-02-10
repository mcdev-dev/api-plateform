<?php

namespace App\Controller;

use App\Entity\Auteur;
use App\Repository\AuteurRepository;
use App\Repository\NationaliteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiAuteurController extends AbstractController
{
    /**
     * @Route("/api/auteurs", name="api_auteurs", methods={"GET"})
     * @param AuteurRepository $repository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function listAuteurs(AuteurRepository $repository, SerializerInterface $serializer)
    {
        $auteurs = $repository->findAll();
        $data = $serializer
            ->serialize(
                $auteurs,
                'json',
                [
                    'groups' => ['listeAuteurSimple', 'listeAuteurComplete']
                ]
            );
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/auteurs/{id}", name="api_auteurs_show", methods={"GET"})
     * @param Auteur $auteur
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function showAuteur(Auteur $auteur, SerializerInterface $serializer)
    {
        $data = $serializer
            ->serialize(
                $auteur, 'json',
                [
                    'groups' => ['listeAuteurSimple', 'listeAuteurComplete']
                ]
            );
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/auteurs", name="api_auteur_create", methods={"POST"})
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param NationaliteRepository $repository
     * @return JsonResponse
     */
    public function createAuteur(EntityManagerInterface $entityManager, Request $request, SerializerInterface $serializer, ValidatorInterface $validator, NationaliteRepository $repository)
    {
        $data = $request->getContent();
        $dataTab = $serializer->decode($data, 'json');
        $auteur = new Auteur();
        $nationalite = $repository->find($dataTab['nationalite']['id']);
        $serializer
            ->deserialize(
                $data,
                Auteur::class,
                'json',
                ['object_to_populate' => $auteur]);
        $auteur->setnationalite($nationalite);
        $errors = $validator->validate($auteur);
        if (count($errors)) {
            $errorJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorJson, Response::HTTP_BAD_REQUEST, [], true);
        }
        $entityManager->persist($auteur);
        $entityManager->flush();

        return new JsonResponse(
            "Nouvel auteur enregistré",
            response::HTTP_CREATED,
            [
                'location' => "api/auteurs/" . $auteur->getId()
            ],
            true
        );
    }

    /**
     * @Route("/api/auteurs/{id}", name="api_auteur_update", methods={"PUT"})
     * @param Auteur $auteur
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param NationaliteRepository $repository
     * @return JsonResponse
     */
    public function updateAuteur(Auteur $auteur, EntityManagerInterface $entityManager, Request $request, SerializerInterface $serializer, ValidatorInterface $validator, NationaliteRepository $repository)
    {
        $data = $request->getContent();
        // Pour pouvoir assigner la bonne nationalité en cas de modification, comme il s'agit d'un objet imbriqué, je dois d'abord "decode" $data
        // pour le transformer en array/tableau. Ensuite, je peux retrouver grace au repo de nationalite, l'id.
        $dataTab = $serializer->decode($data, 'json');
        //dump($dataTab); die;
        $nationalite = $repository->find($dataTab['nationalite']['id']);
        //dump($nationalite); die;
        $serializer
            ->deserialize(
                $data,
                Auteur::class,
                'json',
                ['object_to_populate' => $auteur]
            );
        // Je reattribue l'id de la nationalité avec le setter a auteur.
        $auteur->setnationalite($nationalite);
        $errors = $validator->validate($auteur);
        if (count($errors)) {
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }
        $entityManager->persist($auteur);
        $entityManager->flush();

        return new JsonResponse(
            "Modification de l'auteur effectué",
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/api/auteurs/{id}", name="api_auteur_delete", methods={"DELETE"})
     * @param Auteur $auteur
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function delete(Auteur $auteur, EntityManagerInterface $entityManager)
    {
        $entityManager->remove($auteur);
        $entityManager->flush();

        return new JsonResponse(
            "L'auteur a été supprimé",
            Response::HTTP_OK
        );
    }
}
