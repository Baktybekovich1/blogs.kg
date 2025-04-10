<?php

namespace App\Controller;

session_start();


use App\Entity\Admin;
use App\Entity\Blogs;
use App\Entity\Comment;
use App\Repository\AdminRepository;
use App\Repository\BlogsRepository;
use App\Repository\CommentRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index_index")
     * @return Response
     */
    public function index(BlogsRepository $blogsRepository): Response
    {
        $blogs = $blogsRepository->findAll();

        return $this->render('index/index.html.twig', ['blogs' => $blogs]);
    }

    /**
     * @Route("/admin", name="index_admin")
     * @return Response
     */
    public function admin(Request $request, BlogsRepository $blogsRepository): Response
    {
        if (isset($_SESSION['status'])) {
            if ($request->getMethod() == Request::METHOD_POST) {
                $blogs = new Blogs();
                $blogs->setName($request->request->get("name"));
                $blogs->setTag($request->request->get("tag"));
                $blogs->setDate(date('Y-m-d'));
                $blogs->setTime(date("H:i:s"));
                $blogs->setText($request->request->get("text"));
                $blogs->setAuthor($request->request->get("author"));
                $blogsRepository->add($blogs, true);
                return $this->redirect('/');

            }
            return $this->render('index/admin.html.twig');
        } else {
            return $this->redirect('/404');
        }


    }

    /**
     * @Route ("/delete/{id}", name="index_delete")
     * @return Response
     */
    public function remove(BlogsRepository $blogsRepository, Blogs $blogs): Response
    {
        $blogsRepository->remove($blogs, true);
        return $this->redirectToRoute("index_index");
    }

    /**
     * @Route ("/detail/{id}",name="index_detail")
     * @return Response
     */
    public function detail(Blogs $blogs, Request $request, CommentRepository $commentRepository): Response
    {
        $comments = $commentRepository->findBy(['inn' => $request->get('id'), 'parent' => null]);
        if ($request->getMethod() == Request::METHOD_POST) {
            $comment = new Comment();
            $comment->setName($request->request->get('name'));
            $comment->setCreatedAt(new DateTime());
            $comment->setText($request->request->get('text'));
            $comment->setInn($request->request->get('inn'));
            $commentRepository->add($comment, true);
            return $this->redirect('/detail/' . $request->get('id'));
        }

        return $this->render("index/detail.html.twig", ["comments" => $comments, "blogs" => $blogs, 'date' => date('Y-m-d'),
            'time' => date("H:i:s")]);
    }



    /**
     * @Route ("/404",name="page404")
     * @return Response
     */

    public function page404(): Response
    {
        return $this->render('index/page404.html.twig');
    }

    /**
     * @Route ("/com_com",name="comCom")
     * @return Response
     */
    public function com_com(Request $request, CommentRepository $commentRepository): Response
    {
        if ($request->getMethod() == Request::METHOD_POST) {
            $comment = new Comment();
            $comment->setName($request->request->get('name'));
            $comment->setText($request->request->get('text'));
            $comment->setCreatedAt(new DateTime());
            $parent = $commentRepository->find($request->request->get('idn'));
            $comment->setInn($parent->getInn());
            $comment->setParent($parent);
            $commentRepository->add($comment, true);
            return $this->redirect('/detail/' . $parent->getInn());
        }
        return $this->render('index/com_comments.html.twig');
    }

}

