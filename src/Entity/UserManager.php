<?php

namespace App\Entity;

use App\Utils\TimeUtils;
use App\Utils\Utils;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Elastica\Query\BoolQuery;
use Elastica\Query\QueryString;
use Elastica\Util;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;
use Sonata\Doctrine\Model\ManagerInterface;

class UserManager extends BaseUserManager implements UserManagerInterface, ManagerInterface
{
  private ProgramManager $program_manager;

  private TransformedFinder $user_finder;

  private UrlHelper $url_helper;

  public function __construct(PasswordUpdaterInterface $passwordUpdater,
                              CanonicalFieldsUpdater $canonicalFieldsUpdater,
                              EntityManagerInterface $em,
                              TransformedFinder $user_finder,
                              ProgramManager $program_manager,
                              UrlHelper $url_helper)
  {
    $this->user_finder = $user_finder;
    $this->url_helper = $url_helper;
    $this->program_manager = $program_manager;

    /** @var ObjectManager $om */
    $om = $em;
    parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, User::class);
  }

  public function decodeToken(string $token): array
  {
    $tokenParts = explode('.', $token);
    $tokenPayload = base64_decode($tokenParts[1], true);

    return json_decode($tokenPayload, true);
  }

  public function isPasswordValid(UserInterface $user, string $password, PasswordEncoderInterface $encoder): bool
  {
    return $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());
  }

  public function getMappedUserData(array $raw_user_data): array
  {
    $response_data = [];

    foreach ($raw_user_data as $user) {
      try {
        $country = Countries::getName(strtoupper($user->getCountry()));
      } catch (MissingResourceException $e) {
        $country = '';
      }
      array_push($response_data, [
        'username' => $user->getUsername(),
        'id' => $user->getId(),
        'avatar' => $user->getAvatar(),
        'project_count' => $this->program_manager->countPublicUserProjects($user->getId()),
        'country' => $country,
        'profile' => $user,
      ]);
    }

    return $response_data;
  }

  public function createUserFromScratch(array $userdata): ?User
  {
    $scratch_user_id = intval($userdata['id']);
    /** @var User|null $user */
    $user = $this->findUserBy(['scratch_user_id' => $scratch_user_id]);

    if (null === $user) {
      $username = $userdata['username'];
      $user = new User();
      $user->setScratchUserId($scratch_user_id);
      $user->setScratchUsername($username);
      $user->setEmail($username.'@localhost');
      $user->setPlainPassword(Utils::randomPassword());
      if ($avatar = $userdata['profile']['images']['90x90'] ?? null) {
        $user->setAvatar($avatar);
      }
      $joined = TimeUtils::dateTimeFromScratch($userdata['history']['joined']);
      if ($joined) {
        $user->changeCreatedAt($joined);
      }
      $this->objectManager->persist($user);
      $this->objectManager->flush();
      $this->objectManager->refresh($user);
    }

    return $user;
  }

  public function search(string $query, ?int $limit = 10, int $offset = 0): array
  {
    $program_query = $this->userSearchQuery($query);

    return $this->user_finder->find($program_query, $limit, ['from' => $offset]);
  }

  public function searchCount(string $query): int
  {
    $program_query = $this->userSearchQuery($query);

    $paginator = $this->user_finder->findPaginated($program_query);

    return $paginator->getNbResults();
  }

  private function userSearchQuery(string $query): BoolQuery
  {
    $query = Util::escapeTerm($query);

    $words = explode(' ', $query);
    foreach ($words as &$word) {
      $word = $word.'*';
    }
    unset($word);
    $query = implode(' ', $words);

    $query_string = new QueryString();
    $query_string->setQuery($query);
    $query_string->setFields(['id', 'username']);
    $query_string->setAnalyzeWildcard();
    $query_string->setDefaultOperator('AND');

    $bool_query = new BoolQuery();
    $bool_query->addMust($query_string);

    return $bool_query;
  }


  //-------------- Sonata

  public function findUsersBy(?array $criteria = null, ?array $orderBy = null, $limit = null, $offset = null)
  {
    return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
  }

  public function find($id)
  {
    return $this->getRepository()->find($id);
  }

  public function findAll()
  {
    return $this->getRepository()->findAll();
  }

  public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
  {
    return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
  }

  public function findOneBy(array $criteria, ?array $orderBy = null)
  {
    return parent::findUserBy($criteria);
  }

  public function create()
  {
    return parent::createUser();
  }

  public function save($entity, $andFlush = true): void
  {
    parent::updateUser($entity, $andFlush);
  }

  public function delete($entity, $andFlush = true): void
  {
    if (!$entity instanceof UserInterface) {
      throw new \InvalidArgumentException('Save method expected entity of type UserInterface');
    }

    parent::deleteUser($entity);
  }

  public function getTableName()
  {
    return $this->objectManager->getClassMetadata($this->getClass())->table['name'];
  }

  public function getConnection()
  {
    return $this->objectManager->getConnection();
  }

  /**
   * @param array $criteria
   * @param int $page
   * @param int $limit
   * @param array $sort
   * @return Pager
   */
  public function getPager(array $criteria, $page, $limit = 10, array $sort = [])
  {
    $query = $this->getRepository()
      ->createQueryBuilder('u')
      ->select('u');

    $fields = $this->objectManager->getClassMetadata($this->getClass())->getFieldNames();
    foreach ($sort as $field => $direction) {
      if (!\in_array($field, $fields, true)) {
        throw new \RuntimeException(sprintf("Invalid sort field '%s' in '%s' class", $field, $this->getClass()));
      }
    }
    if (0 === \count($sort)) {
      $sort = ['username' => 'ASC'];
    }
    foreach ($sort as $field => $direction) {
      $query->orderBy(sprintf('u.%s', $field), strtoupper($direction));
    }

    if (isset($criteria['enabled'])) {
      $query->andWhere('u.enabled = :enabled');
      $query->setParameter('enabled', $criteria['enabled']);
    }

    $pager = new Pager();
    $pager->setMaxPerPage($limit);
    $pager->setQuery(new ProxyQuery($query));
    $pager->setPage($page);
    $pager->init();

    return $pager;
  }

}
