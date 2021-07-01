<?php

namespace App\Utils\AbstractClasses;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


abstract class CategoryTreeAbstract {

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UrlGeneratorInterface
     */
    public $urlGenerator;

    /**
     * @var Connection
     */
    protected static $dbConnection;

    /**
     * @var array
     */
    public $categoriesArrayFromDb;

    /**
     * @var array
     */
    public $categoryList;

    /**
     * CategoryTreeAbstract constructor.
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->categoriesArrayFromDb = $this->getCategories();
    }

    abstract public function getCategoryList(array $categories_array);

    /**
     * @param int|null $parent_id
     * @return array
     */
    public function buildTree(int $parent_id = null): array
    {
        $subcategory = [];
        foreach ($this->categoriesArrayFromDb as $category)
        {
            if ($category['parent_id'] == $parent_id)
            {
                $children = $this->buildTree($category['id']);

                if ($children)
                {
                    $category['children'] = $children;
                }
                $subcategory[] = $category;
            }
        }
        return $subcategory;
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getCategories(): array
    {
        if (self::$dbConnection)
        {
            return self::$dbConnection;
        }
        else
        {
            $conn = $this->entityManager->getConnection();
            $sql = "SELECT * FROM categories";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            return self::$dbConnection = $stmt->fetchAll();
        }
    }
}