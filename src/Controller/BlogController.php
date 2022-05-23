<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\NewArticleFormType;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/blog', name: 'blog_')]
class BlogController extends AbstractController
{
   /*
    * Contrôleur de la page permettant de créer un nouvel article
    *
    * Accès réservé aux administrateur (ROLE_ADMI)
    */
    #[Route('/nouvelle-publication/', name: 'new_publication')]
    #[IsGranted('ROLE_ADMIN')]
    public function newPublication(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {
        $article = new Article();

        $form = $this->createForm(NewArticleFormType::class, $article);

        $form->handleRequest($request);

        // Si le formulaire est envoyé et sans erreurs
        if($form->isSubmitted() && $form->isValid()){

            //On termine d'hydrater l'article
            $article
                ->setPublicationDate( new \DateTime() )
                ->setAuthor( $this->getUser() )
                ->setSlug( $slugger->slug( $article->getTitle() )->lower() )
            ;

            $em = $doctrine->getManager();
            $em->persist($article);
            $em->flush();

            // Message flash de suucès
            $this->addFlash('success', 'Article publié avec succès');

            //Redirection vers la page qui affiche l'article (en envoyant son id et son slug dans l'url)
            return $this->redirectToRoute('blog_publication_view', [
                'id' => $article->getId(),
                'slug' => $article->getSlug(),
            ]);

        }

        return $this->render('blog/new_publication.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * Contrôleur de la page permettant de voir un article en détails (via id et slug dans l'url)
     */
    #[Route('/publication/{id}/{slug}', name: 'publication_view')]
    #[ParamConverter('article', options: ['mapping' => [ 'id' => 'id', 'slug' => 'slug' ] ])]
    public function publicationView(Article $article): Response
    {

        return $this->render('blog/publication_view.html.twig', [
           'article' => $article,
        ]);

    }


    /**
     * Contrôleur de la page qui listes les articles
     */
    #[Route('/publication/liste/', name: 'publication_list')]
    public function publicationList(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {
        // Récupération de $_GET['page'], 1 si elle n'existe pas
        $requestedPage = $request->query->getInt('page', 1);

        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        $em=$doctrine->getManager();

        $query=$em->createQuery('SELECT a FROM App\Entity\Article a ORDER BY a.publicationDate DESC');

        $articles = $paginator->paginate(
            $query,     //Requête créée juste avant
            $requestedPage,     //Page qu'on souhaite voir
            10,     //Nombre d'article à afficher par page
        );

        return $this->render('blog/publication_list.html.twig', [
            'articles' => $articles,
        ]);

    }

    /**
     * Contrôleur de la page admin servant à supprimer un article via son id dans l'url
     *
     * Accès réservé aux administrateur(ROLE_ADMIN)
     */
    #[Route('/publication/suppression/{id}', name: 'publication_delete', priority: 10)]
    #[IsGranted('ROLE_ADMIN')]
    public function publicationDate(Article $article, Request $request, ManagerRegistry $doctrine): Response
    {
        $csrfToken = $request->query->get('csrf_token', '');

        if(!$this->isCsrfTokenValid('blog_publication_delete_' . $article->getId(), $csrfToken)){

            $this->addFlash('error', 'Token sécurité invalide, veuillez ré-essayer');

        } else {



        $em = $doctrine->getManager();

        $em->remove($article);

        $em->flush();

        //Message flash de succès
       $this->addFlash('success', 'La publication a été supprimée avec succès !');

        }

       //Redirection
        return $this->redirectToRoute('blog_publication_list');
    }

    /**
     * Contrôleur de la page permettant de modifier un article existant via son id dans l'url
     *
     * Accès réservé aux administrateur(ROLE_ADMIN)
     */
    #[Route('/publication/modifier/{id}', name: 'publication_edit', priority: 10)]
    #[IsGranted('ROLE_ADMIN')]
    public function publicationEdit(Article $article, Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {

        //Instanciation d'un nouveau formulaire basé sur $article qui contient déjà les données actuelles de l'article à modifier
        $form = $this->createForm(NewArticleFormType::class, $article);

        $form->handleRequest($request);

        //Si le formulaire est envoyé et sans erreur
        if($form->issubmitted() && $form->isValid() ){

            // Sauvegarde des données modifiées en BDD
            $article->setSlug( $slugger->slug( $article->getTitle() )->lower());

            $em = $doctrine->getManager();

            $em->flush();

            // Message flash de succès
            $this->addFlash( 'success', 'Publication modifiée avec succès !');

            //Redirection vers l'article modifié
            return $this->redirectToRoute('blog_publication_view', [
                'id' => $article->getId(),
                'slug' => $article->getSlug(),
            ]);

        }

        //Redirection
        return $this->render('blog/publication_edit.html.twig', [
            'form' => $form->createView(),
            'article'
        ]);

    }


}
