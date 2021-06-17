<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\AddProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ) {
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/", methods={"GET"}, name="homepage")
     */
    public function index(): Response
    {
        return $this->render('product/index.html.twig');
    }

    /**
     * @Route("/products", methods={"GET"}, name="show_products")
     */
    public function list(Request $request, PaginatorInterface $paginator): Response
    {
        $filter = $request->query->get('filter', 'sale_only');
        $search = $request->query->get('search');

        $query = $this->productRepository->createQueryBuilder('p');
        $query = ($filter == 'only_sale') ? $query->where('p.sale_price is not Null') :
            ($filter == 'no_sale' ? $query->where('p.sale_price is Null') : $query);

        $query = ($search !== null) ? $query->andWhere('p.sku LIKE :search')
            ->setParameter('search', $search . '%') : $query;

        $query->orderBy('p.id', 'DESC')->getQuery();
        $pagination = $paginator->paginate($query, $request->query->get('page', 1), 5);

        return $this->render('product/search.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/products", methods={"POST"}, name="add_product")
     */
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $form = $this->createForm(AddProductType::class, new Product());
        $form->handleRequest($request);
        $form->submit($data);
        if ($form->isValid()) {
            $product = $form->getData();
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            return new JsonResponse($this->serializer->serialize($product, 'json'), 201, [], true);
        }

        return new JsonResponse($this->getFormErrors($form), Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param FormInterface $form
     *
     * @return array
     */
    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->all() as $item) {
            foreach ($item->getErrors() as $error) {
                if ($error->getMessage()) {
                    $errors[$error->getOrigin()->getName()] = $error->getMessage();
                }
            }
        }
        return $errors;
    }
}
