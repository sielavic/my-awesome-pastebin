<?php
namespace App\Controller;

use App\Entity\Paste;
use App\Form\PasteType;
use App\Repository\PasteRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PasteController extends AbstractController
{

    private $paginator;

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }
    #[Route('/', name: 'paste_index')]
    public function index(EntityManagerInterface $entityManager,Request $request, PasteRepository $pasteRepository): Response
    {
        $pastes = $entityManager->getRepository(Paste::class)->findBy(['access' => 'public'], null);

        $currentPaste = [];
        foreach ($pastes as $paste) {
            if ((new \DateTime()) < $paste->getExpiration()) {
                $currentPaste[] = $paste;
            }
        }


        $pagination = $this->paginator->paginate(
            $currentPaste,
            $request->query->getInt('page', 1), // текущая страница
            10 // кол-во паст на странице
        );


        return $this->render('paste/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    #[Route('/create', name: 'paste_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $paste = new Paste();
        $form = $this->createForm(PasteType::class, $paste);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Установка срока действия
            $expirationDuration = $form->get('expirationDuration')->getData();
            $currentDateTime = new DateTime();

            $currentDateTime->modify('+' . $expirationDuration . 'seconds');
            $paste->setExpiration($currentDateTime);

            // Генерация slug
            $paste->setSlug(bin2hex(random_bytes(5)));

            $entityManager->persist($paste);
            $entityManager->flush();

            return $this->redirectToRoute('paste_show', ['slug' => $paste->getSlug()]);
        }

        return $this->render('paste/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{slug}', name: 'paste_show')]
    public function show(string $slug, EntityManagerInterface $entityManager): Response
    {
        $paste = $entityManager->getRepository(Paste::class)->findOneBy(['slug' => $slug]);

        if (!$paste || (new \DateTime()) > $paste->getExpiration()) {
            throw $this->createNotFoundException('Паста не найдена или срок действия истек.');
        }

        return $this->render('paste/show.html.twig', [
            'paste' => $paste,
        ]);
    }
}
